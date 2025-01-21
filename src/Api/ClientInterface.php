<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Api;

interface ClientInterface
{
    public function search(array $query): array;
    public function bulk(array $query): void;
    public function deleteIndex(string $index): void;
    public function createIndex(string $index, array $params = []): void;
    public function indexExists(string $index): bool;
}
