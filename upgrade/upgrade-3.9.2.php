<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_9_2($module)
{
    $models = array(
        'AgMelhorEnvioRequest'
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

    $tab_id = Tab::getIdFromClassName('AdminAgMelhorEnvioRequests');
	$tab = new Tab($tab_id);
	$tab->module     = 'agmelhorenvio';
	$tab->active     = 1;
	$tab->class_name = 'AdminAgMelhorEnvioRequest';

    //cria abas ps 1.6
    if (version_compare(_PS_VERSION_, '1.7', '<')) {
    	$tab->id_parent  = Tab::getIdFromClassName('AdminParentShipping');

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'MelhorEnvio - Requisições';
        }       
    } else {
    	$tab->id_parent  = Tab::getIdFromClassName('agmelhorenvio');

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Requisições';
        }
    }
	
	$tab->save();

    return true;
}
