<?php

class AgMelhorEnvioSellerDataZipcode extends AgObjectModel
{
    public static $definition = [
        'table' => 'agmelhorenvio_seller_data_zipcode',
        'primary' => 'id_agmelhorenvio_seller_data_zipcode',
        'fields' => [
            'id_agmelhorenvio_seller_data_zipcode' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'zipcode' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(16)'),
            'shop_name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(128)'),
            'address' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(128)'),
            'number' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(8)'),
            'district' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(128)'),
            'city' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(128)'),
            'uf' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(2)'),
            'phone' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(16)'),
            'cnpj' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(20)'),
            'state_register' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(20)'),
            'agency_jadlog' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(20)'),
            'agency_latam' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(20)'),
        ],
        'indexes' => [
            [
                'fields' => ['zipcode'],
                'prefix' => 'unique',
                'name' => 'uniqueness'
            ]
        ]
    ];

    public $id_agmelhorenvio_seller_data_zipcode;
    public $zipcode;
    public $shop_name;
    public $address;
    public $number;
    public $district;
    public $city;
    public $uf;
    public $phone;
    public $cnpj;
    public $state_register;
    public $agency_jadlog;
    public $agency_latam;

    public static function getByZipcode($zipcode)
    {
        $sql = new DbQuery;
        $sql->from('agmelhorenvio_seller_data_zipcode')
            ->select('id_agmelhorenvio_seller_data_zipcode')
            ->where('zipcode="' . pSQL($zipcode) . '"');

        $id = Db::getInstance()->getValue($sql);
        return new AgMelhorEnvioSellerDataZipcode($id);
    }
}