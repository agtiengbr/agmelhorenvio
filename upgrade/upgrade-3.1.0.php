<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_1_0($module)
{
    Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SANDBOX_EMAIL', 0);

    return true;
}
