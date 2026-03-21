<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_3_5($module)
{
    $services = AgMelhorEnvioService::getAll();

    foreach ($services as $service) {
        $carrier = $service->getCarrier();
        $carrier->shipping_handling = true;

        if (Validate::isLoadedObject($carrier)) {
        	$carrier->update();
        }
    }
    
    return true;
}
