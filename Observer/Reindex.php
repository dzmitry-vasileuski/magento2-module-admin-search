<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;

class Reindex implements ObserverInterface
{
    /**
     * @param IndexerRegistry $indexerRegistry
     * @param string $indexerId
     * @param array $fields
     */
    public function __construct(
        private IndexerRegistry $indexerRegistry,
        private string $indexerId,
        private array $fields = [],
    ) {
        //
    }

    /**
     * Reindex the specified index row when an object is saved or deleted.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerId);

        if ($indexer->isScheduled()) {
            return;
        }

        $object = $observer->getDataObject();
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
