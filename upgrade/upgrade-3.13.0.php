<?php

function upgrade_module_3_13_0($module)
{
    //busca os descontos para o formato atual
    $dbPrefix = _DB_PREFIX_;
    $discounts = Db::getInstance()->executeS("select * from {$dbPrefix}agmelhorenvio_discount");

    //atualiza os registros no banco de dados
    $classes = [
        'AgMelhorEnvioDiscount',
        'AgMelhorEnvioRangeCep'
    ];

    foreach ($classes as $class) {
        require_once _PS_MODULE_DIR_ . "agmelhorenvio/classes/{$class}.php";
        $obj = new $class;
        $obj->createMissingColumns();
    }

    Db::getInstance()->execute("ALTER TABLE {$dbPrefix}agmelhorenvio_discount drop column postcode_begin");
    Db::getInstance()->execute("ALTER TABLE {$dbPrefix}agmelhorenvio_discount drop column postcode_end");

    //salva os descontos no formato atual
    foreach ($discounts as $discount) {
        Db::getInstance()->insert('agmelhorenvio_range_cep', [
            'id_agmelhorenvio_discount' => $discount['id_agmelhorenvio_discount'],
            'cep_start' => $discount['postcode_begin'],
            'cep_end' => $discount['postcode_end'],
        ]);
    }

    return true;
}