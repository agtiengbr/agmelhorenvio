<?php

function upgrade_module_3_16_19($module)
{
    try {
        if ((int) Configuration::get('AGMELHORENVIO_AUTO_GENERATE_LABELS') !== 1) {
            return true;
        }

        $sql = new DbQuery();
        $sql->from('order_state');
        $sql->select('id_order_state');
        $sql->where('paid = 1');

        $rows = Db::getInstance()->executeS($sql);
        $state_ids = [];
        foreach ((array) $rows as $row) {
            $state_id = (int) $row['id_order_state'];
            if ($state_id > 0) {
                $state_ids[] = $state_id;
            }
        }

        Configuration::updateValue('AGMELHORENVIO_AUTO_GENERATE_LABEL_STATES', implode(',', array_unique($state_ids)));
    } catch (Exception $e) {
        return false;
    }

    return true;
}
