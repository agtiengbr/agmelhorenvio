<?php

function upgrade_module_3_0_0($module)
{
    $models = array(
        'AgMelhorEnvioDiscount',
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
    
    //todos os descontos por faixas de CEP são salvos com preço de 0 a 999999
    Db::getInstance()->update('agmelhorenvio_discount', ['cart_value_begin' => 0, 'cart_value_end' => 999999]);

    //todos os descontos baseados no valor do carrinho são salvos com CEP de 0 a 99999999
    $sql = 'insert into ' . _DB_PREFIX_ . 'agmelhorenvio_discount (id_agmelhorenvio_service, alias, type_discount, discount, postcode_begin, postcode_end, active, cart_value_begin, cart_value_end)
        SELECT id_agmelhorenvio_service, alias, 1, discount, \'00000000\', \'99999999\', active, cart_value_begin, cart_value_end FROM ' . _DB_PREFIX_ . 'agmelhorenvio_discount_cart_value';

    Db::getInstance()->execute($sql);
    

    $tabs_to_delete = [
        'AdminAgMelhorEnvioDiscountsCartValue',
        'AdminAgMelhorEnvioLabelsPrint'
    ];

    foreach ($tabs_to_delete as $tab_name) {
        $tab = new Tab(Tab::getIdFromClassName($tab_name));
        if (Validate::isLoadedObject($tab)) {
            $tab->delete();
        }
    }

    $module->registerHook('actionAdminOrdersListingResultsModifier');

    $module->uninstallOverrides();
    $module->installOverrides();

    Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_DISPLAY_SIMULATION_IN_PRODUCT_PAGE', 2);

    return true;
}