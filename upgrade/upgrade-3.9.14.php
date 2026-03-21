<?php

function upgrade_module_3_9_14($module)
{
    $models = array(
        'AgMelhorEnvioCache',
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

    $tab_id = Tab::getIdFromClassName('AdminAgMelhorEnvioCache');

	$tab = new Tab($tab_id);
	$tab->module     = 'agmelhorenvio';
	$tab->active     = 0;
	$tab->class_name = 'AdminAgMelhorEnvioCache';
	$tab->id_parent  = Tab::getIdFromClassName('agmelhorenvio');
	foreach (Language::getLanguages(true) as $lang) {
		$tab->name[$lang['id_lang']] = 'Cache';
	}
	
	$tab->save();

    if (file_exists(_PS_MODULE_DIR_ . 'agmelhorenvio/classes/AgMelhorCache.php')) {
        unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/classes/AgMelhorCache.php');
    }

    return true;
}
