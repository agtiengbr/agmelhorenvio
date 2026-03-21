<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_9_0($module)
{
	$module->registerHook([
	    'displayAdminOrderTabContent',
        'displayAdminOrderTabLink'
    ]);

    return true;
}
