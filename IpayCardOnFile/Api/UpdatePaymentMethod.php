<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Api;

interface UpdatePaymentMethod
{
    /**
     * @return bool
     */
    public function update(): bool;
}
