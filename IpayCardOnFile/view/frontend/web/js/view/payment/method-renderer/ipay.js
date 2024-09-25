/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'mage/cookies'
],
function (
    $,
    Component,
    VaultEnabler,
    placeOrderAction,
    additionalValidators,
    fullScreenLoader,
    urlBuilder,
    storage
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'BTRL_IpayCardOnFile/payment/ipay'
        },

        initialize: function () {
            let self = this;

            self._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            return self;
        },

        context: function() {
            return this;
        },

        getControllerName: function() {
            return window.checkoutConfig.payment.iframe.controllerName[this.getCode()];
        },

        getPlaceOrderUrl: function() {
            return window.checkoutConfig.payment.iframe.placeOrderUrl[this.getCode()];
        },

        // Default payment functions
        setPlaceOrderHandler: function(handler) {
            this.placeOrderHandler = handler;
        },

        setValidateHandler: function(handler) {
            this.validateHandler = handler;
        },

        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },

        getCode: function () {
            return 'btrl_ipay';
        },

        placeOrder: async function() {
            let self = this;

            if (this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                // Add save in vault option to additional data
                let data = self.getData(),
                    saveInVault = self.vaultEnabler.isActivePaymentTokenEnabler();
                if (data['additional_data']) {
                    data['additional_data']['is_active_payment_token_enabler'] = saveInVault;
                } else {
                    data['additional_data'] = {'is_active_payment_token_enabler': saveInVault};
                }

                // Place Order but use our own redirect url after
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);

                await $.when(
                    placeOrderAction(data, self.messageContainer)
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
                );
            }
        },

        getOrderRedirectUrl: function(orderId) {
            return storage.get(urlBuilder.createUrl('/btrl/ipay/get-redirect-url/' + orderId, {}));
        }
    });
});
