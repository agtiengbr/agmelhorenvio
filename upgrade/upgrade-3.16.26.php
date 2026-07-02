<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_16_26($module)
{
    $ok = true;

    if (!$module->isRegisteredInHook('actionAdminControllerSetMedia')) {
        $ok = $module->registerHook('actionAdminControllerSetMedia') && $ok;
    }

    return (bool) $ok;
}
