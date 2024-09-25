<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Plugin;

use BTRL\Ipay\Gateway\Config\Config;

class AddUnbindActionToConfigurationModel
{
    // Endpoints
    const UNBIND_SAVED_CARD_ENDPOINT = '/payment/rest/unBindCard.do';

    // Gateway actions
    const UNBIND_SAVED_CARD_ACTION = 'unbind';

    public function afterGetGatewayUrl(
        Config $subject,
        string $url,
        string $paymentAction,
        ?int $storeId = null
    ): string {
        $baseUrl = Config::GATEWAY_PRODUCTION_URL;
        if ($subject->isTestMode($storeId)) {
            $baseUrl = Config::GATEWAY_TEST_URL;
        }

        if ($paymentAction === self::UNBIND_SAVED_CARD_ACTION) {
            $url = $baseUrl . self::UNBIND_SAVED_CARD_ENDPOINT;
        }

        return $url;
    }
}
