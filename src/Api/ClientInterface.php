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

namespace Vasileuski\AdminSearch\Api;

interface ClientInterface
{
    public function search(array $query): array;

    public function bulk(array $query): void;

    public function deleteIndex(string $index): void;

    public function createIndex(string $index, array $params = []): void;

    public function indexExists(string $index): bool;
}
