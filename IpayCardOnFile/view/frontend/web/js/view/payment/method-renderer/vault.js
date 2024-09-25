/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/action/place-order',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'mage/url'
], function (
    ko,
    $,
    VaultComponent,
    placeOrderAction,
    globalMessageList,
    additionalValidators,
    fullScreenLoader,
    urlBuilder,
    storage,
    url
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            active: false,
            imports: {
                onActiveChange: 'active'
            },
            template: 'BTRL_IpayCardOnFile/payment/vault'
        },

        initObservable: function () {
            this._super().observe(['active']);

            return this;
        },

        onActiveChange: function (isActive) {
            if (!this.isChecked() && (this.active_method === this.getId())) {
                this.selectPaymentMethod();
                return;
            }

            if (!isActive) {
                return;
            }
        },

        /**
         * Return the payment method code.
         */
        getCode: function () {
            return 'btrl_ipay_cof';
        },

        getMaskedCard: function () {
            return this.details.pan;
        },

        getExpirationDate: function () {
            let objectDate = new Date(this.details.expirationDate),
                month = objectDate.getMonth(),
                year = objectDate.getFullYear().toString().slice(-2);

            return (month + 1) + '/' + year;
        },

        selectPaymentMethod: function () {
            this._super();

            // Update payment with vault public hash
            this.updatePaymentMethod(this.publicHash)

            return true;
        },

        getCardType: function () {
            return this.details.type;
        },

        placeOrder: async function() {
            let self = this;

            if (
                this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                // Place Order but use our own redirect url after
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);

                await $.when(
                    placeOrderAction(self.getData(), self.messageContainer)
                ).fail(
                    function (response) {
                        self.isPlaceOrderActionAllowed(true);
                        fullScreenLoader.stopLoader();
                    }
                ).done(
                    function (orderId) {
                        self.afterPlaceOrder();
                        self.getOrderRedirectUrl(orderId).done(
                            function(response) {
                                window.location.href = response;
                            }
                        );
                    }
                )
            }
        },

        updatePaymentMethod: function (publicHash) {
            storage.post(
                urlBuilder.createUrl('/btrl/ipay/update-payment', {}),
                JSON.stringify({
                    'public_hash': publicHash,
                    'payment_method_code': this.getId()
                })
            );
        },

        getOrderRedirectUrl: function(orderId) {
            return storage.get(urlBuilder.createUrl('/btrl/ipay/get-redirect-url/' + orderId, {}));
        }
    });
});
