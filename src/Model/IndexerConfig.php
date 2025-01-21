<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model;

use Magento\Framework\Stdlib\ArrayManager;

class IndexerConfig
{
    public function __construct(
        private ArrayManager $arrayManager,
        private array $config = []
    ) {}

    public function get(string $indexId, string $path = '', mixed $default = null): mixed
    {
        $config = $this->config[$indexId] ?? null;

        if (!$config) {
            throw new \InvalidArgumentException(sprintf('Indexer "%s" not found in configuration', $indexId));
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
