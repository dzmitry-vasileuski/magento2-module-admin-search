<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model\Indexer;

use Vasileuski\AdminSearch\Model\Indexer;

class Block extends Indexer
{
    public const INDEXER_ID = 'admin_search_blocks';

    /**
     * @inheritDoc
     */
    protected function getDocuments(array $ids, int $page, int $pageSize): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            $this->resource->getTableName('cms_block'),
            ['block_id', 'title', 'identifier', 'is_active']
        );

        if ($ids) {
            $select->where('block_id IN (?)', $ids);
        }

        $select->limitPage($page, $pageSize);

        $entities = $connection->fetchAll($select);
        $documents = [];

        foreach ($entities as $entity) {
            $documents[$entity['block_id']] = [
                'block_title' => $entity['title'],
                'block_identifier' => $entity['identifier'],
                'block_status' => $entity['is_active'] ? __('Enabled') : __('Disabled'),
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
            $this->resource->getTableName('cms_block'),
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('block_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
