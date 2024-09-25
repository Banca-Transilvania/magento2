<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\ViewModel;

use BTRL\Ipay\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CardRendererViewModel implements ArgumentInterface
{
    private ConfigProvider $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function getMethodTitle(): string
    {
        return (string)$this->configProvider->getGatewayConfig()->getValue('title');
    }
}
