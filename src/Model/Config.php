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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config implements ArgumentInterface
{
    private const XML_PATH_ADMIN_SEARCH_STICKY_HEADER = 'admin/search/sticky_header';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isStickyHeaderEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ADMIN_SEARCH_STICKY_HEADER);
    }
}
