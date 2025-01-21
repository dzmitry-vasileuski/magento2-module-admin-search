<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config implements ArgumentInterface
{
    private const XML_PATH_ADMIN_SEARCH_STICKY_HEADER = 'admin/search/sticky_header';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {}

    public function isStickyHeaderEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ADMIN_SEARCH_STICKY_HEADER);
    }
}
