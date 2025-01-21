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

namespace Vasileuski\AdminSearch\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Vasileuski\AdminSearch\Model\Search as AdminSearchIndex;

class Index extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private AdminSearchIndex $index
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $query  = $this->getRequest()->getParam('query');
        $result = $this->index->search($query);

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    protected function _validateSecretKey(): bool
    {
        return true;
    }
}
