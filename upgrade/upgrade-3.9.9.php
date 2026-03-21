<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_9_9($module)
{
    $module->installWorkers();
    return true;
}
