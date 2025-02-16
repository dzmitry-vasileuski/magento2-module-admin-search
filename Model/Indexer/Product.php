<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model\Indexer;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vasileuski\AdminSearch\Api\ClientInterface;
use Vasileuski\AdminSearch\Model\Indexer;
use Vasileuski\AdminSearch\Model\IndexerConfig;

class Product extends Indexer
{
    public const INDEXER_ID = 'admin_search_products';

    /**
     * @param Config $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     * @param IndexerConfig $indexerConfig
     * @param ClientInterface $client
     */
    public function __construct(
        private Config $eavConfig,
        private StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        IndexerConfig $indexerConfig,
        ClientInterface $client
    ) {
        parent::__construct(
            $resource,
            $timezone,
            $localeResolver,
            $indexerConfig,
            $client
        );
    }

    /**
     * @inheritDoc
     */
    protected function getDocuments(array $ids, int $page, int $pageSize): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            ['p' => $this->resource->getTableName('catalog_product_entity')],
            ['entity_id', 'sku', 'type_id']
        );

        $select->join(
            ['pv' => $this->resource->getTableName('catalog_product_entity_varchar')],
            'p.entity_id = pv.entity_id',
            ['name' => 'value']
        );

        $select->join(
            ['a' => $this->resource->getTableName('eav_attribute')],
            'a.attribute_id = pv.attribute_id',
            []
        );

        $entityTypeId = $this->eavConfig->getEntityType(ProductModel::ENTITY)->getId();
        $storeId = $this->storeManager->getStore('admin')->getId();

        $select->where('pv.store_id = ?', $storeId);
        $select->where('a.attribute_code = ?', 'name');
        $select->where('a.entity_type_id = ?', $entityTypeId);

        if ($ids) {
            $select->where('p.entity_id IN (?)', $ids);
        }

        $select->limitPage($page, $pageSize);

        $entities = $connection->fetchAll($select);
        $documents = [];

        foreach ($entities as $entity) {
            $documents[$entity['entity_id']] = [
                'product_name' => $entity['name'],
                'product_sku' => $entity['sku'],
                'product_type' => __(ucfirst($entity['type_id'])),
            ];
        }

        return $documents;
    }

    /**
     * @inheritDoc
     */
    protected function getDocumentsCount(array $ids): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            ['p' => $this->resource->getTableName('catalog_product_entity')],
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('p.entity_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
