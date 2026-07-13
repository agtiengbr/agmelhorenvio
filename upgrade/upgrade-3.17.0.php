<?php

function upgrade_module_3_17_0($module)
{
    $models = [
        'AgMelhorEnvioService',
        'AgMelhorEnvioShipmentLog',
        'AgMelhorEnvioOrderNfe',
    ];

    foreach ($models as $class) {
        require_once _PS_MODULE_DIR_ . $module->name . '/classes/' . $class . '.php';
        $modelInstance = new $class();
        $modelInstance->createDatabase();
        $modelInstance->createMissingColumns();
        $modelInstance->createIndexes();
    }

    AgMelhorEnvioOrderNfe::ensureStorageProtection();

    $services = AgMelhorEnvioService::getAll();
    foreach ($services as $service) {
        if (!$service->shipment_type) {
            $service->shipment_type = AgMelhorEnvioShipmentTypesEnum::HYBRID;
        }
        $service->normalizeShipmentSettings();
        $service->update();
    }

    return true;
}
