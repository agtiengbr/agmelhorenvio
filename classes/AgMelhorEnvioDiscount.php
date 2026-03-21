<?php

class AgMelhorEnvioDiscount extends AgObjectModel
{
	public static $definition = [
		'table'     => 'agmelhorenvio_discount',
        'primary'   => 'id_agmelhorenvio_discount',
        'multilang' => false,
        'fields'    => [
            'id_agmelhorenvio_discount'=> ['type' => self::TYPE_INT,    'db_type' => 'int',         'validate' => 'isInt'],
            'id_agmelhorenvio_service' => ['type' => self::TYPE_INT,    'db_type' => 'int',         'validate' => 'isInt',         'required' => true],
            'alias'                    => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(50)', 'validate' => 'isGenericName', 'required' => true],
            'type_discount' 		   => ['type' => self::TYPE_INT,    'db_type' => 'int',   'validate' => 'isInt',               'required' => true],
            'discount' 				   => ['type' => self::TYPE_FLOAT,  'db_type' => 'float', 'validate' => 'isFloat',             'required' => true],
            'cart_value_begin'         => ['type' => self::TYPE_FLOAT,   'db_type' => 'float'],
            'cart_value_end'           => ['type' => self::TYPE_FLOAT,   'db_type' => 'float'],
            'active'                   => ['type' => self::TYPE_BOOL,   'db_type' => 'boolean', 'default' => 0]
        ]
	];

	public $id_agmelhorenvio_discount;
    public $id_agmelhorenvio_service;
    public $alias;
	public $type_discount;
	public $discount;
    public $cart_value_begin;
    public $cart_value_end;
    public $id_zone;
    public $active;



    public static function hasIntersectionWithOtherInterval($zipcode_begin, $zipcode_end, $cart_value_begin, $cart_value_end, $id_service, $id_interval)
    {
        $sql = 'SELECT disc.* FROM '.  _DB_PREFIX_ . 'agmelhorenvio_discount disc ';
        $sql .= 'INNER JOIN '._DB_PREFIX_.'agmelhorenvio_range_cep cep ON cep.id_agmelhorenvio_discount = disc.id_agmelhorenvio_discount';
        $sql .= ' WHERE CAST(cep.cep_end AS SIGNED INTEGER) >= '  .(int) $zipcode_begin;
        $sql .= ' AND CAST(cep.cep_start AS SIGNED INTEGER) <= '  .(int) $zipcode_end;
        $sql .= ' AND (disc.cart_value_end  >= '  .(float) $cart_value_begin . ' OR disc.cart_value_end = 0 OR disc.cart_value_end IS NULL)';
        $sql .= ' AND disc.cart_value_begin  <= '  .(float) $cart_value_end;
        $sql .= ' AND disc.id_agmelhorenvio_service=' . (int)$id_service;
        if ($id_interval) {
            $sql .= ' AND disc.id_agmelhorenvio_discount != ' . (int) $id_interval;
        }

        $db_data = Db::getInstance()->getRow($sql);
        
        if (!is_array($db_data)) {
            $db_data = array();
        }

        $return = new AgMelhorEnvioDiscount();
        $return->hydrate($db_data);

        return $return;
    }

    public static function getByZoneAndService($id_zone, $id_service, $id_interval)
    {
        $sql = 'SELECT * FROM '.  _DB_PREFIX_ . 'agmelhorenvio_discount ';
        $sql .= ' WHERE id_agmelhorenvio_service=' . (int)$id_service;
        $sql .= ' AND id_zone=' . (int)$id_zone;
        $sql .= ' AND active=1';


        if ($id_interval) {
            $sql .= ' AND id_agmelhorenvio_discount != ' . (int) $id_interval;
        }

        $db_data = Db::getInstance()->getRow($sql);
        
        if (!is_array($db_data)) {
            $db_data = array();
        }


        $return = new AgMelhorEnvioDiscount();
        $return->hydrate($db_data);

        return $return;
    }

    public static function getDiscountByPostcodeAndPrice($postcode, $price, $id_agmelhorenvio_service)
    {
        $postcode = str_replace('.', '', $postcode);
        $postcode = str_replace('-', '', $postcode);

        $sql = new DbQuery();
        $sql->select('dsc.*')
            ->from('agmelhorenvio_discount', 'dsc')
            ->join('INNER JOIN ' . _DB_PREFIX_ . 'agmelhorenvio_range_cep cep ON cep.id_agmelhorenvio_discount=dsc.id_agmelhorenvio_discount')
            ->where('CAST(cep.cep_start AS SIGNED INTEGER) <= ' . (int) $postcode)
            ->where('CAST(cep.cep_end AS SIGNED INTEGER) >= ' . (int) $postcode)
            ->where('dsc.cart_value_begin <= ' . (float) $price)
            ->where('dsc.cart_value_end >= ' . (float) $price . ' OR dsc.cart_value_end = 0 OR dsc.cart_value_end IS NULL')
            ->where('dsc.id_agmelhorenvio_service=' . (int) $id_agmelhorenvio_service)
            ->where('dsc.active=1');
            
        $discount = Db::getInstance()->getRow($sql);
        if (!is_array($discount)) {
            $discount = [];
        }

        $return = new AgMelhorEnvioDiscount;
        $return->hydrate($discount);

        return $return;
    }

    public function applyTo($price)
    {
        if ($this->type_discount == 1) {
            $return = max(0, $price - $this->discount);
        } else {
            $return = max(0, $price * (1 - $this->discount / 100));
        }

        return $return;
    }
}
