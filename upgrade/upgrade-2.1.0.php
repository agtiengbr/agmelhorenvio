<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_1_0($module)
{
	$tab_id = Tab::getIdFromClassName('AdminAgMelhorEnvioDiscounts');
	$tab = new Tab($tab_id);
	$tab->module     = 'agmelhorenvio';
	$tab->active     = 0;
	$tab->class_name = 'AdminAgMelhorEnvioDiscounts';
	$tab->id_parent  = Tab::getIdFromClassName('agmelhorenvio');
	foreach (Language::getLanguages(true) as $lang) {
		$tab->name[$lang['id_lang']] = 'MelhorEnvio - Descontos';
	}
	
	$tab->save();

	require_once _PS_MODULE_DIR_ . $module->name . '/classes/AgMelhorEnvioDiscount.php';
	$model = new AgMelhorEnvioDiscount;
	$model->createDatabase();
	
	return true;
}
