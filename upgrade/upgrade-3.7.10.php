<?php

function upgrade_module_3_7_10($module)
{
    AgMelhorEnvioConfiguration::setAgmelhorenvioStatusMappingEnabled(true);
    return true;
}
