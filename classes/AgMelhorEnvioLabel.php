<?php

use AGTI\Cliente\Domain\Shipping\Service\GetOriginZipcodeByProduct;

class AgMelhorEnvioLabel extends AgObjectModel
{
    public static $definition = [
        'table'     => 'agmelhorenvio_label',
        'primary'   => 'id_agmelhorenvio_label',
        'multilang' => false,
        'fields'    => [
            'id_agmelhorenvio_label' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'default' => 0
            ],
            'id_order_remote' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(255)'
            ],
            'zipcode_origin' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(16)'
            ],
            'protocol' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(255)'
            ],
            'service_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'required' => true
            ],
            'agency_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'default' => 0 
            ],
            'price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'db_type' => 'float',
                'default' => 0
            ],
            "discount" => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'db_type' => 'float',
                'default' => 0
            ],
            "delivery_time" => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'default' => 0
            ],
            "status" => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(255)'
            ],
            "insurance_value" => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'db_type' => 'float',
                'default' => 0
            ],
            "weight" => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'db_type' => 'float',
                'default' => 0
            ],
            "width"=> [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'db_type' => 'float',
                'default' => 0
            ],
            "height" => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'db_type' => 'float',
                'default' => 0
            ],
            "length" => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'db_type' => 'float',
                'default' => 0
            ],
            "diameter" => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'db_type' => 'float',
                'default' => 0
            ],
            "format" => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(255)'
            ],
            'reverse' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'db_type' => 'boolean',
                'default' => '0'
            ],
            'tracking' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(255)'
            ],
            'self_tracking' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(255)'
            ],
            'paid_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'generated_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'posted_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'delivered_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'canceled_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'expired_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'created_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'receipt' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'db_type' => 'boolean',
                'default' => '0'
            ],
            'own_hand' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'db_type' => 'boolean',
                'default' => '0'
            ],
            'collect' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'db_type' => 'boolean',
                'default' => '0'
            ],
            'collect_scheduled_at' => [
                'type' => self::TYPE_DATE,
                'db_type' => 'datetime'
            ],
            'payment_link' => [
                'type' => self::TYPE_STRING,
                'db_type' => 'varchar(255)'
            ],
        ],
    ];

    public $id_agmelhorenvio_label;
    public $id_order;
    public $id_order_remote;
    public $zipcode_origin;
    public $protocol;
    public $service_id;
    public $agency_id;
    public $price;
    public $discount;
    public $delivery_time;
    public $status;
    public $insurance_value;
    public $weight;
    public $height;
    public $width;
    public $length;
    public $diameter;
    public $format;
    public $reverse;
    public $tracking;
    public $self_tracking;
    public $paid_at;
    public $generated_at;
    public $posted_at;
    public $delivered_at;
    public $canceled_at;
    public $expired_at;
    public $created_at;
    public $date_upd;
    public $receipt;
    public $own_hand;
    public $collect;
    public $collect_scheduled_at;
    public $payment_link;

    public function getService()
    {
        $service = AgMelhorEnvioService::getByIdRemote($this->service_id);

        return $service;
    }

    public function cloneBasicInfo()
    {
        $return = new AgMelhorEnvioLabel();
        $return->id_order = $this->id_order;
        $return->service_id = $this->service_id;
        $return->status = $this->status;
        $return->zipcode_origin = $this->zipcode_origin;

        return $return;
    }

    public function getDataToMelhorEnvio(AgMelhorEnvio $module)
    {
        $id_br = Country::getByIso('br');

        $order = new Order($this->id_order);
        $service = $this->getService();
        $to = new Address($order->id_address_delivery);


        $from = $module->getFromAddress();
        $defaultFrom = $module->getFromAddress();

        //remetente
        if ($this->zipcode_origin && $this->zipcode_origin != GetOriginZipcodeByProduct::NO_ZIPCODE && $this->zipcode_origin != GetOriginZipcodeByProduct::UNKNOWN_ZIPCODE) {
            $from->postcode = $this->zipcode_origin;
        }
        
        $from_remote = new AgMelhorEnvioRemoteAddress();

        //a etiqueta será postada para o endereço padrão do módulo
        if ($from->postcode == $defaultFrom->postcode) {
            $from_remote->setPostalCode($from->postcode)
                ->setName($from->company)
                // Trocado para phone_mobile ou phone para corrigir o Notice: Undefined property: Address::$shop_address_phone
                ->setPhone($from->phone_mobile ? $from->phone_mobile : $from->phone)
                ->setAddress($from->address)
                ->setNumber($from->number)
                ->setDistrict($from->district)
                ->setCity($from->city)
                ->setUf($from->state)
                ->setCountryId('BR')
                ->setDocument(AgMelhorEnvioConfiguration::getCnpj())
                ->setStateRegister(AgMelhorEnvioConfiguration::getStateRegister());
        } else {
            $sellerData = AgMelhorEnvioSellerDataZipcode::getByZipcode($from->postcode);

            $from_remote->setPostalCode($from->postcode)
                ->setName($sellerData->shop_name)
                ->setPhone($sellerData->phone)
                ->setAddress($sellerData->address)
                ->setNumber($sellerData->number)
                ->setDistrict($sellerData->district)
                ->setCity($sellerData->city)
                ->setUf($sellerData->uf)
                ->setCountryId('BR')
                ->setDocument($sellerData->cnpj)
                ->setStateRegister($sellerData->state_register);
        }


        //destinatário
        $to = new Address($order->id_address_delivery);
        $state_to = new State($to->id_state);
        $country_to = new Country($id_br);

        $module = new agmelhorenvio;
        $document = $module->getCustomerData($order->getCustomer(), new Address($order->id_address_delivery));


        $number_mapped = $module->getAddressNumberMapping()->getMappedField();
        $sql = new DbQuery;
        $sql->from('address')
            ->select($number_mapped)
            ->where('id_address=' . (int) $order->id_address_delivery);

        $to_number = Db::getInstance()->getValue($sql);


        $to_remote = new AgMelhorEnvioRemoteAddress();
        $to_remote->setPostalCode($to->postcode)
            ->setName($to->firstname . ' ' . $to->lastname)
            ->setPhone($to->phone_mobile ? $to->phone_mobile : $to->phone)
            ->setAddress($to->address1)
            ->setNumber($to_number)
            ->setDistrict($to->address2)
            ->setCity($to->city)
            ->setUf($state_to->iso_code)
            ->setCountryId($country_to->iso_code)
            ->setComplement($to->{$module->getAddressComplementMapping()->getMappedField()})
            ->setEmail($order->getCustomer()->email)
            ->setDocument(@$document['cpf'])
            ->setCompanyDocument(@$document['cnpj']);

        //produtos
        $total_price = 0;
        $products = array();
        foreach ($order->getProducts() as $product) {
            $id_product = $product['product_id'];
            $id_product_attribute = $product['product_attribute_id'];

            //verifica se o CEP de origem do produto é o mesmo da etiqueta
            $serviceZipcode = new GetOriginZipcodeByProduct;
            $zipcode = $serviceZipcode->exec($id_product);
            if ($zipcode != GetOriginZipcodeByProduct::UNKNOWN_ZIPCODE) {
                $zipcode = preg_replace("/[^0-9]/", "", $zipcode);
            }
            if (
                ($zipcode == GetOriginZipcodeByProduct::UNKNOWN_ZIPCODE && $this->zipcode_origin == $defaultFrom->postcode)
                || ($zipcode == $from->postcode)
            ) {
                //conversão de unidade
                // o product_weight é o valor do produto considerando o atributo
                $attribute_weight = $product['weight'];
                if ($id_product_attribute) {
                    $attribute_weight += AgMelhorEnvioLabelProduct::GetCombinationWeight($id_product_attribute, $order->id_shop);
                }

                $weight = $attribute_weight;
                if (Configuration::get('PS_WEIGHT_UNIT') == 'g') {
                    $weight /= 1000;
                }

                $row = array(
                    'name' => AgMelhorEnvioLabelProduct::getMaskedProductName(
                        $product['product_id'],
                        $product['product_attribute_id'],
                        $product['product_name']
                    ),
                    'id' => "{$id_product}-{$id_product_attribute}",
                    'width' => max($product['width'], 0.1),
                    'height' => max($product['height'], 0.1),
                    'length' => max($product['depth'], 0.1),
                    'weight' => max($weight, 0.001),

                    'quantity' => $product['product_quantity'],
                    'unitary_value' => round($product['original_product_price'], 2),
                );

                if ($service->insurance) {
                    $row['insurance_value'] =  $product['product_price'];
                }

                $products[] = $row;
                
                $total_price += $product['original_product_price'] * $product['product_quantity'];
            }
        }

        try {
            $module->ignore_discounts = true;
            $shipping = $module->calcShippingCost($service, $from, $to, $products, $total_price, true, false);
            if (is_null($shipping) || $shipping == []) {
                throw new Exception("A transportadora escolhida não atende ao CEP de destino.");
            }
        } catch (Exception $e) {
            $msg_error = "Erro obtendo pacotes para a postagem - {$e->getMessage()}";

            throw new Exception($msg_error);
        }

        return $shipping;
    }

    public static function generateLabelsForOrder(Order $order)
    {
        
        $module = new agmelhorenvio;

        $service = AgMelhorEnvioService::getByCarrier(new Carrier($order->id_carrier));

        foreach ($order->getProducts() as $product) {
            $id_product = $product['product_id'];
            $id_product_attribute = $product['product_attribute_id'];

            if ($id_product_attribute) {
                $attribute_weight = AgMelhorEnvioLabelProduct::GetCombinationWeight($id_product_attribute, $order->id_shop);
            }

            $products["{$id_product}-{$id_product_attribute}"] = array(
                'name' => $product['product_name'],
                'id' => "{$id_product}-{$id_product_attribute}",
                'width' => max($product['width'], 0.1),
                'height' => max($product['height'], 0.1),
                'length' => max($product['depth'], 0.1),
                'weight' => max($product['product_weight'], 0.001) + @$attribute_weight,

                'unitary_value' => round($product['original_product_price'], 2),
                'insurance_value' => $service->insurance ? round($product['original_product_price'], 2) : 0
            );
        }

        //agrupa os produtos por CEP de origem
        $groupedProducts = [];
        foreach ($products as $product) {
            $ids = explode('-', $product['id']);

            $searchService = new GetOriginZipcodeByProduct;
            $zipcodeOrigin = $searchService->exec($ids[0]);

            if (!isset($groupedProducts[$zipcodeOrigin])) {
                $groupedProducts[$zipcodeOrigin] = [$product];
            } else {
                $groupedProducts[$zipcodeOrigin][] = $product;
            }
        }

        foreach ($groupedProducts as $zipcode=>$products) {
            $label = new AgMelhorEnvioLabel;
            $label->id_order = $order->id;
            $label->service_id = $service->id_remote;
            $label->status = 'to_be_generated';
            $label->zipcode_origin = $zipcode;

            $from = $module->getFromAddress();
            
            if ($zipcode == GetOriginZipcodeByProduct::UNKNOWN_ZIPCODE) {
                $label->zipcode_origin = $from->postcode;
            }

            $from_remote = new AgMelhorEnvioRemoteAddress();
            $from_remote->setPostalCode($from->postcode)
                ->setName($from->company)
                ->setPhone($from->phone)
                ->setAddress($from->address)
                ->setNumber($from->number)
                ->setDistrict($from->district)
                ->setCity($from->city)
                ->setUf($from->state)
                ->setCountryId('BR')
                ->setDocument(AgMelhorEnvioConfiguration::getCnpj())
                ->setStateRegister(AgMelhorEnvioConfiguration::getStateRegister());

            $to_remote = $label->getToRemote();

            $service = $label->getService();

            $order = new Order($label->id_order);
            $cart = Cart::getCartByOrderId($label->id_order);

            $products = array();

            try {
                $dt = $label->getDataToMelhorEnvio($module);
                //constrói os itens a serem adicionados no carrinho de compras do MelhorEnvio, um para cada pacote
                $return = [];
                $packages = $dt->getPackages();

                foreach ($packages as $i => $package) {
                    $products = $package->getProducts();

                    try {
                        if ($i == 0) {
                            $obj = $label;
                            $obj->price = $dt->getPrice();
                        } else {
                            $obj = $label->cloneBasicInfo();
                            $obj->price = $package->getPrice();
                        }

                        foreach (['width', 'height', 'weight', 'length'] as $field) {
                            if (!$obj->{$field}) {
                                $method = 'get' . ucfirst($field);
                                $obj->{$field} = $package->{$method}();
                            }
                        }

                        $obj->updated_at = date('Y-m-d H:i:s');
                        if (!$obj->save()) {
                            $msg_error = "Erro salvando a etiqueta no banco de dados.";
                            $msg_error .= Db::getInstance()->getMsgError();
                            Logger::addLog('agmelhornevio - Erro gerando etiqueta para o pedido ' . $order->id . ' - ' . $msg_error);
                            continue;
                        }

                        foreach ($products as $product) {
                            $ids = explode('-', $product->id);

                            //obtém o valor untário do produto no pedido
                            $sql = new DbQuery;
                            $sql->from('order_detail')
                                ->select('unit_price_tax_incl, product_name')
                                ->where('id_order=' . (int)$order->id)
                                ->where('product_id=' . (int)$ids[0])
                                ->where('product_attribute_id=' . (int)$ids[1]);

                            $db_data = Db::getInstance()->getRow($sql);

                            $unitary_value = round($db_data['unit_price_tax_incl'], 2);
                            $product_name = $db_data['product_name'];

                            AgMelhorEnvioLabelProduct::create($obj->id, $product->quantity, $ids[0], $ids[1], $product_name, $unitary_value);
                        }
                    } catch (Exception $e) {
                        $msg_error = "Erro adicionando etiqueta ao carrinho de compras - {$e->getMessage()}";
                        Logger::addLog('agmelhorenvio - Erro gerando etiqueta para o pedido ' . $order->id . ' - ' . $msg_error, 3, $e->getCode(), 'Order', $order->id, true);
                    }
                }
            } catch (Exception $e) {
                $msg_error = "Erro adicionando etiqueta ao carrinho de compras - {$e->getMessage()}";
                Logger::addLog('agmelhorenvio - Erro gerando etiqueta para o pedido ' . $order->id . ' - ' . $msg_error, 3, $e->getCode(), 'Order', $order->id, true);

                return $msg_error;
            }
        }

        return true;
    }

    public function getToRemote()
    {
        $id_br = Country::getByIso('br');

        $cache_key = get_called_class() . __FUNCTION__ . $this->id;

        if (!Cache::isStored($cache_key)) {
            $order = new Order($this->id_order);
            $cart = new Cart($order->id_cart);
            $context = Context::getContext();
            $service = $this->getService();


            $to = new Address($order->id_address_delivery);
            $state_to = new State($to->id_state);
            $country_to = new Country($id_br);

            $module = new agmelhorenvio;
            $document = $module->getCustomerData($order->getCustomer(), new Address($order->id_address_delivery));

            if ($module->getAddressNumberMapping()->isMappingEnabled()) {
                $number_mapped = $module->getAddressNumberMapping()->getMappedField();
                $sql = new DbQuery;
                $sql->from('address')
                    ->select($number_mapped)
                    ->where('id_address=' . (int) $order->id_address_delivery);
                $to_number = Db::getInstance()->getValue($sql);
            }

            if ($module->getAddressComplementMapping()->isMappingEnabled()) {
                $other = $to->{$module->getAddressComplementMapping()->getMappedField()};
            }

            $other = Tools::substr($other, 0, 64);

            $to_remote = new AgMelhorEnvioRemoteAddress();
            $to_remote->setPostalCode($to->postcode)
                ->setName($to->firstname . ' ' . $to->lastname)
                ->setPhone($to->phone_mobile? $to->phone_mobile : $to->phone)
                ->setAddress($to->address1)
                ->setNumber(@$to_number)
                ->setDistrict($to->address2)
                ->setCity($to->city)
                ->setUf($state_to->iso_code)
                ->setCountryId($country_to->iso_code)
                ->setComplement(@$other)
                ->setEmail($order->getCustomer()->email)
                ->setDocument(@$document['cpf'])
                ->setCompanyDocument(@$document['cnpj'])
                ;

            Cache::store($cache_key, $to_remote);
        }

        return Cache::retrieve($cache_key);
    }

    public function getRemotePackage()
    {
        $package = new AgMelhorEnvioRemotePackage;

        $package->setWidth($this->width);
        $package->setHeight($this->height);
        $package->setLength($this->length);
        $package->setWeight($this->weight);

        return $package;
    }

    public static function getStatuses()
    {
        return AgMelhorEnvioLabelsStatusesEnum::getAll();
    }

    public static function getStatusText($status_code)
    {
        switch($status_code) {
            case 'to_be_generated' :
                return "Etiqueta não gerada";
            case 'pending' :
                return "Aguardando Pagamento da Etiqueta";
            case 'released' :
                return "Pronta para impressão";
            case 'paid' :
                return 'Pagamento Aprovado';
            case 'received':
                return 'Recebida na transportadora';
            case 'posted' :
                return 'Etiqueta Postada';
            case 'printed' :
                return 'Etiqueta Impressa';
            case 'delivered':
                return 'Entregue';
            case 'canceled':
                return 'Cancelada';
            case 'to_be_shipped':
                return 'Aguardando Postagem';
        }

        return $status_code;
    }

    public static function getByIdOrderRemote($id_order_remote)
    {
        $sql = new DbQuery;
        $sql->from('agmelhorenvio_label')
            ->where('id_order_remote="' . pSQL($id_order_remote) . '"');

        $db_data = Db::getInstance()->getRow($sql);

        if (is_array($db_data)) {
            $return = new AgMelhorEnvioLabel($db_data['id_agmelhorenvio_label']);

            return $return;
        }
    }

    public static function getByIdOrder($id_order)
    {
        $cache_key = get_called_class() . __FUNCTION__ . $id_order;

        if (!Cache::isStored($cache_key)) {
            $sql = new DbQuery;
            $sql->from('agmelhorenvio_label')
                ->where('id_order="' . pSQL($id_order) . '"');

            $db_data = Db::getInstance()->executeS($sql);
            Cache::store($cache_key, $db_data);
        }

        return Cache::retrieve($cache_key);
    }


    public function update($null_values = false)
    {
        $order = new Order($this->id_order);

        if (Validate::isLoadedObject($order)) {
            if ($this->tracking) {
                if (isset($order->shipping_number)) {
                    $order->shipping_number = $this->tracking;
                    $order->update();
                }


                $id_order_carrier = $order->getIdOrderCarrier();

                if ($id_order_carrier) {
                    $order_carrier = new OrderCarrier($id_order_carrier);

                    if ($order_carrier->tracking_number != $this->tracking) {
                        $order_carrier->tracking_number = $this->tracking;
                        $order_carrier->update();
                        $this->sendTrackingEmail();
                    }
                }
            }


            //atualização do estado da etiqueta
            if (!AgMelhorEnvioConfiguration::getAgmelhorenvioStatusMappingEnabled()) {
                goto _parent;
            }

            $module = new agmelhorenvio;
            $new_state = $module->getMappedStatus($this->status);
            if ($new_state <= 0) {
                goto _parent;
            }

            $sql = new DbQuery();
            $sql->from('order_history')
                ->where('id_order = ' . (int) $order->id);

            $histories = Db::getInstance()->executeS($sql);

            //se o status atual já tiver sido usado em algum momento neste pedido, ele é ignorado
            $set_state = true;
            if (is_array($histories)) {
                foreach ($histories as $history) {
                    if ($history['id_order_state'] == $new_state) {
                        $set_state = false;
                        break;
                    }
                }
            }

            if ($set_state) {
                $order->setCurrentState($new_state);
            }
        }

        _parent:
        return parent::update($null_values);
    }

    public function sendTrackingEmail()
    {
        $order = new Order($this->id_order);
        $carrier = new Carrier($order->id_carrier);
        $customer = new Customer($order->id_customer);

        if (Configuration::get('AGMELHORENVIO_CONFIGURATION_SEND_TRACKING_EMAIL')) {
            Mail::send(
                (int) $order->id_lang,
                'agmelhorenvio_tracking',
                'O código de rastreio do seu pedido ' . $order->reference . ' foi gerado',
                [
                    '{tracking_number}' => $this->tracking,
                    '{tracking_url}' => str_replace('@', $this->tracking, $carrier->url),
                    '{carrier_name}' => $carrier->name,
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{order_name}' => $order->reference
                ],
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null, // from email
                null, // from name
                null, // file attachment
                null, // mode smtp
                _PS_MODULE_DIR_  .'agmelhorenvio/mails'
            );
        }
    }
}