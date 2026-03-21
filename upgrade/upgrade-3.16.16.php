<?php

function upgrade_module_3_16_16($module)
{
    $module->registerHook([
        'displayPDFInvoice',
    ]);

    return true;
}

