<?php

use PrestaShop\PrestaShop\Core\Exception\DatabaseException;

class AgMelhorEnvioCache extends AgObjectModel
{
    const product_index = [
        'width' => ['width'],
        'height' => ['height'],
        'length' => ['depth', 'length'],
        'weight' => ['weight'],
        'quantity' => ['quantity'],
        'price' => ['price', 'insurance_value', 'price_wt'],
    ];

    public static $definition = [
        'table' => 'agmelhorenvio_cache',
        'primary' => 'id_agmelhorenvio_cache',
        'fields' => [
            'id_agmelhorenvio_cache'      => ['type' => self::TYPE_INT],
            'cache_key'                   => ['type' => self::TYPE_STRING, 'db_type' => 'text'],
            'id_remote'                   => ['type' => self::TYPE_INT,    'db_type' => 'int'],
            'shipping_cost_data'          => ['type' => self::TYPE_STRING, 'db_type' => 'text'],
            'delivery_time'               => ['type' => self::TYPE_INT,    'db_type' => 'int'],
            'date_add'                    => ['type' => self::TYPE_DATE,   'db_type' => 'datetime'],
        ],
        'indexes' => [
            [
                'fields' => ['id_remote', 'cache_key(512)'],
                'name' => 'uniqueness'
            ]
        ]
    ];

    public $id_agmelhorenvio_cache;
    public $cache_key;
    public $id_remote;
    public $shipping_cost_data;
    public $delivery_time;
    public $date_add;

    /**
     * @return AgMelhorEnvioCache
     */
    public static function get($cache_keys, $services)
    {
        $arr_shipping_cost = [];

        foreach ($services as $service) {
            $sql = new DbQuery;
            $sql->from('agmelhorenvio_cache')
                ->where('cache_key="' . pSQL($cache_keys[$service]) . '"')
                ->where('id_remote=' . (int) $service);

            $db_data = Db::getInstance()->getRow($sql, false);
            $error = Db::getInstance()->getMsgError();

            if ($error) {
                throw new PrestaShopDatabaseException($error);
            }

            if (!is_array($db_data)) {
                $db_data = [];
            }

            $return = new AgMelhorEnvioCache();
            $return->hydrate($db_data);

            $arr_shipping_cost[] = $return;
        }

        return $arr_shipping_cost;
    }

    /**
     * Salva o cache no banco de dados.
     * 
     * @throws Exception Erro de validação do Object Model
     * @throws DatabaseException Erro gravando os dados no BD.
     */
    public static function saveCache($cache_keys, $shipping_data)
    {
        // checa se o cache já foi inserido
        $exists_service_cache = AgMelhorEnvioCache::get($cache_keys, [$shipping_data->getIdService()])[0];
        if (!\Validate::isLoadedObject($exists_service_cache)) {
            $shipping_cost_data = self::objectToArray($shipping_data);

            $obj = new AgMelhorEnvioCache;

            $obj->id_remote = $shipping_data->getIdService();
            $obj->cache_key = $cache_keys[$shipping_data->getIdService()];
            $obj->shipping_cost_data = json_encode($shipping_cost_data);
            $obj->delivery_time = $shipping_data->getDeliveryTime();

            $valid = $obj->validateFields(false, true);

            if ($valid !== true) {
                throw new Exception($valid);
            }

            $obj->add();

            $error = Db::getInstance()->getMsgError();
            if ($error) {
                throw new DatabaseException($error);
            }
        } else {
            $obj = $exists_service_cache;
        }

        return $obj;
    }

