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

class CartPriceRule extends AbstractIndexer
{
    public const INDEXER_ID = 'admin_search_cart_price_rules';

    protected function getDocuments(array $ids, int $page, int $pageSize): array
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select();

        $select->from(
            $this->resource->getTableName('salesrule'),
            [
                'rule_id',
                'name',
                'from_date',
                'to_date',
                'is_active',
            ]
        );

        if ($ids) {
            $select->where('rule_id IN (?)', $ids);
        }

        $select->limitPage($page, $pageSize);

        $entities  = $connection->fetchAll($select);
        $documents = [];

        foreach ($entities as $entity) {
            $documents[$entity['rule_id']] = [
                'cart_price_rule_name'      => $entity['name'],
                'cart_price_rule_from'      => $this->getFormattedDate($entity['from_date']),
                'cart_price_rule_to'        => $this->getFormattedDate($entity['to_date']),
                'cart_price_rule_is_active' => (int) $entity['is_active'],
            ];
        }

        return $documents;
    }

    protected function getDocumentsCount(array $ids): int
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select();

        $select->from(
            $this->resource->getTableName('salesrule'),
            ['count' => new Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('rule_id IN (?)', $ids);
        }

        return (int) $connection->fetchOne($select);
    }
}
