<?php

function upgrade_module_3_15_0()
{
    $db_prefix = _DB_PREFIX_;
    $sql = "TRUNCATE {$db_prefix}agmelhorenvio_cache";
    Db::getInstance()->execute($sql);

    return true;
}