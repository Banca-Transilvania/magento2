<?php
/**
 * Copyright © Banca Transilvania. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BTRL\IpayCardOnFile\Model\Method;

class Vault extends \Magento\Vault\Model\Method\Vault
{
    public function isInitializeNeeded(): bool
    {
        return false;
    }
}
