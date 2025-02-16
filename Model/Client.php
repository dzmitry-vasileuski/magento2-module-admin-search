<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\ObjectManagerInterface;
use Vasileuski\AdminSearch\Api\ClientInterface;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    private string $engine;

    /**
     * @var \Magento\AdvancedSearch\Model\Client\ClientInterface|\Smile\ElasticsuiteCore\Api\Client\ClientInterface
     */
    private object $client;

    /**
     * @param ClientResolver $clientResolver
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ClientResolver $clientResolver,
        ObjectManagerInterface $objectManager
    ) {
        $this->engine = $clientResolver->getCurrentEngine();

        $this->client = match ($this->engine) {
            'elasticsuite' => $objectManager->get(\Smile\ElasticsuiteCore\Api\Client\ClientInterface::class),
            default => $clientResolver->create(),
        };
    }

    /**
     * @inheritDoc
     */
    public function search(array $query): array
    {
        return match ($this->engine) {
            'elasticsuite' => $this->client->search($query),
            default => $this->client->query($query),
        };
    }

    /**
     * @inheritDoc
     */
    public function bulk(array $query): void
    {
        match ($this->engine) {
            'elasticsuite' => $this->client->bulk($query),
            default => $this->client->bulkQuery($query),
        };
    }

    /**
     * @inheritDoc
     */
    public function deleteIndex(string $index): void
    {
        $this->client->deleteIndex($index);
    }

    /**
     * @inheritDoc
     */
    public function createIndex(string $index, array $params = []): void
    {
        $this->client->createIndex($index, $params);
    }

    /**
     * @inheritDoc
     */
    public function indexExists(string $index): bool
    {
        return $this->client->indexExists($index);
    }
}
