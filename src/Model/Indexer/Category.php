<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024-2025 Dzmitry Vasileuski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/dzmitry-vasileuski/magento2-module-admin-search
 */

namespace Vasileuski\AdminSearch\Model\Indexer;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vasileuski\AdminSearch\Api\ClientInterface;
use Vasileuski\AdminSearch\Model\AbstractIndexer;
use Vasileuski\AdminSearch\Model\IndexerConfig;
use Zend_Db_Expr;

use function array_column;
use function array_combine;
use function array_merge;
use function array_slice;
use function array_unique;
use function explode;
use function implode;

class Category extends AbstractIndexer
{
    public const INDEXER_ID = 'admin_search_categories';

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
                if (isset($parentEntities[$categoryId]['name'])) {
                    $parents[] = $parentEntitiesMap[$categoryId]['name'];
                }
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
        $select     = $connection->select();

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
        $storeId      = $this->storeManager->getStore('admin')->getId();

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
        $select     = $connection->select();

        $select->from(
            ['c' => $this->resource->getTableName('catalog_category_entity')],
            ['count' => new Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('c.entity_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
