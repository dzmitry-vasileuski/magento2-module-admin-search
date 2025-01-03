<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Vasileuski\AdminSearch\Api\ClientInterface;

abstract class Indexer implements MviewActionInterface, IndexerActionInterface
{
    public const INDEXER_ID = '';
    public const BATCH_SIZE = 5000;

    public function __construct(
        protected ResourceConnection $resource,
        protected TimezoneInterface $timezone,
        protected ResolverInterface $localeResolver,
        protected IndexerConfig $indexerConfig,
        protected ClientInterface $client
    ) {}

    abstract protected function getDocuments(array $ids, int $page, int $pageSize): array;
    abstract protected function getDocumentsCount(array $ids): int;

    public function execute($ids)
    {
        if (!$ids && $this->client->indexExists($this->getIndexName())) {
            $this->client->deleteIndex($this->getIndexName());
        }

        if (!$this->client->indexExists($this->getIndexName())) {
            $this->client->createIndex(
                $this->getIndexName(),
                [
                    'mappings' => [
                        'properties' => $this->indexerConfig->get(
                            static::INDEXER_ID,
                            'properties'
                        )
                    ]
                ]
            );
        }

        $documentsCount = $this->getDocumentsCount($ids);
        $totalPages = max(1, ceil($documentsCount / static::BATCH_SIZE));
        $page = 1;

        while ($page <= $totalPages) {
            $documents = $this->getDocuments($ids, $page, self::BATCH_SIZE);
            $documentsToDelete = array_diff($ids, array_keys($documents));

            $bulk = [
                'index' => $this->getIndexName(),
                'body' => [],
            ];

            foreach ($documents as $id => $document) {
                $bulk['body'][] = [
                    'index' => [
                        '_id' => $id,
                    ]
                ];

                $bulk['body'][] = $document;
            }

            foreach ($documentsToDelete as $id) {
                $bulk['body'][] = [
                    'delete' => [
                        '_id' => $id,
                    ],
                ];
            }

            if ($bulk['body']) {
                $this->client->bulk($bulk);
            }

            $page++;
        }
    }

    public function executeFull()
    {
        $this->execute([]);
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    protected function getIndexName(): string
    {
        return static::INDEXER_ID;
    }

    protected function getFormattedDate(?string $date, ?string $timezone = null): string
    {
        if (!$date) {
            return '';
        }

        if ($timezone) {
            $date = new \DateTime($date, new \DateTimeZone($timezone));
        } else {
            $date = new \DateTime($date, new \DateTimeZone($this->timezone->getConfigTimezone()));
        }

        return $this->timezone->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $this->localeResolver->getDefaultLocale(),
        );
    }
}
