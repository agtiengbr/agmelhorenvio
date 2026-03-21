<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_3_3($module)
{
    $services = AgMelhorEnvioService::getAll();

    foreach ($services as $service) {
        $carrier = $service->getCarrier();

        if (!Validate::isLoadedObject($carrier)) {
        	continue;
        }

        $carrier->shipping_handling = true;
        $carrier->update();
    }
    
    return true;
}
