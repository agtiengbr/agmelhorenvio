<?php

function upgrade_module_3_16_1($module)
{
    try {
        Db::getInstance(0)->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'agmelhorenvio_service add column additional_cost_type int'
        );
    } catch (Exception $e){}


    return true;
}