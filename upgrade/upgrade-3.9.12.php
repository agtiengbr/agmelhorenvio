<?php

function upgrade_module_3_9_12($module)
{
    Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'agmelhorenvio_request ADD COLUMN time_spent FLOAT AFTER body');

    return true;
}
