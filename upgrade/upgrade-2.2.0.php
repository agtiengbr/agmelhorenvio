<?php

function upgrade_module_2_2_0($module)
{
    $models = array(
        'AgMelhorEnvioDiscount',
        'AgMelhorEnvioDiscountCartValue',
        'AgMelhorEnvioLabel',
        'AgMelhorEnvioOption',
        'AgMelhorEnvioPackage',
        'AgMelhorEnvioService',
        'AgMelhorEnvioServiceOptional',
        'AgMelhorEnvioServiceRequirement'
    );

    foreach ($models as $class) {
        require_once _PS_MODULE_DIR_ . $module->name . '/classes/' . $class . '.php';
        //instantiate the module
        $modelInstance = new $class();

        //create the table relative to this model in the database
        //if the table does not exists yet
        $modelInstance->createDatabase();

        //if the table already exists, add to it any column that may be missing.
        //this is useful in the case of new updates that require new columns
        //to exist in the table.
        $modelInstance->createMissingColumns();

        $modelInstance->createIndexes();
    }
    
    return true;
}
