<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Vasileuski\AdminSearch\Model\Search as AdminSearchIndex;

class Index extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private AdminSearchIndex $index
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $query = $this->getRequest()->getParam('query');
        $result = $this->index->search($query);

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    protected function _validateSecretKey(): bool
    {
        return true;
    }
}
