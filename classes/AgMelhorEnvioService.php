<?php

class AgMelhorEnvioService extends AgObjectModel
{
    public static $definition = array(
        'table'     => 'agmelhorenvio_service',
        'primary'   => 'id_agmelhorenvio_service',
        'multilang' => false,
        'fields'    => array(
            'id_agmelhorenvio_service' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            //nome no Melhor Envio
            'service_name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'carrier_name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),

            'id_remote' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),

            'type' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),
            'me_range' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(100)'),

            'image' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'handling_time' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned'),

            //não criar índice unique, porque podem haver vários serviços mapeados para a "transportadora" de id 0
            'id_carrier' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'default' => 0),

            'insurance' => array('type' => self::TYPE_BOOL, 'db_type' => 'bool', 'default' => 0),
            'own_hands' => array('type' => self::TYPE_BOOL, 'db_type' => 'bool', 'default' => 0),
            'receipt'   => array('type' => self::TYPE_BOOL, 'db_type' => 'bool', 'default' => 0),

            'additional_cost' => array(
                'type' => self::TYPE_FLOAT,
                'db_type' => 'float',
                'validate' => 'isFloat',
                'default' => 0.0
            ),
            'additional_cost_type' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'varchar(10)',
                'validate' => 'isString'
            ),
            'additional_time' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int unsigned',
                'validate' => 'isInt',
                'default' => 0
            ),
        ),
        'indexes' => array(
            array(
                'fields' => array('id_remote'),
                'prefix' => 'unique',
                'name' => 'unique_id_remote'
            )
        )
    );

    public $id_agmelhorenvio_service;
    public $name;
    public $service_name;
    public $carrier_name;
    public $id_remote;
    public $type;
    public $me_range;
    public $image;
    public $handling_time;
    public $id_carrier;

    public $insurance;
    public $own_hands;
    public $receipt;

    public $additional_cost;
    public $additional_cost_type;
    public $additional_time;

    public static function getByIdRemote($id_remote)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_service')
            ->where('id_remote=' . (int)$id_remote);

        $db_data = Db::getInstance()->getRow($sql);
        if (!is_array($db_data)) {
            $db_data = array();
        }

        $return = new AgMelhorEnvioService();
        $return->hydrate($db_data);

        return $return;
    }
    
    /**
     * @throws AgMelhorEnvioServiceSavingException - Erro ao salvar no BD
     */
    public static function installServices()
    {
        $services = self::getRemoteServices();

        foreach ($services as $service) {
            $current_service = self::getByIdRemote($service->getId());

            $company = $service->getCompany();

            $current_service->service_name = $service->getName();
            $current_service->image = $service->getPicture();

            $current_service->carrier_name = $company->getName();
            $current_service->name = $company->getName() . ' - ' . $service->getName();
            $current_service->type = $service->getType();
            $current_service->me_range = $service->getRange();
            $current_service->id_remote = $service->getId();
            $current_service->picture_url = $service->getPicture();
            $current_service->insurance = $company->getName() == 'Correios' ? ( isset($current_service->insurance) ? $current_service->insurance : 0 ) : 1;

            if (!$current_service->save()) {
                $msg_error = Db::getInstance()->getMsgError();
                throw new AgMelhorEnvioServiceSavingException("Erro instalando o serviço {$current_service->name} - {$msg_error}");
            }

            //salva os parâmetros opcionais e obrigatórios do serviço
            AgMelhorEnvioServiceRequirement::updateForService($current_service, $service->getRequirements());
            AgMelhorEnvioServiceOptional::updateForService($current_service, $service->getOptional());
        }
    }

    /**
     * @throws AgMelhorEnvioServiceSavingException
     * @throws AgMelhorEnvioServiceCarrierSavingException
     */

    public function installCarrierToPrestaShop()
    {
        //se o serviço atual está mapeado a uma transportadora que está cadastrada na loja PS
        if ($this->id_carrier) {
            $carrier = $this->getCarrier();
            
            //se a transportadora cadastrada ainda estiver ativa, remove-a
            if (Validate::isLoadedObject($carrier)) {
                $carrier->deleted = 1;
                if (!$carrier->update()) {
                    throw new AgMelhorEnvioServiceSavingException(sprintf(
                        'Erro excluindo a transportadora #%d(%s) do banco de dados - %s',
                        $carrier->id,
                        $carrier->name,
                        Db::getInstance()->getMsgError()
                    ));
                }
            }
        }

        $carrier = new Carrier($this->id_carrier);
        if ($carrier->deleted) {
            $carrier = new Carrier();
        }

        $carrier->name                 = $this->carrier_name . ' - ' . $this->service_name;
        $carrier->id_reference = $this->id_carrier;
        $carrier->id_tax_rules_group   = 0;
        $carrier->id_zone              = 1;
        $carrier->active               = true;
        $carrier->deleted              = 0;
        $carrier->shipping_handling    = true;
        $carrier->range_behavior       = 0;
        $carrier->is_module            = true;
        $carrier->shipping_external    = true;
        $carrier->external_module_name = 'agmelhorenvio';
        $carrier->need_range           = true;
        $carrier->url                  = 'https://melhorrastreio.com.br/meu-rastreio/@';
        
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $carrier->delay[(int) $language['id_lang']] = 'Prazo de Entrega Padrão';
        }

        if (!$carrier->save()) {
            throw new AgMelhorEnvioServiceCarrierSavingException(sprintf(
                'Erro salvando a transportadora %s na base de dados do PrestaShop - %s',
                $carrier->name,
                Db::getInstance()->getMsgError()
            ));
        }

        $this->id_carrier = $carrier->id;
        if (!$this->update()) {
            throw new AgMelhorEnvioServiceSavingException(sprintf(
                'Erro mapeando o serviço %d à transportadora %d.',
                $this->id,
                $carrier->id
            ));
        }

        $groups = Group::getGroups(true);
        foreach ($groups as $group) {
            Db::getInstance()->insert(
                'carrier_group',
                array(
                    'id_carrier' => (int) ($carrier->id),
                    'id_group'   => (int) ($group['id_group']
                    ),
                )
            );
        }

        self::installRangesToCarrier($carrier, $this);

        if (!copy($this->image, _PS_SHIP_IMG_DIR_ . (int) $carrier->id . '.jpg')) {
            throw new AgMelhorEnvioServiceImageCopyingException('Erro copiando a imagem da transportadora.');
        }
    }

    protected static function removeRangesFromCarrier(Carrier $carrier)
    {
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'range_price WHERE id_carrier=' . (int)$carrier->id;
        Db::getInstance()->execute($sql);

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'range_weight WHERE id_carrier=' . (int)$carrier->id;
        Db::getInstance()->execute($sql);

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'delivery WHERE id_carrier=' . (int)$carrier->id;
        Db::getInstance()->execute($sql);

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'carrier_zone WHERE id_carrier=' . (int)$carrier->id;
        Db::getInstance()->execute($sql);
    }

    protected static function installRangesToCarrier(Carrier $carrier)
    {
        self::removeRangesFromCarrier($carrier);

        $rangePrice             = new RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '1000000';
        $rangePrice->add();


        $zones = Zone::getZones(true);
        foreach ($zones as $zone) {
            Db::getInstance()->insert(
                'carrier_zone',
                array('id_carrier' => (int) ($carrier->id), 'id_zone' => (int) ($zone['id_zone'])
                )
            );
        }

        $rangeWeight             = new RangeWeight();
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = 0;
        $rangeWeight->delimiter2 = 100000;

        $rangeWeight->add();
        foreach ($zones as $zone) {
            foreach (Shop::getShops() as $shop) {
                Db::getInstance()->insert(
                    'delivery',
                    array(
                        'id_carrier' => (int) ($carrier->id),
                        'id_range_price' => (int) ($rangePrice->id),
                        'id_range_weight' => null,
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                        // 'id_shop' => is_array($shop) ? $shop['id_shop'] : $shop->id
                    )
                );

                Db::getInstance()->insert(
                    'delivery',
                    array(
                        'id_carrier' => (int) ($carrier->id),
                        'id_range_price' => null,
                        'id_range_weight' => (int) ($rangeWeight->id),
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                        // 'id_shop' => is_array($shop) ? $shop['id_shop'] : $shop->id
                    )
                );
            }
        }
    }

    public function getOptionals()
    {
        return AgMelhorEnvioServiceOptional::getByService($this);
    }

    public function getRequirements()
    {
        return AgMelhorEnvioServiceRequirement::getByService($this);
    }


    public function configureOptionals($optionals)
    {
        foreach ($optionals as $id => $value) {
            $instance = new AgMelhorEnvioServiceOptional($id);

            if (!Validate::isLoadedObject($instance)) {
                throw new AgMelhorEnvioServiceOptionalFindingException("Parâmetro opcional {$id} não encontrado.");
            }

            $instance->enabled = $value;

            if (!$instance->update()) {
                throw new AgMelhorEnvioServiceOptionalSavingException("Erro atualizando parâmetro opcional {$instance->name}.");
            }
        }
    }

    /**
     * @return Carrier
     */
    public function getCarrier()
    {
        $carrier = new Carrier($this->id_carrier);

        $return = Carrier::getCarrierByReference($carrier->id_reference);
        
        if (Validate::isLoadedObject($return) && $return->id != $this->id_carrier) {
            $this->id_carrier = $return->id;
            $this->update();
        }
        
        return $return;
    }

    public function requireCPF()
    {
        return true;
    }

    public static function getByCarrier(Carrier $carrier)
    {        
        $carrier = Carrier::getCarrierByReference($carrier->id_reference);

        $sql = new DbQuery();
        $sql->from('agmelhorenvio_service')
            ->where('id_carrier = ' . (int) $carrier->id  .' OR id_carrier=' . (int)$carrier->id_reference);

        $db_data = Db::getInstance()->getRow($sql);
        if (!is_array($db_data)) {
            $db_data = array();
        }

        $return = new AgMelhorEnvioService();
        $return->hydrate($db_data);
        
        return $return;

    }

    /**
     * @return array[AgMelhorEnvioService]
     */
    public static function getAll()
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_service');

        $db_data = Db::getInstance()->executeS($sql);

        if (!is_array($db_data)) {
            $msg_error = Db::getInstance()->getMsgError();

            if ($msg_error) {
                throw new AgMelhorEnvioServiceFindingException("Erro buscando serviços ativos - {$msg_error}");
            }

            $db_data = array();
        }

        return ObjectModel::hydrateCollection('AgMelhorEnvioService', $db_data);
    }

    protected static function getRemoteServices()
    {
        return AgMelhorEnvioGateway::getCarriers();
    }

    /**
     * @return AgMelhorEnvioService[]
     */
    public static function findBy($options = [])
    {
        $sql = new DbQuery;
        $sql->from('agmelhorenvio_service');

        foreach ($options as $field => $value) {
            $sql->where($field . '="' . pSQL($value) . '"');
        }


        $db_data = Db::getInstance()->executeS($sql);
        if (!$db_data) {
            $db_data = [];
        }

        return ObjectModel::hydrateCollection("AgMelhorEnvioService", $db_data);
    }
}
