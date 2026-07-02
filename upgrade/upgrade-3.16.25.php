<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_16_25($module)
{
    return (bool) $module->registerHook('actionAdminControllerSetMedia');
}
