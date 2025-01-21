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

use InvalidArgumentException;
use Magento\Framework\Stdlib\ArrayManager;

use function array_keys;
use function sprintf;

class IndexerConfig
{
    public function __construct(
        private ArrayManager $arrayManager,
        private array $config = []
    ) {
    }

    public function get(string $indexId, string $path = '', mixed $default = null): mixed
    {
        $config = $this->config[$indexId] ?? null;

        if (! $config) {
            throw new InvalidArgumentException(sprintf('Indexer "%s" not found in configuration', $indexId));
        }

        if ($path) {
            $config = $this->arrayManager->get("{$indexId}/$path", $this->config, $default);
        }

        return $config;
    }

    public function list(): array
    {
        return array_keys($this->config);
    }
}
