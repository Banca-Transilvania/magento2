<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class UpdatePaymentMethod implements \BTRL\IpayCardOnFile\Api\UpdatePaymentMethod
{
    private CheckoutSession $checkoutSession;
    private CustomerSession $customerSession;
    private RestRequest $restRequest;
    private CartRepositoryInterface $cartRepository;

    public function __construct(
        RestRequest $restRequest,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        CartRepositoryInterface $cartRepository
    ) {
        $this->restRequest = $restRequest;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->cartRepository = $cartRepository;
    }

    public function update(): bool
    {
        $postData = $this->restRequest->getRequestData();
        $publicHash = $postData['public_hash'] ?? null;
        $paymentMethodCode = $postData['payment_method_code'] ?? null;
        $customerId = $this->customerSession->getCustomerId();

        if (!$publicHash || !$customerId) {
            return false;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();

        $payment->setAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH, $publicHash)
            ->setAdditionalInformation(PaymentTokenInterface::CUSTOMER_ID, $customerId)
            ->setAdditionalInformation(PaymentTokenInterface::PAYMENT_METHOD_CODE, $paymentMethodCode);
        $quote->setPayment($payment);

        $this->cartRepository->save($quote);

        return true;
    }
}
