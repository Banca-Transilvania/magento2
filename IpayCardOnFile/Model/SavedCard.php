<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Model;

use BTRL\Ipay\Model\Client;
use BTRL\IpayCardOnFile\Plugin\AddUnbindActionToConfigurationModel;
use Magento\Framework\Exception\NoSuchEntityException;

class SavedCard
{
    private Client $client;

    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * @param mixed[] $request
     * @return mixed[]
     * @throws NoSuchEntityException
     */
    public function unbind(array $request, int $storeId): array
    {
        return $this->client->makeRequest(
            AddUnbindActionToConfigurationModel::UNBIND_SAVED_CARD_ACTION,
            $request,
            [],
            $storeId
        );
    }
}
