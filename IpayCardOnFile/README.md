## BT iPay Card on File

This is a plugin for Magento 2 BTRL_Ipay payment module.

### Magento versions compatibility :

**Which version should I use?**

| Magento Version                                   | Module Version                                                            |
|---------------------------------------------------|---------------------------------------------------------------------------|
| Magento **2.4.x** Opensource (CE) / Commerce (EE) | **100.x.x** latest release: ```composer require btrl/ipay-card-on-file``` |

### Requirements

The module requires :

- btrl/ipay > 100.x.x


### How to use

1. Install the module via Composer :

``` composer require btrl/ipay-card-on-file ```

2. Enable it

``` bin/magento module:enable BTRL_IpayCardOnFile ```

3. Install the module and rebuild the DI cache

``` bin/magento setup:upgrade ```


### How to configure

> Stores > Configuration > Sales > Payment Methods > BT iPay Payment > Enable Card On File
