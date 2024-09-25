<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Block\Customer;

use BTRL\IpayCardOnFile\Model\Ui\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

class CardRenderer extends AbstractCardRenderer
{
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE;
    }

    public function getNumberLast4Digits()
    {
        $details = $this->getTokenDetails();

        return $details['pan'] ?? '';
    }

    public function getExpDate(?string $format = 'm/y')
    {
        $expiration = $this->getToken()->getExpiresAt();
        $date = new \DateTime($expiration);

        return $date->format($format);
    }

    public function getIconUrl()
    {
        return '';
    }

    public function getIconHeight()
    {
        return 0;
    }

    public function getIconWidth()
    {
        return 0;
    }
}
