<?php

function upgrade_module_3_16_17($module)
{
    if (Configuration::get('AGMELHORENVIO_AGENCY_TOTAL_EXPRESS') === false) {
        Configuration::updateValue('AGMELHORENVIO_AGENCY_TOTAL_EXPRESS', 0);
    }

    return true;
}
