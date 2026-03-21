<?php

function upgrade_module_3_13_9($module)
{
    $module->installWorkers();
    return true;
}