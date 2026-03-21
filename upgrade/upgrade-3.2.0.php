<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_2_0($module)
{
	$module->uninstallOverrides();

	unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/override/classes/Cart.php');
	rmdir(_PS_MODULE_DIR_ . 'agmelhorenvio/override/classes');
	rmdir(_PS_MODULE_DIR_ . 'agmelhorenvio/override');

	unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/controllers/front/Simulate.php');

	unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/views/js/agmelhorenvio.js');
	unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/views/js/agmelhorenvio.ps16js');

	unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/views/css/agmelhorenvio.css');
	rmdir(_PS_MODULE_DIR_ .  'agmelhorenvio/views/css');

	unlink(_PS_MODULE_DIR_ . 'agmelhorenvio/views/templates/hook/hookProductButtons.tpl');

	$agcliente = new agcliente;
	$agcliente->installOverrides();

    return true;
}
