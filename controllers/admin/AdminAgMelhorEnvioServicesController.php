<?php

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class AdminAgMelhorEnvioServicesController extends ModuleAdminController
{
    const CONF_INSTALLED = 1000;

    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'agmelhorenvio_service';
        $this->identifier = 'id_agmelhorenvio_service';
        $this->className  = 'AgMelhorEnvioService';
        $this->noLink = true;
        $this->list_no_link = true;

        parent::__construct();

        try {
            AgMelhorEnvioService::installServices();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        
        $this->fields_list = array(
            'id_agmelhorenvio_service' => array(
                'type' => 'int',
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'carrier_name' => array(
                'title' => 'Transportadora',
            ),
            'service_name' => array(
                'title' => 'Serviço',
            ),
            'additional_cost' => array(
                'title' => 'Custo Adicional',
                'align' => 'right',
                'currency' => true,
                'filter_key' => 'a!additional_cost',
                'callback' => 'displayAdditionalCost'
            ),
            'additional_time' => array(
                'title' => 'Prazo Adicional',
                'type' => 'int',
                'align' => 'right',
                'class' => 'fixed-width-xs',
                'filter_key' => 'a!additional_time',
                'suffix' => ' dias úteis'
            ),
            'image' => array(
                'title' => 'Imagem',
            ),
            'ps_carrier_name' => array(
                'title' => 'Transportadora PS',
                'filter_key' => 'c!name'
            ),
            'type' => array(
                'title' => 'Tipo',
                'type' => 'select',
                'list' => array(
                    1 => 'Normal',
                    2 => 'Expresso'
                ),
                'filter_key' => 'a!type'
            ),
            'me_range' => array(
                'title' => 'Alcance',
                'type' => 'select',
                'list' => array(
                    "municipal" => 'Municipal',
                    "intermunicipal" => 'Intermunicipal',
                    "interstate" => 'Interestadual',
                    "global" => 'Global'
                ),
                'filter_key' => 'a!me_range'
            ),
            'receipt' => [
                'type'   => 'bool',
                'title'  => 'AR',
                'hint' => 'Aviso de Recebimento',
				'active' => 'receipt'
            ],
            'own_hands' => [
                'type'   => 'bool',
                'title'  => 'MP',
                'hint' => 'Mãos Próprias',
				'active' => 'own_hands'
            ],
            'insurance' => [
                'type'   => 'bool',
                'title'  => 'VD',
                'hint' => 'Seguro da Mercadoria',
                'active' => 'insurance'
            ]
        );

        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'carrier c ON c.id_carrier = a.id_carrier';
        $this->_select .= 'c.name AS ps_carrier_name';

        $this->actions = array('edit', 'view', 'install', 'uninstall');

        $this->bulk_actions = array(
            "Install" => array(
                'text' => 'Instalar Transportadoras',
                'icon' => 'icon-download',
            )
        );

        $this->_conf[self::CONF_INSTALLED] = 'Instalação realizada com sucesso!';

        $this->fields_form = [
            'legend' => [
                'title' => 'Configuração de Serviços',
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'label' => 'Custo Extra',
                    'name' => 'additional_cost',
                    'type' => 'text',
                    'col' => 2,
                ],
                [
                    'label' => 'Tipo de Custo Extra',
                    'name' => 'additional_cost_type',
                    'type' => 'select',
                    'options' => [
                        'query' => [
                            ['id' => 0, 'name' => 'Percentual'],
                            ['id' => 1, 'name' => 'Valor Fixo']
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ],
                    'col' => 2,
                ],
                [
                    'label' => 'Prazo de entrega Extra',
                    'suffix' => 'dias úteis',
                    'name' => 'additional_time',
                    'type' => 'text',
                    'col' => 2,
                ],
            ],
            'submit' => [
                'title' => 'Salvar',
                'class' => 'btn btn-default pull-right'
            ],
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
    
    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

        if (is_array($this->_list)) {
            $nb = count($this->_list);
            
            for ($i = 0; $i < $nb; $i++) {
                if ($this->_list[$i]['type'] == 1) {
                    $this->_list[$i]['type'] = 'Normal';
                } else if ($this->_list[$i]['type'] = 2) {
                    $this->_list[$i]['type'] = 'Expresso';
                }

                switch ($this->_list[$i]['me_range']) {
                    case 'municipal':
                        $this->_list[$i]['me_range'] = 'Municipal';
                        break;
                    case 'intermunicipal':
                        $this->_list[$i]['me_range'] = 'Intermunicipal';
                        break;
                    case 'interstate':
                        $this->_list[$i]['me_range'] = 'Interestadual';
                        break;
                    case 'global':
                        $this->_list[$i]['me_range'] = 'Global';
                        break;
                }
            }
        }
    }

    public function displayInstallLink(
        $token = null,
        $id = 0,
        $name = null
    )
    {
        $tpl = $this->createTemplate('helpers/list/install.tpl');
        $tpl->assign([
            'link' => $this->context->link->getAdminLink('AdminAgMelhorEnvioServices'),
            'id' => $id
        ]);

        return $tpl->fetch();
    }

    public function initContent()
    {
        if ($this->display == 'view') {
            $this->loadObject();

            if (Tools::getIsSet('agmelhorenvio_submit')) {
                $optionals = Tools::getValue('optionals', array());
                try {
                    $this->object->configureOptionals($optionals);
                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=4');
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            } elseif (Tools::getIsSet('agmelhorenvio_cancel')) {
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
            }

            $this->context->controller->addJs(array(
                'https://cdnjs.cloudflare.com/ajax/libs/riot/2.6.7/riot+compiler.min.js'
            ));

            $this->context->smarty->assign(array(
                'object' => $this->object,
                'form_action' => self::$currentIndex . '&token=' . $this->token . '&view' . $this->table . '&' . $this->identifier . '=' . Tools::getValue($this->identifier)
            ));

            $this->setTemplate('view.tpl');
        } elseif (Tools::getValue('action') == 'install') {
            if (Tools::getIsSet('id_agmelhorenvio_service')) {
                $service = $this->loadObject();

                try {
                    if (!Validate::isLoadedObject($service)) {
                        throw new AgMelhorEnvioServiceFindingException(sprintf(
                            'Serviço #%d não encontrado.',
                            Tools::getValue('id_agmelhorenvio_service')
                        ));
                    }

                    $service->installCarrierToPrestaShop();
                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=' . self::CONF_INSTALLED);
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            }
        } elseif (Tools::getIsSet('own_hands' . $this->table)) {
            $obj = $this->loadObject();
            $obj->own_hands = !$obj->own_hands;
            $obj->update();
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=4');
        } elseif (Tools::getIsSet('receipt' . $this->table)) {
            $obj = $this->loadObject();
            $obj->receipt = !$obj->receipt;
            $obj->update();
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=4');
        } elseif (Tools::getIsSet('insurance' . $this->table)) {
            $obj = $this->loadObject();
            if($obj->carrier_name == 'Correios') {
                $obj->insurance = !$obj->insurance;
                $obj->update();
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=4');
            } else {
                $this->errors[] = "Não é possível desativar o VD no serviço {$obj->carrier_name}";
            }
        }

        parent::initContent();
    }

    public function processBulkInstall()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id) {
                $service = new AgMelhorEnvioService($id);

                try {
                    if (!Validate::isLoadedObject($service)) {
                        throw new AgMelhorEnvioServiceFindingException(sprintf(
                            'Serviço #%d não encontrado.',
                            Tools::getValue('id_agmelhorenvio_service')
                        ));
                    }

                    $service->installCarrierToPrestaShop();
                } catch (Exception $e) {
                    $this->errors[] = sprintf(
                        'Erro instalando o serviço #%d - %s',
                        $id,
                        $e->getMessage()
                    );
                }
            }
        }

        if (empty($this->errors)) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=' . self::CONF_INSTALLED);
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJs([
            _PS_MODULE_DIR_ . $this->module->name . '/views/js/admin_services.js'
        ]);
    }

    public function displayAdditionalCost($value, $row)
    {
        if ($row['additional_cost_type'] == 0) {
            return $value . '%';
        }

        return (new PriceFormatter())->format(
            $value,
            $this->context->currency
        );
    }
}
