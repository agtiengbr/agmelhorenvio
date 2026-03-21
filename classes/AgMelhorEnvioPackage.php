<?php
class AgMelhorEnvioPackage extends AgObjectModel
{
    public static $definition = array(
        'table'     => 'agmelhorenvio_package',
        'primary'   => 'id_agmelhorenvio_package',
        'multilang' => false,
        'fields'    => array(
            'id_agmelhorenvio_package' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            //largura em milímetros
            'width' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'required' => true),
            //altura em milímetros
            'height' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'requied' => true),
            //profundidade em mílimetros
            'depth' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'required' => true),
            //peso em gramas
            'weight' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'required' => true),
            'quantity' => array('type' => self::TYPE_BOOL, 'db_type' => 'int', 'default' => '0'),
            'is_infinite' => array('type' => self::TYPE_BOOL, 'db_type' => 'bool', 'default' => 0),
            'is_active' => array('type' => self::TYPE_BOOL, 'db_type' => 'bool', 'default' => 0),
            'price' => array('type' => self::TYPE_FLOAT, 'db_type' => 'float'),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type' => 'datetime'
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type' => 'datetime'
            ),
        ),
        'indexes' => array(
            array(
                'fields' => array('name'),
                'prefix' => 'unique',
                'name' => 'unique_package_name'
            ),
        )
    );

    public $id_agmelhorenvio_package;
    public $name;
    public $width;
    public $height;
    public $depth;
    public $weight;
    public $quantity;
    public $is_infinite;
    public $is_active;
    public $price;
    public $date_add;
    public $date_upd;

    public static function getByName($package_name)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_package')
            ->where('name = "' . pSQL($package_name) . '"');

        $db_data = Db::getInstance()->getRow($sql);

        if (!is_array($db_data)) {
            $db_data = array();
        }

        $return = new AgMelhorEnvioPackage();
        $return->hydrate($db_data);

        return $return;
    }

    public static function getAll()
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_package')
            ->where('is_active = 1');

        $db_data = Db::getInstance()->getRow($sql);

        if (!$is_array($db_data)) {
            $db_data = array();
        }

        return ObjectModel::hydrateCollection('AgMelhorEnvioPackage', $db_data);
    }
}
