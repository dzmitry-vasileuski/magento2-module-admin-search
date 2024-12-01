<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model\Indexer;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vasileuski\AdminSearch\Model\Indexer;
use Vasileuski\AdminSearch\Model\IndexerConfig;

class Category extends Indexer
{
    public const INDEXER_ID = 'admin_search_categories';

    public function __construct(
        private Config $eavConfig,
        private StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        IndexerConfig $indexerConfig,
        ClientResolver $clientResolver
    ) {
        parent::__construct($resource, $timezone, $localeResolver, $indexerConfig, $clientResolver);
    }

    protected function getDocuments(array $ids, int $page, int $pageSize): array
    {
        $entities = $this->getCategories($ids, $page, $pageSize);

        $parentEntityIds = [];

        foreach ($entities as $entity) {
            $parentEntityIds = array_merge(
                $parentEntityIds,
                array_slice(explode('/', $entity['path']), 1)
            );
        }

        $parentEntities = $this->getCategories(array_unique($parentEntityIds));

        $parentEntitiesMap = array_combine(
            array_column($parentEntities, 'entity_id'),
            $parentEntities
        );

        $documents = [];

        foreach ($entities as $entity) {
            $parents = [];

            foreach (array_slice(explode('/', $entity['path']), 1) as $categoryId) {
                $parents[] = $parentEntitiesMap[$categoryId]['name'];
            }

            $documents[$entity['entity_id']] = [
                'category_name' => $entity['name'],
                'category_path' => implode(' / ', $parents),
            ];
        }

        return $documents;
    }

    private function getCategories(array $ids, ?int $page = null, ?int $pageSize = null): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            ['c' => $this->resource->getTableName('catalog_category_entity')],
            ['entity_id', 'path']
        );

        $select->join(
            ['cv' => $this->resource->getTableName('catalog_category_entity_varchar')],
            'c.entity_id = cv.entity_id',
            ['name' => 'value']
        );

        $select->join(
            ['a' => $this->resource->getTableName('eav_attribute')],
            'a.attribute_id = cv.attribute_id',
            []
        );

        $entityTypeId = $this->eavConfig->getEntityType(CategoryModel::ENTITY)->getId();
        $storeId = $this->storeManager->getStore('admin')->getId();

        $select->where('cv.store_id = ?', $storeId);
        $select->where('a.attribute_code = ?', 'name');
        $select->where('a.entity_type_id = ?', $entityTypeId);

        if ($ids) {
            $select->where('c.entity_id IN (?)', $ids);
        }

        if ($page && $pageSize) {
            $select->limitPage($page, $pageSize);
        }

        return $connection->fetchAll($select);
    }

    protected function getDocumentsCount(array $ids): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            ['c' => $this->resource->getTableName('catalog_category_entity')],
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('c.entity_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
