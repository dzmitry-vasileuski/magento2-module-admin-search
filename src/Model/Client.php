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

namespace Vasileuski\AdminSearch\Model;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\ObjectManagerInterface;
use Vasileuski\AdminSearch\Api\ClientInterface;

class Client implements ClientInterface
{
    private string $engine;
    private object $client;

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

    public function search(array $query): array
    {
        return match ($this->engine) {
            'elasticsuite' => $this->client->search($query),
            default => $this->client->query($query),
        };
    }

    public function bulk(array $query): void
    {
        match ($this->engine) {
            'elasticsuite' => $this->client->bulk($query),
            default => $this->client->bulkQuery($query),
        };
    }

    public function deleteIndex(string $index): void
    {
        $this->client->deleteIndex($index);
    }

    public function createIndex(string $index, array $params = []): void
    {
        $this->client->createIndex($index, $params);
    }

    public function indexExists(string $index): bool
    {
        return $this->client->indexExists($index);
    }
}
