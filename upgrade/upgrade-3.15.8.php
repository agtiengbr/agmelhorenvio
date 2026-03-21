<?php

function upgrade_module_3_15_8($module)
{
    $module->RemakeWorkers();

    return true;
}