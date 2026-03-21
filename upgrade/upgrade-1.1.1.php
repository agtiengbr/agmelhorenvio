<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_1_1($module)
{
    //reinstala os overrides
    $module->uninstallOverrides();
    $module->installOverrides();

    return true;
}
