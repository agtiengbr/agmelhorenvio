<?php

class AdminAgMelhorEnvioDiscountsController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap    = true;
        $this->table        = 'agmelhorenvio_discount';
        $this->identifier   = 'id_agmelhorenvio_discount';
        $this->className    = 'AgMelhorEnvioDiscount';
        $this->noLink       = true;
        $this->list_no_link = true;

		parent::__construct();

		$this->module->prepareNotifications();

		$services = AgMelhorEnvioService::getAll();

		$services_query = [];
		foreach ($services as $service) {
			$services_query[] = [
				'id_service' => $service->id_remote,
				'name' 	     => $service->carrier_name . ' - ' . $service->service_name
			];
		}

		$this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'agmelhorenvio_service asv ON asv.id_remote = a.id_agmelhorenvio_service ';
		$this->_select .= ' CONCAT(asv.carrier_name, " - ", asv.service_name) service, ';

		$this->fields_list = [
			'id_agmelhorenvio_discount' => [
				'type'  => 'int',
				'title' => 'ID',
				'class' => 'fixed-width-sm'
			],
			'alias' => [
				'type'  => 'text',
				'title' => 'Nome da Campanha',
			],
			'service' => [
				'type'  => 'text',
				'title' => 'Serviço',
				'class' => 'fixed-width-lg'
			],
			'type_discount' => [
				'type'       => 'select',
				'title'      => 'Tipo de Desconto',
				'filter_key' => 'a!type_discount',
				'list'       => [
					'0' => 'Percentual',
					'1' => 'Valor Fixo'
				],
				'class' => 'fixed-width-md'
			],
			'discount' => [
				'type'  => 'int',
				'title' => 'Desconto',
				'class' => 'fixed-width-sm'
			],
			'cart_value_begin' => [
				'type'  => 'price',
				'title' => 'Pedido Mínimo',
				'class' => 'fixed-width-sm'
			],
			'cart_value_end' => [
				'type'  => 'price',
				'title' => 'Pedido Máximo',
				'class' => 'fixed-width-sm'
			],
			'active' => [
				'type'   => 'bool',
				'title'  => 'Ativo',
				'active' => 'active'
			]
		];

		$this->fields_form = [
			'legend' => ['title' => 'Desconto'],
			'input'  => [
				[
					'name'     => 'id_agmelhorenvio_service',
					'type'     => 'select',
					'label'    => 'Serviço',
					'col'      => 3,
                    'options' => [
                        'id'    => 'id_service',
                        'name'  => 'name',
                        'query' => $services_query
                    ],
				],
				[
					'name'     => 'alias',
					'type'     => 'text',
					'label'    => 'Nome da Campanha',
					'hint'     => 'Ex: Frete Grátis Sudeste',
					'col'      => '5',
					'required' => true
				],
				[
					'name'     => 'type_discount',
					'type'     => 'radio',
					'label'    => 'Tipo de desconto',
					'required' => true,
					'values'   => [
						[
							'label' => 'Percentual',
							'id'    => 'type_discount_percentual',
							'value' => 0
						],
						[
							'label' => 'Valor Fixo',
							'id'    => 'type_discount_fixed_value',
							'value' => 1
						]
					]
				],
				[
					'name'     => 'discount',
					'type'     => 'text',
					'label'    => 'Desconto',
					'required' => true,
					'col'      => 1
				],
				[
					'name'     => 'cart_value_begin',
					'type'     => 'text',
					'label'    => 'Pedido Mínimo',
					'prefix'   => 'R$',
					'col'      => 2
				],
				[
					'name'     => 'cart_value_end',
					'type'     => 'text',
					'label'    => 'Pedido Máximo',
					'prefix'   => 'R$',
					'col'      => 2
				],
				[
                    'type' => 'switch',
                    'label' => 'Ativo',
                    'name' => 'active',
                    'values' => [
                        [
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ],
                        [
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => 'Não',
                        ],
                    ],
                ]
			],
			'submit' => [
                'title' => 'Salvar',
            ]
		];

		$this->actions = ['edit', 'delete'];
		$this->bulk_actions = [
			'enableSelection' => [
				'text' => 'Ativar',
            	'icon' => 'icon-check'
			],
			'disableSelection' => [
				'text' => 'Desativar',
            	'icon' => 'icon-times'
			],
            'delete' => [
            	'text' => 'Excluir',
            	'icon' => 'icon-trash'
            ]
        ];
	}

	public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['cogs'] = [
            'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name,
            'desc' => 'Configurar'
        ];
    }
	
	public function initContent()
	{
		if (Tools::getIsSet('active' . $this->table)) {
			$object = $this->loadObject();
			$object->active = !$object->active;
			$object->update();

			$this->module->confirmations[]  = 'Desconto atualizado com sucesso!';
			$this->module->saveNotifications();

			Tools::redirectAdmin(self::$currentIndex);
		} elseif (Tools::getIsSet('searchByZone')) {
			$zone = Tools::getValue('zone');
			$ret = AddressFinder::findByZone($zone);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchByUf')) {
			$uf = Tools::getValue('uf');
			$ret = AddressFinder::findByUf($uf);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchCityByName')) {
			$name = Tools::getValue('name');
			$uf = Tools::getValue('uf');

			$ret = AddressFinder::findCitiesByName($name, $uf);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchByCityAndUf')) {
			$uf = Tools::getValue('uf');
			$city = Tools::getValue('city');

			$ret = AddressFinder::findByUfAndCity($uf, $city);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchNeighborhoodByName')) {
			$uf = Tools::getValue('uf');
			$city = Tools::getValue('city');
			$neighborhood = Tools::getValue('neighborhood');

			$ret = AddressFinder::findNeighborhoodByName($uf, $city, $neighborhood);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('searchByNeighborhood')) {
			$uf = Tools::getValue('uf');
			$city = Tools::getValue('city');
			$neighborhood = Tools::getValue('neighborhood');

			$ret = AddressFinder::findIntervalByNeighborhood($uf, $city, $neighborhood);

			echo json_encode($ret);
			exit();
		} elseif (Tools::getIsSet('saveRanges')) {
			try {
				$this->saveRanges();

				echo json_encode([
					'success' => true
				]);
			} catch (Exception $e) {
				echo json_encode([
					'success' => false,
					'error' => $e->getMessage()
				]);
			}

			exit();
		} elseif (Tools::getIsSet('getRanges')) {
			try {
				$ranges = $this->getRanges();

				echo json_encode([
					'success' => true,
					'ranges' => $ranges
				]);
			} catch (Exception $e) {
				echo json_encode([
					'success' => false,
					'error' => $e->getMessage()
				]);
			}

			exit();
		}

		parent::initContent();
	}

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

        if (is_array($this->_list)) {
            $nb = count($this->_list);
            
            for ($i = 0; $i < $nb; $i++) {
                $this->_list[$i]['type_discount'] = isset($this->_list[$i]['type_discount']) && $this->_list[$i]['type_discount'] == 0? 'Percentual' : 'Valor Fixo';

                $this->_list[$i]['type_interval'] = isset($this->_list[$i]['type_interval']) && $this->_list[$i]['type_interval'] == 0? 'Faixa de CEP' : 'Região';
            }
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

		$this->addJs("https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js");
		$this->addJs('https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js');
        $this->addJs("https://cdn.jsdelivr.net/npm/maska@1.5.1/dist/maska.js");

		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/grid/table.js");
        $this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/grid/header.js");
        $this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/grid/body.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/grid/switch.js");

        $this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/loading/loading.vue.js");

		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/form/input-text.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/form/autocomplete.vue.js");

		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/row_actions.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/states.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/city_autocomplete_list.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/cities.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/zones.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/zipcodes.vue.js");
        $this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/neighborhoods.vue.js");
        $this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/neighborhood_autocomplete_list.vue.js");
		$this->addJs(_PS_MODULE_DIR_ . "agcliente/views/js/component/zipcode_grid/component.vue.js");

        $this->addCss(_PS_MODULE_DIR_ . "agcliente/views/css/component/zipcode_grid.css");

        $this->addJs(array(
            _PS_MODULE_DIR_ . 'agmelhorenvio/views/js/discounts/form.js'
        ));
    }


    /******************* ações em massa ************************/
    protected function processBulkEnableSelection()
    {
        return $this->processBulkStatusSelection(1);
    }

    protected function processBulkDisableSelection()
    {
        return $this->processBulkStatusSelection(0);
    }

    protected function processBulkStatusSelection($status)
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id) {
                /** @var ObjectModel $object */
                $object = new $this->className((int)$id);
                $object->active = (int)$status;
                if (!$object->update()) {
                    $msg_error = Db::getInstance()->getMsgError();
                    $this->module->errors[] = "Erro atualizando status do desconto {$id} - {$msg_error}";
                } else {
                    $this->module->confirmations[] = "Desconto {$id} atualizada com sucesso!";
                }
            }
        }
    }

	public function displayAjaxSave()
	{
		$r = parent::processSave();

		echo json_encode(['success' => (bool)$r, 'id' => $r->id]);
		exit();
	}

	private function saveRanges()
	{
		$ranges = Tools::getValue('ranges');
		$idDiscount = Tools::getValue('id_discount');

		$obj = new AgMelhorEnvioDiscount($idDiscount);
		if (!Validate::isLoadedObject($obj)) {
			throw new Exception("Desconto não localizado.");
		}

		AgMelhorEnvioRangeCep::deleteByDiscount($obj);

		foreach ($ranges as $range) {
			$objRange = new AgMelhorEnvioRangeCep();

			$objRange->id_agmelhorenvio_discount = $obj->id;
			$objRange->region = $range['region'];
			$objRange->state = $range['state'];
			$objRange->city = $range['city'];
			$objRange->neighborhood = $range['neighborhood'];
			$objRange->cep_start = str_replace('-', '', $range['cep_start']);
			$objRange->cep_end = str_replace('-', '', $range['cep_end']);

			$objRange->save();
		}
	}

	public function getRanges()
	{
		$idDiscount = Tools::getValue('id_agmelhorenvio_discount');
		$obj = new AgMelhorEnvioDiscount($idDiscount);
		if (!Validate::isLoadedObject($obj)) {
			throw new Exception("Desconto não localizado.");
		}

		$discounts = AgMelhorEnvioRangeCep::getByDiscount($obj);

		return $discounts;
	}
}
