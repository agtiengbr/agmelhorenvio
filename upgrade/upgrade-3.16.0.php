<?php

function upgrade_module_3_16_0($module)
{
    try {
        Db::getInstance(0)->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'agmelhorenvio_service add column additional_cost float not null default 0'
        );
    } catch (Exception $e){}

    try {
        Db::getInstance(0)->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'agmelhorenvio_service add column additional_delay int default 0'
        );
    } catch (Exception $e){}


    return true;
}