<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Vasileuski\AdminSearch\Model\Search as AdminSearchIndex;

class Index extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param AdminSearchIndex $index
     */
    public function __construct(
        Context $context,
        private AdminSearchIndex $index
    ) {
        parent::__construct($context);
    }

    /**
     * Process search request and return JSON response with search results.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $query = $this->getRequest()->getParam('query');
        $result = $this->index->search($query);

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * @inheritDoc
     */
    protected function _validateSecretKey(): bool
    {
        return true;
    }
}
