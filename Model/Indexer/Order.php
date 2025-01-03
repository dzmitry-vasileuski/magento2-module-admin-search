<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order\Config;
use Vasileuski\AdminSearch\Api\ClientInterface;
use Vasileuski\AdminSearch\Model\Indexer;
use Vasileuski\AdminSearch\Model\IndexerConfig;

class Order extends Indexer
{
    public const INDEXER_ID = 'admin_search_orders';

    public function __construct(
        private Config $orderConfig,
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
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            $this->resource->getTableName('sales_order'),
            [
                'entity_id',
                'increment_id',
                'status',
                'customer_firstname',
                'customer_lastname',
                'customer_email',
                'created_at',
            ]
        );

        if ($ids) {
            $select->where('entity_id IN (?)', $ids);
        }

        $select->where('created_at > DATE_SUB(NOW(), INTERVAL 1 YEAR)');

        $select->limitPage($page, $pageSize);

        $entities = $connection->fetchAll($select);
        $statuses = $this->orderConfig->getStatuses();
        $documents = [];

        foreach ($entities as $entity) {
            $documents[$entity['entity_id']] = [
                'order_increment_id' => $entity['increment_id'],
                'order_status' => $statuses[$entity['status']] ?? null,
                'order_customer_firstname' => $entity['customer_firstname'],
                'order_customer_lastname' => $entity['customer_lastname'],
                'order_customer_email' => $entity['customer_email'],
                'order_created_at' => $this->getFormattedDate($entity['created_at'], 'UTC'),
            ];
        }

        return $documents;
    }

    protected function getDocumentsCount(array $ids): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        $select->from(
            $this->resource->getTableName('sales_order'),
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        );

        if ($ids) {
            $select->where('entity_id IN (?)', $ids);
        }

        $select->where('created_at > DATE_SUB(NOW(), INTERVAL 1 YEAR)');

        return (int) $connection->fetchOne($select);
    }
}
