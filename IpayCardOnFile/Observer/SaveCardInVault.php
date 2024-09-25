<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Observer;

use BTRL\IpayCardOnFile\Model\Vault;
use Magento\Framework\Event\{Observer, ObserverInterface};
use Psr\Log\LoggerInterface;

class SaveCardInVault implements ObserverInterface
{
    private Vault $vaultModel;
    private LoggerInterface $logger;

    public function __construct(
        Vault $vaultModel,
        LoggerInterface $logger
    ) {
        $this->vaultModel = $vaultModel;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment\Info $payment */
        $payment = $observer->getEvent()->getData('payment');
        /** @var mixed[] $response */
        $response = $observer->getEvent()->getData('response');

        try {
            $this->vaultModel->saveCard($payment, $response);
        } catch (\Exception $e) {
            $this->logger->critical('BT iPay Card On File: ' . $e->getMessage());
        }
    }
}
