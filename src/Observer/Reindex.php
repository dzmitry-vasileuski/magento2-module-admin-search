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

namespace Vasileuski\AdminSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;

class Reindex implements ObserverInterface
{
    public function __construct(
        private IndexerRegistry $indexerRegistry,
        private string $indexerId,
        private array $fields = [],
    ) {
    }

    public function execute(Observer $observer): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerId);

        if ($indexer->isScheduled()) {
            return;
        }

        $object     = $observer->getDataObject();
        $hasChanges = false;

        foreach ($this->fields as $field) {
            if ($object->dataHasChangedFor($field)) {
                $hasChanges = true;
                break;
            }
        }

        if ($hasChanges || $object->isDeleted()) {
            $indexer->reindexRow($object->getId());
        }
    }
}
