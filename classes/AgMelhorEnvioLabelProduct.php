<?php
class AgMelhorEnvioLabelProduct extends AgObjectModel
{
    public static $definition = [
        'table'     => 'agmelhorenvio_label_product',
        'primary'   => 'id_agmelhorenvio_label_product',
        'multilang' => false,
        'fields'    => [
            'id_agmelhorenvio_label_product' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_agmelhorenvio_label' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'db_type' => 'int unsigned'],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'db_type' => 'int unsigned'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'db_type' => 'int unsigned', 'default' => 0],
            'quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'db_type' => 'int unsigned'],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'db_type' => 'varchar(255)'],
            // 'width' =>  ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'db_type' => 'float'],
            // 'height' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'db_type' => 'float'],
            // 'length' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'db_type' => 'float'],
            // 'weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'db_type' => 'float'],
            'unitary_value'   => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'db_type' => 'float'],
            // 'insurance_value' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'db_type' => 'float']
        ]
    ];

    public $id_agmelhorenvio_label_product;
    public $id_agmelhorenvio_label;
    public $id_product;
    public $id_product_attribute;
    public $quantity;
    public $name;
    // public $width;
    // public $height;
    // public $length;
    // public $weight;
    public $unitary_value;
    // public $insurance_value;

    /**
     * Retorna o ID do valor da feature para um produto.
     * @param int $id_product
     * @param int $id_feature
     * @return int|null
     */
    public static function getFeatureValueId($id_product, $id_feature)
    {
        $sql = new DbQuery();
        $sql->select('id_feature_value');
        $sql->from('feature_product');
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_feature = ' . (int)$id_feature);
        $id = Db::getInstance()->getValue($sql);
        return $id ? (int)$id : null;
    }

    /**
     * Retorna o ID da feature pelo nome exato.
     * @param string $name
     * @return int|null
     */
    public static function getFeatureIdByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_feature');
        $sql->from('feature_lang');
        $sql->where('name = "' . pSQL($name) . '"');
        $sql->where('id_lang = ' . (int)Configuration::get('PS_LANG_DEFAULT'));
        $id = Db::getInstance()->getValue($sql);
        return $id ? (int)$id : null;
    }

    /**
     * Retorna o nome mascarado do produto, usando a feature AGMELHORENVIO_PRODUCT_NAME se existir,
     * senão retorna o nome do produto do PrestaShop, já removendo caracteres especiais.
     */
    public static function getMaskedProductName($id_product, $id_product_attribute = 0, $fallbackName = '')
    {
        $finalName = '';
        if (class_exists('Feature') && class_exists('Product')) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
            $id_feature = (int)self::getFeatureIdByName('MASKED_NAME');
            if ($id_feature) {
                $id_feature_value = (int)self::getFeatureValueId($id_product, $id_feature);
                if ($id_feature_value) {
                    $feature_value = new FeatureValue($id_feature_value, $id_lang);
                    if (!empty($feature_value->value)) {
                        $finalName = $feature_value->value;
                    }
                }
            }
            if (!$finalName) {
                $finalName = Product::getProductName($id_product, $id_product_attribute, $id_lang);
            }
        }
        if (!$finalName) {
            $finalName = $fallbackName;
        }
        // Remove caracteres especiais conforme padrão do agbling
        $finalName = str_replace(['^','<','>',';','=','#','{','}'], '', $finalName);
        return $finalName;
    }

    public static function create($id_agmelhorenvio_label, $quantity, $id_product, $id_product_attribute, $name, $unitary_value)
    {
        $obj = new AgMelhorEnvioLabelProduct;

        // Sempre usa o nome mascarado
        $finalName = self::getMaskedProductName($id_product, $id_product_attribute, $name);

        $obj->id_agmelhorenvio_label = $id_agmelhorenvio_label;
        $obj->id_product = $id_product;
        $obj->id_product_attribute = $id_product_attribute;
        $obj->quantity = $quantity;
        $obj->name = $finalName;
        $obj->unitary_value = round($unitary_value, 2);

        $obj->add();

        return $obj;
    }

    public static function getFromLabel($id_agmelhorenvio_label)
    {
        $sql = new DbQuery;
        $sql->from('agmelhorenvio_label_product')
            ->where('id_agmelhorenvio_label=' . (int)$id_agmelhorenvio_label);

        return Db::getInstance()->executeS($sql);
    }

    public static function GetCombinationWeight($id_product_attribute, $id_shop)
    {
        $sql = new DbQuery;
        $sql->from('product_attribute_shop')
            ->where('id_product_attribute=' . (int) $id_product_attribute)
            ->where('id_shop=' . (int) $id_shop)
            ->select('weight');

        $consult = Db::getInstance()->getValue($sql);

        if ($consult === false) {
            $sql = new DbQuery;
            $sql->from('product_attribute')
                ->select('weight')
                ->where('id_product_attribute=' . (int)$id_product_attribute);

            $consult = Db::getInstance()->getValue($sql);
        }

        return $consult;
    }
}
