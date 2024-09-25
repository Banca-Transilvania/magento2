<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Plugin;

use BTRL\Ipay\Model\{OrderProcessor, Ui\ConfigProvider};
use BTRL\IpayCardOnFile\Model\SavedCard;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\{Data\PaymentTokenInterface, PaymentTokenRepositoryInterface};

class RemoveSavedCard
{
    private SavedCard $savedCard;
    private StoreManagerInterface $storeManager;
    private MessageManagerInterface $messageManager;

    public function __construct(
        SavedCard $savedCard,
        StoreManagerInterface $storeManager,
        MessageManagerInterface $messageManager
    ) {
        $this->savedCard = $savedCard;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    public function afterDelete(
        PaymentTokenRepositoryInterface $subject,
        bool $result,
        PaymentTokenInterface $paymentToken
    ): bool {
        if ($result && $paymentToken->getPaymentMethodCode() === ConfigProvider::CODE) {
            $response = $this->savedCard->unbind(
                ['bindingId' => $paymentToken->getGatewayToken()],
                (int)$this->storeManager->getStore()->getId()
            );

            $responseErrorCode = (int)($response[OrderProcessor::PAYMENT_ERROR_CODE_KEY] ?? 0);
            $responseErrorMessage = $response[OrderProcessor::PAYMENT_ERROR_MESSAGE_KEY] ?? '';
            if ($responseErrorCode) {
                $this->messageManager->addErrorMessage(
                    __('Saved card cannot be removed from Banca Transilvania: %1. Please contact merchant or Banca Transilvania!', $responseErrorMessage)
                );
            } else {
                $tokenModel = $subject->getById($paymentToken->getEntityId());
                $tokenModel->delete(); /** @phpstan-ignore-line */
            }
        }

        return $result;
    }
}
