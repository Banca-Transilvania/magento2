<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Model;

use BTRL\IpayCardOnFile\Model\Ui\ConfigProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Model\InfoInterface as PaymentInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\{
    OrderPaymentExtensionInterface,
    OrderPaymentExtensionInterfaceFactory
};
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\Data\{
    PaymentTokenFactoryInterface,
    PaymentTokenInterface
};
use Magento\Vault\Model\Ui\VaultConfigProvider;

class Vault
{
    const KEY_BINDING_INFO = 'bindingInfo';
    const KEY_BINDING_CUSTOMER_ID = 'clientId';
    const KEY_BINDING_ID = 'bindingId';
    const KEY_CARD_INFO = 'cardAuthInfo';
    const KEY_CARD_EXPIRATION = 'expiration';
    const KEY_CARD_HOLDER = 'cardholderName';
    const KEY_CARD_APPROVAL_CODE = 'approvalCode';
    const KEY_CARD_PAN = 'pan';

    private PaymentTokenFactoryInterface $paymentTokenFactory;
    private OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory;
    private PaymentTokenManagementInterface $paymentTokenManagement;
    private OrderRepositoryInterface $orderRepository;
    private Json $json;

    public function __construct(
        PaymentTokenFactoryInterface $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        OrderRepositoryInterface $orderRepository,
        Json $json
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->orderRepository = $orderRepository;
        $this->json = $json;
    }

    /**
     * @param mixed[] $response
     */
    public function saveCard(PaymentInfo $payment, array $response): void
    {
        $paymentToken = $this->createVaultPaymentToken($response);

        if ($paymentToken !== null) {
            // This is used to set visibility for saved token
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment->setAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE, true);
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);

            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $order = $payment->getOrder();
            $this->orderRepository->save($order);
        }
    }

    /**
     * @param mixed[] $response
     */
    protected function createVaultPaymentToken(array $response): ?PaymentTokenInterface
    {
        $bindingCustomerId = $this->getBindingCustomerId($response);
        $bindingId = $this->getBindingId($response);
        $cardExpiration = $this->getCardExpiration($response);
        $cardHolder = $this->getCardHolder($response);
        $cardPanMask = $this->getCardPanMask($response);

        if ($bindingId && $bindingCustomerId && $cardExpiration && $cardPanMask) {
            // Check if the card is already saved
            if ($this->getPaymentTokenByBinding($bindingId, $bindingCustomerId)) {
                return null;
            }

            /** @var PaymentTokenInterface $paymentToken */
            $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
            $paymentToken->setGatewayToken($bindingId)
                ->setExpiresAt($cardExpiration)
                ->setPaymentMethodCode(ConfigProvider::CODE)
                ->setIsActive(true);

            $paymentToken->setTokenDetails(
                (string)($this->json->serialize([
                    'pan' => $cardPanMask,
                    'expirationDate' => $cardExpiration,
                    'holder' => $cardHolder
                ]) ?: '{}')
            );

            return $paymentToken;
        }

        return null;
    }

    protected function getExtensionAttributes(PaymentInfo $payment): OrderPaymentExtensionInterface
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * @param mixed[] $response
     */
    protected function getBindingCustomerId(array $response): int
    {
        return (int)($response[self::KEY_BINDING_INFO][self::KEY_BINDING_CUSTOMER_ID] ?? 0);
    }

    /**
     * @param mixed[] $response
     */
    protected function getBindingId(array $response): string
    {
        return $response[self::KEY_BINDING_INFO][self::KEY_BINDING_ID] ?? '';
    }

    /**
     * @param mixed[] $response
     */
    protected function getCardExpiration(array $response): string
    {
        $expiration = $response[self::KEY_CARD_INFO][self::KEY_CARD_EXPIRATION] ?? '';

        $year = substr($expiration, 0, 4);
        $month = substr($expiration, 4);
        $date = new \DateTime($year . '-' . $month . '-' . '01');

        return $date->format('Y-m-t');
    }

    /**
     * @param mixed[] $response
     */
    protected function getCardHolder(array $response): string
    {
        return $response[self::KEY_CARD_INFO][self::KEY_CARD_HOLDER] ?? '';
    }

    /**
     * @param mixed[] $response
     */
    protected function getCardPanMask(array $response): string
    {
        return $response[self::KEY_CARD_INFO][self::KEY_CARD_PAN] ?? '';
    }

    protected function getPaymentTokenByBinding(string $bindingId, int $customerId): ?PaymentTokenInterface
    {
        return $this->paymentTokenManagement->getByGatewayToken(
            $bindingId,
            ConfigProvider::CODE,
            $customerId
        );
    }
}
