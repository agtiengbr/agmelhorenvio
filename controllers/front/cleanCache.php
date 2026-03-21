<?php
class agmelhorenviocleanCacheModuleFrontController extends FrontController
{
    public function init()
    {
        Db::getInstance()->delete("agmelhorenvio_cache", "date_add <= '" . date("Y-m-d H:i:s", strtotime('-' . Configuration::get("agmelhorenvio_time_expire_cache") . ' hours')) . "'");
        exit();
    }
}