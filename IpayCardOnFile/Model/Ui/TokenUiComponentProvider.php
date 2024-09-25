<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Model\Ui;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\{
    TokenUiComponentInterface,
    TokenUiComponentProviderInterface,
    TokenUiComponentInterfaceFactory
};

class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    private CheckoutSession $checkoutSession;
    private TokenUiComponentInterfaceFactory $componentFactory;
    private ConfigProvider $configProvider;
    private Json $json;

    public function __construct(
        CheckoutSession $checkoutSession,
        TokenUiComponentInterfaceFactory $componentFactory,
        ConfigProvider $configProvider,
        Json $json
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->componentFactory = $componentFactory;
        $this->configProvider = $configProvider;
        $this->json = $json;
    }

    public function getComponentForToken(PaymentTokenInterface $paymentToken): TokenUiComponentInterface
    {
        $jsonDetails = $this->json->unserialize((string)$paymentToken->getTokenDetails());

        $storeId = (int)$this->configProvider->getStoreManager()->getStore()->getId();
        $this->configProvider->getGatewayConfig()->setMethodCode(ConfigProvider::VAULT_CODE);
        $isVaultActive = $this->configProvider->getGatewayConfig()->isActive($storeId);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $activeMethod = $payment->getAdditionalInformation(PaymentTokenInterface::PAYMENT_METHOD_CODE);

        return $this->componentFactory->create(
            [
                'config' => [
                    'code' => ($isVaultActive ? ConfigProvider::VAULT_CODE : 'none'),
                    'active_method' => $activeMethod,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'BTRL_IpayCardOnFile/js/view/payment/method-renderer/vault'
            ]
        );
    }
}
