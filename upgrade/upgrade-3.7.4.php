<?php

function upgrade_module_3_7_4($module)
{
    mkdir(_PS_MODULE_DIR_ . $module->name . '/cache', 755);

    return true;
}
