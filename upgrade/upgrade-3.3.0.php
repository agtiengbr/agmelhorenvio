<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_3_0($module)
{
	AgMelhorEnvioService::installServices();
	
    return true;
}
