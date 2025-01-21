<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model\Indexer;

use Vasileuski\AdminSearch\Model\Indexer;

class Customer extends Indexer
{
    public const INDEXER_ID = 'admin_search_customers';

    protected function getDocuments(array $ids, int $page, int $pageSize): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            $this->resource->getTableName('customer_entity'),
            [
                'entity_id',
                'email',
                'firstname',
                'lastname',
            ]
        );

        if ($ids) {
            $select->where('entity_id IN (?)', $ids);
        }

        $select->limitPage($page, $pageSize);

        $entities = $connection->fetchAll($select);
        $documents = [];

        foreach ($entities as $entity) {
            $documents[$entity['entity_id']] = [
                'customer_email' => $entity['email'],
                'customer_firstname' => $entity['firstname'],
                'customer_lastname' => $entity['lastname'],
            ];
        }

        return $documents;
    }

    protected function getDocumentsCount(array $ids): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            $this->resource->getTableName('customer_entity'),
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('entity_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
