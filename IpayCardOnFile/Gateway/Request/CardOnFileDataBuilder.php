<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

class CardOnFileDataBuilder implements BuilderInterface
{
    /**
     * @param mixed[] $buildSubject
     * @return mixed[]
     */
    public function build(array $buildSubject): array
    {
        $request['body'] = [];

        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDataObject->getPayment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $additionalData = $payment->getAdditionalInformation();
        if ($order->getCustomerId() && !empty($additionalData[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $request['body'] = [
                'clientId' => $order->getCustomerId()
            ];
        }

        $extensionAttributes = $payment->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $paymentToken = $extensionAttributes->getVaultPaymentToken();

            if (
                $paymentToken
                && $paymentToken->getGatewayToken()
                && $paymentToken->getCustomerId()
            ) {
                $request['body'] = [
                    'clientId' => $paymentToken->getCustomerId(),
                    'bindingId' => $paymentToken->getGatewayToken(),
                ];
            }
        }

        return $request;
    }
}
