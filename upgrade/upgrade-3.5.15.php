<?php

function upgrade_module_3_5_15($module)
{
    AgMelhorEnvioConfiguration::setShopAddressPhone(Configuration::get("PS_SHOP_PHONE"));

    return true;
}
