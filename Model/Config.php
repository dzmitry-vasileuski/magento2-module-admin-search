<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config implements ArgumentInterface
{
    private const XML_PATH_ADMIN_SEARCH_STICKY_HEADER = 'admin/search/sticky_header';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
        //
    }

    /**
     * Check if sticky header is enabled in the admin configuration.
     *
     * @return bool
     */
    public function isStickyHeaderEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ADMIN_SEARCH_STICKY_HEADER);
    }
}