    public static function GetCacheKeys($from, $to, $services, $options, $products)
    {
        $from = preg_replace('/\D/', '', $from->getPostalCode());
        $to = preg_replace('/\D/', '', $to->getPostalCode());
        $indexes = self::findIndexes($products[0]);

        // ordenando os produtos
        usort($products, function ($a, $b) {
            $indexes = self::findIndexes($a);
            $height = abs((float) $b[$indexes['height']] - (float) $a[$indexes['height']]);
            $width = abs((float) $b[$indexes['width']] - (float) $a[$indexes['width']]);
            $length = abs((float) $b[$indexes['length']] - (float) $a[$indexes['length']]);

            if ((float) $b[$indexes['height']] < (float) $a[$indexes['height']]) {
                return 1;
            } else if ($height < 1E-4) {
                if ((float) $b[$indexes['width']] < (float) $a[$indexes['width']]) {
                    return 1;
                } else if ($width < 1E-4) {
                    if ((float) $b[$indexes['length']] < (float) $a[$indexes['length']]) {
                        return 1;
                    }
                }
            }

            return -1;
        });

        $sorted_products = $products;
        $string_option = '';

        // verifica se existe alguma opção habilitada
        if (count((array) $options) > 0) {
            foreach ($options->getOptions() as $key => $option) {
                // pega a primeira letra da opção
                $name = strtoupper(substr($option->getName(), 0, 1));
                $string_option .=  $name . ($option->getValue() ? 1 : 0);
            }
        }

        $cache_keys = [];
        foreach ($services as $service) {
            // atribui o cep de origem e depois o de destino
            $cache_key = 'O' . $from . 'D' . $to;

            foreach ($sorted_products as $product) {

                // altura.largura.profundidade.peso.quantidade._preço 
                $cache_key .=
                    'A' . number_format((float) isset($product[$indexes['height']])?$product[$indexes['height']]:0.00, 2, '.', '')  .
                    'L' . number_format((float)isset($product[$indexes['width']])?$product[$indexes['width']]:0.00, 2, '.', '')  .
                    'P' . number_format((float)isset($product[$indexes['length']])?$product[$indexes['length']]:0.00, 2, '.', '')  .
                    'P' . number_format((float) isset($product[$indexes['weight']])?$product[$indexes['weight']]:0.00, 2, '.', '') .
                    'Q' . number_format((int) isset($product[$indexes['quantity']])?$product[$indexes['quantity']]:0, 0, '.', '') .
                    '_V' . number_format((float) isset($product[$indexes['price']])?$product[$indexes['price']]:0.00 , 2, '.', '');
            }

            // adiciona o serviço e as opções caso exista alguma
            $cache_key .= 'S' . $service . (!empty($string_option) ? 'O' . $string_option : '');
            $cache_keys[$service] = $cache_key;
        }

        return $cache_keys;
    }

    public static function ClearShippingCache($time_expire)
    {
        $expired_time = new Datetime(); //strtotime(date('Y-m-s H:i:s')) + $time_expire;
        $expired_time->add(new DateInterval("PT{$time_expire}S"));
        $expired_time = $expired_time->format('Y-m-d H:i:s');

        $shop_id = Context::getContext()->shop->id;
        $id_lang = Context::getContext()->language->id;

        try {
            Db::getInstance()->delete(
                'agmelhorenvio_cache',
                '`date_add` <= "' . $expired_time . '"'
                // . ' AND id_shop=' . (int) $shop_id . ' AND ' .
                // 'id_lang=' . (int) $id_lang
            );

            $requests_deleted = Db::getInstance()->Affected_Rows();

            AgClienteLogger::addLog("agmelhorenvio - Limpeza do cache concluida - {$requests_deleted} linhas deletadas", '1', '', '', '', true);

            return true;
        } catch (PDOException $ex) {
            AgClienteLogger::addLog('agmelhorenvio - Ocorreu um erro ao tentar limpar o cache - ' . $ex->getMessage(), '3', $ex->getCode(), '', '', true);

            return false;
        }
    }

    static function findIndexes($product)
    {
        $indexes = [];

        foreach ($product as $key => $value) {
            foreach (self::product_index as $key_comp => $val_comp) {
                $index = array_search($key, $val_comp);
                if ($index !== false) {
                    $indexes[$key_comp] = $val_comp[$index];
                    break;
                }
            }
        }

        return $indexes;
    }

    public static function objectToArray($object)
    {
        $objectAsArray = (array) $object;

        foreach ($objectAsArray as $key => $value) {
            if (empty($value) && $value !== 0) {
                unset($objectAsArray[$key]);
                continue;
            }

            if (stripos($key, "\0") === 0) {
                $newKey = self::fixKeyName($key);
                self::replaceKey($objectAsArray, $key, $newKey);
            }

            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    if (is_object($sub_value)) {
                        $objectAsArray[$newKey][$sub_key] = self::objectToArray($sub_value);
                    }
                }
            }

            if (is_object($value)) {
                $objectAsArray[$newKey] = self::objectToArray($objectAsArray[$newKey]);
            }
        }

        return $objectAsArray;
    }

    public static function replaceKey(&$array, $curkey, $newkey)
    {
        if (array_key_exists($curkey, $array)) {
            $array[$newkey] = $array[$curkey];
            unset($array[$curkey]);

            return true;
        }

        return false;
    }

    public static function fixKeyName($oldKey)
    {
        return substr($oldKey, strpos($oldKey, "\0", 2) + 1);
    }

    public static function SearchDoc($doc_type, $lst_docs)
    {
        foreach ($lst_docs as $doc) {
            if ($doc->type == $doc_type) {
                return $doc;
            }
        }
    }
}
