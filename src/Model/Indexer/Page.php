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

use Vasileuski\AdminSearch\Model\AbstractIndexer;
use Zend_Db_Expr;

use function __;

class Page extends AbstractIndexer
{
    public const INDEXER_ID = 'admin_search_pages';

    protected function getDocuments(array $ids, int $page, int $pageSize): array
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select();

        $select->from(
            $this->resource->getTableName('cms_page'),
            ['page_id', 'title', 'identifier', 'is_active']
        );

        if ($ids) {
            $select->where('page_id IN (?)', $ids);
        }

        $select->limitPage($page, $pageSize);

        $entities  = $connection->fetchAll($select);
        $documents = [];

        foreach ($entities as $entity) {
            $documents[$entity['page_id']] = [
                'page_title'      => $entity['title'],
                'page_identifier' => $entity['identifier'],
                'page_status'     => $entity['is_active'] ? __('Enabled') : __('Disabled'),
            ];
        }

        return $documents;
    }

    protected function getDocumentsCount(array $ids): int
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select();

        $select->from(
            $this->resource->getTableName('cms_page'),
            ['count' => new Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('page_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
