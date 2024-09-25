<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Model\Ui;

class ConfigProvider extends \BTRL\Ipay\Model\Ui\ConfigProvider
{
    const VAULT_CODE = 'btrl_ipay_cof';

    /**
     * @return mixed[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig(): array
    {
        $storeId = (int)$this->getStoreManager()->getStore()->getId();
        $isActive = $this->getGatewayConfig()->isActive($storeId);

        $paymentConfig = [
            'payment' => [
                self::CODE => [
                    'isActive' => $isActive,
                    'successPage' => $this->getUrlBuilder()->getUrl(
                        'checkout/onepage/success',
                        ['_secure' => $this->getRequest()->isSecure()]
                    )
                ]
            ]
        ];

        if ($this->isVaultActive()) {
            $paymentConfig['payment'][self::CODE]['vaultCode'] = self::VAULT_CODE;
        }

        return $paymentConfig;
    }

    public function isVaultActive(): bool
    {
        $storeId = (int)$this->getStoreManager()->getStore()->getId();
        $this->getGatewayConfig()->setMethodCode(self::VAULT_CODE);
        $isActive = $this->getGatewayConfig()->isActive($storeId);
        $this->getGatewayConfig()->setMethodCode(self::CODE);

        return $isActive;
    }
}
