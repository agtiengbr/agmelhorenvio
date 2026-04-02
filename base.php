<?php

use AGTI\Cliente\Factory\DeliveryTimeFormatterFactory;
use \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton;

require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgCarrierModule.php';

class BaseAgMelhorEnvio extends AgCarrierModule
{
    protected $hooks = array(
        'displayBackOfficeHeader',
        //para salvar o pacote associado ao pedido
        'actionValidateOrder',
        'dashboardZoneTwo',
        'actionAdminOrdersListingResultsModifier',
        'actionOrderStatusUpdate',

        'displayOrderPreview',
        'actionGetAdminOrderButtons',
        //adicionar os campos no pedido
        'displayAdminOrderTabContent',
        'displayAdminOrderTabLink',

        // botão de rastreamento no detalhe do pedido
        'displayTrackingButton',

        'displayAdminProductsExtra',
        // adiciona detalhes da(s) etiqueta(s) na fatura (PDF)
        'displayPDFInvoice',
    );

    protected $tabs = array(
        array(
            "name"      => "Melhor Envio",
            "className" => "AdminAgMelhorEnvioConfig",
            "active"    => 1
        ),
        array(
            "name"      => "Melhor Envio",
            "className" => "AdminAgMelhorEnvioServicess",
            "active"    => 0,
            "childs"    => array(
                array(
                    "active"    => 1,
                    "name"      => "Serviços",
                    "className" => "AdminAgMelhorEnvioServices",
                ),
                array(
                    "active"    => 1,
                    "name"      => "Gerar Etiquetas",
                    "className" => "AdminAgMelhorEnvioLabels",
                ),
                array(
                    "active"    => 1,
                    "name"      => "Rastreamentos",
                    "className" => "AdminAgMelhorEnvioLabelsTrack",
                ),
                array(
                    "active"    => 1,
                    "name"      => "Descontos",
                    "className" => "AdminAgMelhorEnvioDiscounts",
                ),
                array(
                    "active"    => 1,
                    "name"      => "Cache",
                    "className" => "AdminAgMelhorEnvioCache",
                ),
                array(
                    "active"    => 1,
                    "name"      => "Requisições API",
                    "className" => "AdminAgMelhorEnvioRequest",
                )
            )
        )
    );

    protected $workers = [
        [
            'name' => 'getPackagesPrices',
            'controller' => 'getPackagesPrice',
            'delay' => 90,
            'qty_wanted_workers' => 1
        ],
        [
            'name' => 'TrackLabels',
            'controller' => 'TrackLabels',
            'delay' => 900,
            'qty_wanted_workers' => 1
        ],
        [
            'name' => 'cleanCache',
            'controller' => 'cleanCache',
            'delay' => 3600,
            'qty_wanted_workers' => 1
        ]
    ];

    protected $main_tab = 'AdminParentShipping';

    protected $invoice_number_mapping;
    protected $address_number_mapping;

    /** @var AgColumnMapping */
    protected $cnpj_mapping;

    protected static $cache = array();
    protected static $delay = array();
    public $ignore_discounts = false;

    public $id_carrier;

    public function __construct()
    {
        $this->name     = 'agmelhorenvio';
        $this->tab      = 'shipping_logistics';
        $this->version  = '3.16.19';
        $this->author   = 'AGTI';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Melhor Envio';
        $this->description = 'Integra a sua loja com o Intermediador de Frete Melhor Envio.';

        $this->loadMappings();
        AgMelhorEnvioConfiguration::loadConfigurations();
        $this->initMelhorEnvioGateway();
    }

    public function install()
    {
        $success = parent::install();

        if ($success) {
            $this->createDefaultData();
        }

        AgMelhorEnvioService::installServices();

        if (!Configuration::get('AGMELHORENVIO_INSTALLED_ONCE')) {
            Configuration::updateValue('AGMELHORENVIO_INSTALLED_ONCE', 1);
            $this->resetConfig();
        }

        //cria a coluna de número e série da NF na tabela de pedidos
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'orders add column agmelhorenvio_invoice_number varchar(20)';
        try {
            Db::getInstance()->execute($sql);
            $this->getInvoiceNumberMapping()->mapsTo('agmelhorenvio_invoice_number');
        } catch (Exception $e) {
        }

        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'orders add column agmelhorenvio_invoice_serie varchar(255)';
        try {
            Db::getInstance()->execute($sql);
            $this->getInvoiceSerieMapping()->mapsTo('agmelhorenvio_invoice_serie');
        } catch (Exception $e) {
        }

        return $success;
    }

    public function resetConfig()
    {
        parent::resetConfig();

        AgMelhorEnvioConfiguration::setPaymentMode(0);
        AgMelhorEnvioConfiguration::setEnabledCache(1);
        AgMelhorEnvioConfiguration::setTimeExpireCache(3600);
    }


    public function getContent()
    {
        $this->context->controller->addJs(array(
            _PS_MODULE_DIR_.'agcliente' . '/views/js/jquery.mask.min.js',
            _PS_MODULE_DIR_. $this->name . '/views/js/add_masks.js'
        ));

        return $this->renderConfigTab();
    }

    public function hookDisplayTrackingButton($params)
    {
        // replica a lógica do agcorreios: só exibe o botão se houver rastreio para o pedido
        $idOrder = isset($params['id_order']) ? (int) $params['id_order'] : 0;
        if ($idOrder <= 0) {
            return '';
        }

        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        // Busca etiquetas do Melhor Envio vinculadas ao pedido
        $labels = AgMelhorEnvioLabel::getByIdOrder($idOrder);
        if (!is_array($labels) || count($labels) === 0) {
            return '';
        }

        // Verifica se existe ao menos um código de rastreio (tracking ou self_tracking)
        $hasTracking = false;
        foreach ($labels as $labelRow) {
            if (!empty($labelRow['tracking']) || !empty($labelRow['self_tracking'])) {
                $hasTracking = true;
                break;
            }
        }

        if (!$hasTracking) {
            return '';
        }

        $carrier = new Carrier($order->id_carrier);

        $followup = str_replace('@', $order->getWsShippingNumber(), $carrier->url);

        $this->context->smarty->assign([
            'id_order' => $idOrder,
            'tracking_number' => $order->getWsShippingNumber(),
            'followup' => $followup
        ]);
        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/hook/tracking_button.tpl');
    }

    public function hookDisplayPDFInvoice($params)
    {
        if (empty($params['object']) || !($params['object'] instanceof OrderInvoice)) {
            return '';
        }

        $idOrder = (int) $params['object']->id_order;
        if ($idOrder <= 0) {
            return '';
        }

        $labels = AgMelhorEnvioLabel::getByIdOrder($idOrder);
        if (!is_array($labels) || count($labels) === 0) {
            return '';
        }

        $labelsToShow = [];
        foreach ($labels as $labelRow) {
            if (empty($labelRow['id_order_remote'])) {
                continue;
            }

            $service = AgMelhorEnvioService::getByIdRemote((int) $labelRow['service_id']);
            $serviceDisplayName = '';
            if (!empty($service->name)) {
                $serviceDisplayName = (string) $service->name;
            } else {
                $parts = array_filter([(string) ($service->carrier_name ?? ''), (string) ($service->service_name ?? '')]);
                $serviceDisplayName = implode(' - ', $parts);
            }

            $labelsToShow[] = [
                'id_order_remote' => (string) $labelRow['id_order_remote'],
                'protocol' => (string) $labelRow['protocol'],
                'service_name' => (string) $serviceDisplayName,
                'tracking' => (string) (!empty($labelRow['tracking']) ? $labelRow['tracking'] : $labelRow['self_tracking']),
                'length' => (float) $labelRow['length'],
                'height' => (float) $labelRow['height'],
                'width' => (float) $labelRow['width'],
                'weight' => (float) $labelRow['weight'],
            ];
        }

        if (count($labelsToShow) === 0) {
            return '';
        }

        $smarty = !empty($params['smarty']) ? $params['smarty'] : $this->context->smarty;
        $smarty->assign([
            'agme_invoice_labels' => $labelsToShow,
        ]);

        return $smarty->fetch($this->getLocalPath() . 'views/templates/hook/pdf_invoice_labels.tpl');
    }

    public function checkConfigErrors()
    {

        $warnings = [];
        $errors = [];

        $id_br = Country::getByIso('br');
        if (strpos(strtoupper(AddressFormat::getAddressCountryFormat($id_br)), 'STATE') === false) {

            $redirect_url = $this->context->link->getAdminLink('AdminCountries') . '&updatecountry=&id_country=' . $id_br;

            $errors[] = "É necessário adicionar um campo da aba STATE no layout do endereço, para fazer isso <a href='{$redirect_url}' target='_blank' rel='noopener noreferrer'>clique aqui</a>, acesse a aba state e adicione alguma das opções";
        }

        $sandbox = Configuration::get('AGMELHORENVIO_CONFIGURATION_SANDBOX_ENABLED');

        $token_sandbox = Configuration::get('AGMELHORENVIO_CONFIGURATION_SANDBOX_TOKEN');
        $token_prod = Configuration::get('AGMELHORENVIO_CONFIGURATION_TOKEN');

        if (!self::validateToken($sandbox, '', $sandbox == 1 ? $token_sandbox : $token_prod)) {
            $errors[] = sprintf("Token de %s %s ", $sandbox == 1 ? 'sandbox' : 'produção', $sandbox == 0 && empty($token_prod) ? 'não foi preenchido' : 'inválido');
        }

        //verifica pelos dados de autenticação
        if (!AgMelhorEnvioConfiguration::getToken() || !AgMelhorEnvioConfiguration::getEmail()) {
            $errors[] = sprintf('Os dados de autenticação %s com o Melhor Envio não estão configurados.', empty($token_prod) ? 'de produção' : '');
        }

        //verifica se há algum serviço não instalado
        $qtt_uninstaled = 0;

        $services = AgMelhorEnvioService::getAll();
        foreach ($services as $service) {
            //serviço não mapeado para nenhuma transportadora
            if ($service->id_carrier == 0) {
                $qtt_uninstaled++;
                break;
            }
        }

        if ($qtt_uninstaled == count($services)) {
            $errors[] = 'Nenhum serviço do Melhor Envio está instalado em sua loja.';
        } elseif ($qtt_uninstaled) {
            $warnings[] = 'Há um ou mais serviços do Melhor Envio não instalados em sua loja.';
        }


        //verifica se os dados do remetente foram configurados
        $address = $this->getFromAddress();

        if (
            !AgMelhorEnvioConfiguration::getShopName() ||
            !$address->address ||
            !$address->number ||
            !$address->city ||
            !$address->state ||
            !$address->postcode ||
            !$address->district
        ) {
            $errors[] = 'Os dados do remetente não estão completamente configurados.';
        }

        if (!AgMelhorEnvioConfiguration::getCnpj()) {
            $warnings[]  = 'O CNPJ do remetente não foi configurado. Ele é obrigatório para algumas transportadoras.';
        }

        if (AgMelhorEnvioConfiguration::getCnpj() && !AgMelhorEnvioConfiguration::getCnae()) {
            $warnings[] = 'O CNAE do remetente não foi configurado. Ele é obrigatório para envios com CNPJ na transportadora LaTAM Cargo.';
        }

        if ($this->getCpfMapping()->isMappingEnabled() == false) {
            $errors[] = 'O Mapeamento de CPF não está configurado. O CPF ou o CNPJ do destinatário são obrigatórios para todas as transportadoras do Melhor Envio.';
        }

        if ($this->getAddressNumberMapping()->isMappingEnabled() == false || $this->getAddressComplementMapping()->isMappingEnabled() == false) {
            $warnings[] = 'Alguns dos campos não estão mapeados, o que pode fazer com que o endereço do cliente não seja inserido de forma completa na etiqueta de postagem.';
        }

        return compact('warnings', 'errors');
    }

    static function validateToken($sandbox = 0, $email = '', $token = '')
    {

        try {
            if ($sandbox == 0) {
                $sandbox = Tools::getValue('agmelhorenvio_sandbox_enabled');
            }

            AgMelhorEnvioGateway::setSandbox($sandbox);

            if ($sandbox == 1) {
                AgMelhorEnvioGateway::setToken(!empty($token) ? $token : Tools::getValue('agmelhorenvio_sandbox_token'));
            } else {
                AgMelhorEnvioGateway::setToken(!empty($token) ? $token : Tools::getValue('agmelhorenvio_token'));
            }

            $to_remote = new AgMelhorEnvioRemoteAddress();
            $to_remote->setPostalCode('96020360');

            $from_remote = new AgMelhorEnvioRemoteAddress();
            $from_remote->setPostalCode('01018020');

            $options = new AgMelhorEnvioRemoteOptions();

            $products = [
                [
                    "id" => "2",
                    "width" => 16,
                    "height" => 25,
                    "length" => 11,
                    "weight" => 0.3,
                    "insurance_value" => 55.05,
                    "quantity" => 2
                ],
                [
                    "id" => "1",
                    "width" => 11,
                    "height" => 17,
                    "length" => 11,
                    "weight" => 0.3,
                    "insurance_value" => 10.1,
                    "quantity" => 1
                ]
            ];

            $response = AgMelhorEnvioGateway::simulateShipping(
                $from_remote,
                $to_remote,
                $options,
                $products,
                [],
                [],
                false
            );

            foreach ($response as $r) {
                if (!empty($r->getIdService())) {
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            AgClienteLogger::addLog("agmelhorenvio - Erro na validaçao: " . $e->getMessage(), 3, $e->getCode(), "", "", true);
        }
    }

    protected function renderConfigAuthenticationTab()
    {
        //salvar configuração autenticação
        if (Tools::isSubmit('agmelhorenvio-config-auth')) {

            $email = Tools::getValue('agmelhorenvio_email');
            if ($email && !Validate::isEmail($email)) {
                $this->context->controller->errors[] = 'E-mail de produção inválido.';
            } else {
                AgMelhorEnvioConfiguration::setEmail(trim($email));
            }

            $sandbox_email = Tools::getValue('agmelhorenvio_sandbox_email');
            if ($sandbox_email && !Validate::isEmail($sandbox_email)) {
                $this->context->controller->errors[] = 'E-mail de sandbox inválido.';
            } else {
                AgMelhorEnvioConfiguration::setSandboxEmail(trim($sandbox_email));
            }

            AgMelhorEnvioConfiguration::setPaymentMode(trim(Tools::getValue('agmelhorenvio_payment_mode')));

            AgMelhorEnvioConfiguration::setToken(trim(Tools::getValue('agmelhorenvio_token')));
            AgMelhorEnvioConfiguration::setSandboxToken(trim(Tools::getValue('agmelhorenvio_sandbox_token')));

            AgMelhorEnvioConfiguration::setSandboxEnabled(trim(Tools::getValue('agmelhorenvio_sandbox_enabled')));

            AgMelhorEnvioConfiguration::setSandboxPaymentMode(trim(Tools::getValue('agmelhorenvio_sandbox_payment_mode')));
        }

        $helper = $this->generateDefaultHelperForm();

        $panels = [];

        $panels[0]['form'] = [
                'legend' => [
                    'title' => 'Produção',
                    'icon' => 'icon-cogs'
                ],
                'input'  => [
                    [
                        'label' => 'E-mail',
                        'name' => 'agmelhorenvio_email',
                        'type' => 'text',
                        'col' => 3
                    ],
                    [
                        'label' => 'Token',
                        'name' => 'agmelhorenvio_token',
                        'type' => 'textarea',
                        'rows' => 20,
                        'desc' => 'Se tiver dúvidas sobre a geração do token de acesso, consulte a documentação do Melhor Envio.'
                    ],
                    [
                        'label' => 'Forma de Pagamento das Etiquetas',
                        'name' => 'agmelhorenvio_payment_mode',
                        'type' => 'radio',
                        'values' => [
                            [
                                'value' => '0',
                                'label' => 'Saldo em Carteira',
                                'id' => 'agmelhorenvio_payment_mode_0'
                            ],
                            [
                                'value' => 'mercado-pago',
                                'label' => 'Mercado Pago',
                                'id' => 'agmelhorenvio_payment_mode_mp'
                            ],
                            [
                                'value' => 'moip',
                                'label' => 'Wirecard',
                                'id' => 'agmelhorenvio_payment_mode_moip'
                            ],
                            [
                                'value' => 'picpay',
                                'label' => 'PicPay',
                                'id' => 'agmelhorenvio_payment_mode_picpay'
                            ],
                            [
                                'value' => 'pagseguro',
                                'label' => 'PagSeguro',
                                'id' => 'agmelhorenvio_payment_mode_pagseguro'
                            ]
                        ]
                    ]
                ],
                'submit' => [
                    'title' => "Salvar",
                    "name"  => "agmelhorenvio-config-auth",
                ]
            ];


        $panels[1]['form'] = [
            'legend' => [
                'title' => 'Ambiente de Testes',
                'icon' => 'icon-exclamation'
            ],
            'description' => 'No ambiente de testes, as etiquetas geradas não poderão ser utilizadas.',
            'input'  => [
                [
                    'type'   => 'switch',
                    'label'  => 'Ativar Sandbox',
                    'name'   => 'agmelhorenvio_sandbox_enabled',
                    'id'     => 'agmelhorenvio_sandbox_enabled',
                    'values' => array(
                        array(
                            'id'    => 'agmelhorenvio_sandbox_enabled_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agmelhorenvio_sandbox_enabled_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    ),
                    'readonly' => false
                ],
                [
                    'label' => 'E-mail',
                    'name' => 'agmelhorenvio_sandbox_email',
                    'type' => 'text',
                    'col' => 3,
                    'readonly' => false
                ],
                [
                    'label' => 'Token',
                    'name' => 'agmelhorenvio_sandbox_token',
                    'type' => 'textarea',
                    'rows' => 20,
                    'readonly' => false
                ],
                [
                    'label' => 'Forma de Pagamento das Etiquetas',
                    'name' => 'agmelhorenvio_sandbox_payment_mode',
                    'type' => 'radio',
                    'values' => [
                        [
                            'value' => 'mercado-pago',
                            'label' => 'Mercado Pago',
                            'id' => 'agmelhorenvio_sandbox_payment_mode_mp'
                        ],
                        [
                            'value' => 'moip',
                            'label' => 'Wirecard',
                            'id' => 'agmelhorenvio_sandbox_payment_mode_moip'
                        ],
                        [
                            'value' => 'picpay',
                            'label' => 'PicPay',
                            'id' => 'agmelhorenvio_sandbox_payment_mode_picpay'
                        ],
                        [
                            'value' => 'pagseguro',
                            'label' => 'PagSeguro',
                            'id' => 'agmelhorenvio_sandbox_payment_mode_pagseguro'
                        ]
                    ]
                ]
            ],
            'submit' => [
                'title' => "Salvar",
                "name"  => "agmelhorenvio-config-auth"
            ]
        ];


        $helper->fields_value['agmelhorenvio_email'] = AgMelhorEnvioConfiguration::getEmail();
        $helper->fields_value['agmelhorenvio_token'] = AgMelhorEnvioConfiguration::getToken();
        $helper->fields_value['agmelhorenvio_payment_mode'] = AgMelhorEnvioConfiguration::getPaymentMode();
        $helper->fields_value['agmelhorenvio_sandbox_enabled'] = AgMelhorEnvioConfiguration::getSandboxEnabled();
        $helper->fields_value['agmelhorenvio_sandbox_email'] = AgMelhorEnvioConfiguration::getSandboxEmail();
        $helper->fields_value['agmelhorenvio_sandbox_token'] = AgMelhorEnvioConfiguration::getSandboxToken();
        $helper->fields_value['agmelhorenvio_sandbox_payment_mode'] = AgMelhorEnvioConfiguration::getSandboxPaymentMode();

        return $helper->generateForm($panels);
    }

    protected function renderConfigConfigurationTab()
    {
        if (Tools::isSubmit('agmelhorenvio-config-configuration')) {
            AgMelhorEnvioConfiguration::setAutoGenerateLabels(Tools::getValue('agmelhorenvio_auto_generate_labels'));
            AgMelhorEnvioConfiguration::setAutoGenerateLabelStates(Tools::getValue('agmelhorenvio_auto_generate_label_states', []));
            AgMelhorEnvioConfiguration::setHandlingTime(Tools::getValue('agmelhorenvio_handling_time'));
            AgMelhorEnvioConfiguration::setTimeoutClearRequests(Tools::getValue('agmelhorenvio_timeout_clear_requests'));
            AgMelhorEnvioConfiguration::setEnabledCache(Tools::getValue('agmelhorenvio_enabled_cache'));
            AgMelhorEnvioConfiguration::setTimeExpireCache(
                Tools::getValue('agmelhorenvio_time_expire_cache') > 0 ? Tools::getValue('agmelhorenvio_time_expire_cache') * 3600 : 0
            );
            AgMelhorEnvioConfiguration::setCoupon(Tools::getValue('agmelhorenvio_coupons'));
        }

        $helper = $this->generateDefaultHelperForm();

        $panels = [];
        $order_states = OrderState::getOrderStates($this->context->language->id);
        $order_states_for_select = [];
        foreach ($order_states as $order_state) {
            $order_states_for_select[] = [
                'id' => (int) $order_state['id_order_state'],
                'name' => $order_state['name']
            ];
        }

        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Configurações',
                'icon' => 'icon-cogs'
            ],
            'input'  => [
                [
                    'label' => 'Tempo de Preparação',
                    'name' => 'agmelhorenvio_handling_time',
                    'type' => 'text',
                    'col' => 3,
                    'suffix' => 'dias úteis',
                    'desc' => 'Esse tempo será adicionado ao prazo de entrega retornado pelas transportadoras.'
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Emissão Automática das Etiquetas',
                    'name'   => 'agmelhorenvio_auto_generate_labels',
                    'id'     => 'agmelhorenvio_auto_generate_labels',
                    'desc'   => 'Essa opção requer que as etiquetas sejam pagas pelo pelo saldo em carteira. As etiquetas serão geradas assim que os pedidos forem pagos.',
                    'values' => array(
                        array(
                            'id'    => 'agmelhorenvio_auto_generate_labels_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agmelhorenvio_auto_generate_labels_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )
                ],
                [
                    'label' => 'Emitir automaticamente ao entrar nestes status',
                    'name' => 'agmelhorenvio_auto_generate_label_states[]',
                    'id' => 'agmelhorenvio_auto_generate_label_states',
                    'type' => 'select',
                    'multiple' => true,
                    'size' => 8,
                    'desc' => 'Selecione um ou mais status. A etiqueta será emitida quando o pedido entrar em um dos status selecionados.',
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $order_states_for_select
                    ]
                ],
                [
                    'label' => 'Manter registro das requisições feitas à API do Melhor Envio por:',
                    'name' => 'agmelhorenvio_timeout_clear_requests',
                    'type' => 'text',
                    'class' => 'center',
                    'col' => 3,
                    'suffix' => 'dias',
                    'desc'   => 'Se deixar 0 dias, a tabela de requisições não será limpa e isso pode fazer com que o disco do servidor encha mais rapidamente.',
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Habilitar cache da cotação de frete',
                    'name'   => 'agmelhorenvio_enabled_cache',
                    'id'     => 'agmelhorenvio_enabled_cache',
                    'hint'   => 'O cache fará com que os preços e prazos do frete sejam salvos em seu banco de dados, evitando a necessidade de consultar o servidor do Melhor Envio a cada carregamento de página.',
                    'desc'   => '',
                    'values' => array(
                        array(
                            'id'    => 'agmelhorenvio_enabled_cache_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agmelhorenvio_enabled_cache_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )
                ],
                [
                    'label' => 'Expirar o cache em',
                    'name' => 'agmelhorenvio_time_expire_cache',
                    'type' => 'text',
                    'class' => 'center',
                    'col' => 3,
                    'suffix' => 'horas',
                    'desc'   => 'Se deixar 0, o cache nunca expirará.',
                ],
                [
                    'label' => 'Cupom de desconto',
                    'name' => 'agmelhorenvio_coupons',
                    'type' => 'text',
                    'class' => 'center',
                    'col' => 3,
                    'desc'   => 'Se você possuir um cupom de desconto do Melhor Envio, insira neste campo ele te garantirá desconto no pagamento das etiquetas',
                ],
            ],
            'submit' => [
                'title' => "Salvar",
                "name"  => "agmelhorenvio-config-configuration",
            ]
        ];

        $helper->fields_value['agmelhorenvio_handling_time'] = AgMelhorEnvioConfiguration::getHandlingtime();
        $helper->fields_value['agmelhorenvio_timeout_clear_requests'] = AgMelhorEnvioConfiguration::getTimeoutClearRequests();
        $helper->fields_value['agmelhorenvio_auto_generate_labels'] = AgMelhorEnvioConfiguration::getAutoGenerateLabels();
        $helper->fields_value['agmelhorenvio_auto_generate_label_states[]'] = AgMelhorEnvioConfiguration::getAutoGenerateLabelStates();
        $helper->fields_value['agmelhorenvio_auto_generate_label_states'] = AgMelhorEnvioConfiguration::getAutoGenerateLabelStates();
        $helper->fields_value['agmelhorenvio_enabled_cache'] = AgMelhorEnvioConfiguration::getEnabledCache();
        $helper->fields_value['agmelhorenvio_time_expire_cache'] = AgMelhorEnvioConfiguration::getTimeExpireCache() / 3600;
        $helper->fields_value['agmelhorenvio_coupons'] = AgMelhorEnvioConfiguration::getCoupon();

        return $helper->generateForm($panels);
    }

    protected function renderConfigShopDataTab()
    {
        //salvar configurações remetente
        if (Tools::isSubmit('agmelhorenvio-config-shop-data')) {
            AgMelhorEnvioConfiguration::setShopName(Tools::getValue('agmelhorenvio_shop_name'));
            AgMelhorEnvioConfiguration::setShopAddress(Tools::getValue('agmelhorenvio_shop_address'));
            AgMelhorEnvioConfiguration::setShopAddressNumber(Tools::getValue('agmelhorenvio_shop_address_number'));
            AgMelhorEnvioConfiguration::setShopAddressDistrict(Tools::getValue('agmelhorenvio_shop_address_district'));

            $new_city = Tools::getValue('agmelhorenvio_shop_address_city');

            if (!$new_city) {
                $this->context->controller->errors[] = "A cidade do remetente é obrigatória.";
            } elseif (strlen($new_city) < 3) {
                $this->context->controller->errors[] = "A cidade do remetente deve ter ao menos 3 letras.";
            } else {
                AgMelhorEnvioConfiguration::setShopAddressCity($new_city);
            }

            $new_uf = Tools::getValue('agmelhorenvio_shop_address_state');
            if (!$new_uf) {
                $this->context->controller->errors[] = "O estado do remetente é obrigatório.";
            } elseif (strlen($new_uf) != 2) {
                $this->context->controller->errors[] = "O estado do remetente deve ter exatamente duas letras.";
            } else {
                AgMelhorEnvioConfiguration::setShopAddressState($new_uf);
            }

            AgMelhorEnvioConfiguration::setShopAddressZipcode(Tools::getValue('agmelhorenvio_shop_address_zipcode'));
            AgMelhorEnvioConfiguration::setShopAddressPhone(Tools::getValue('agmelhorenvio_shop_address_phone'));
            AgMelhorEnvioConfiguration::setCnpj(Tools::getValue('agmelhorenvio_cnpj'));
            AgMelhorEnvioConfiguration::setCnae(Tools::getValue('agmelhorenvio_cnae'));
            AgMelhorEnvioConfiguration::setStateRegister(Tools::getValue('agmelhorenvio_state_register'));

            if (Tools::getValue('agmelhorenvio_agency_jadlog') != '-1') {
                AgMelhorEnvioConfiguration::setAgencyJadlog(Tools::getValue('agmelhorenvio_agency_jadlog'));
            }

            if (Tools::getValue('agmelhorenvio_agency_latam') != '-1') {
                AgMelhorEnvioConfiguration::setAgencyLatam(Tools::getValue('agmelhorenvio_agency_latam'));
            }

            if (Tools::getValue('agmelhorenvio_agency_total_express') != '-1') {
                AgMelhorEnvioConfiguration::setAgencyTotalExpress(Tools::getValue('agmelhorenvio_agency_total_express'));
            }
        }

        $helper = $this->generateDefaultHelperForm();

    // prepare agency arrays so they are defined even if gateway call fails
    $agencies_jadlog = [];
    $agencies_latam = [];
    $agencies_total_express = [];

        try {
            $agencies = AgMelhorEnvioGateway::getAgencies();
            usort($agencies, function ($a1, $a2) {
                $address1 = $a1->getAddress();
                $address2 = $a2->getAddress();

                if ($address1->getUf() == '' && $address2->getUf() != '') {
                    return 1;
                }

                if ($address2->getUf() == '' && $address1->getUf() != '') {
                    return -1;
                }

                if ($address1->getUf() == '') {
                    return strcmp($a1->getName(), $a2->getName());
                }

                if ($address1->getUf() != $address2->getUf()) {
                    return strcmp($address1->getUf(), $address2->getUf());
                }

                return strcmp($address1->getCity(), $address2->getCity());
            });

            $agencies_jadlog[] = [
                'id' => 0,
                'text' => 'SELECIONE A AGÊNCIA'
            ];

            $agencies_latam[] = [
                'id' => 0,
                'text' => 'SELECIONE A AGÊNCIA'
            ];

            $agencies_total_express[] = [
                'id' => 0,
                'text' => 'SELECIONE A AGÊNCIA'
            ];

            foreach ($agencies as $agency) {
                $agency_text = '';
                if ($agency->getAddress()->getUf() != '') {
                    $agency_text .= $agency->getAddress()->getUf() . ' - ';
                }

                if ($agency->getAddress()->getCity() != '') {
                    $agency_text .= $agency->getAddress()->getCity() . ' - ';
                }

                if ($agency->getCompanyName()) {
                    $agency_text .= $agency->getCompanyName() . ' - ';
                } else {
                    $agency_text .= $agency->getName() . ' - ';
                }

                if ($agency->getAddress()->getAddress() != '') {
                    $agency_text .= $agency->getAddress()->getAddress();
                }

                //verifica se é uma ag. jadlog
                foreach ($agency->getCompanies() as $company) {
                    if ($company->getId() == 2) {
                        $agencies_jadlog[] = [
                            'id' => $agency->getId(),
                            'text' => $agency_text
                        ];
                    }

                    if ($company->getId() == 6) {
                        $agencies_latam[] = [
                            'id' => $agency->getId(),
                            'text' => $agency_text
                        ];
                    }

                    if ($company->getId() == 8) {
                        $agencies_total_express[] = [
                            'id' => $agency->getId(),
                            'text' => $agency_text
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // fallback error entries for selects
            $agencies_jadlog[] = [
                'id' => '-1',
                'text' => 'Erro consultando as agências JadLog junto ao Melhor Envio'
            ];
            $agencies_latam[] = [
                'id' => '-1',
                'text' => 'Erro consultando as agências LaTAM junto ao Melhor Envio'
            ];
            $agencies_total_express[] = [
                'id' => '-1',
                'text' => 'Erro consultando as agências Total Express junto ao Melhor Envio'
            ];
        }
        $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/completeCEP.js');

        $panels = [];
        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Dados do Remetente',
                'icon' => 'icon-map-market'
            ],
            'input'  => [
                [
                    'label' => 'Nome do Remetente',
                    'name' => 'agmelhorenvio_shop_name',
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'label' => 'CEP',
                    'name' => 'agmelhorenvio_shop_address_zipcode',
                    'type' => 'text',
                    'col' => 2,
                    'required' => true
                ],
                [
                    'label' => 'Endereço',
                    'name' => 'agmelhorenvio_shop_address',
                    'type' => 'text',
                    'col' => 2,
                    'required' => true
                ],
                [
                    'label' => 'Número',
                    'name' => 'agmelhorenvio_shop_address_number',
                    'type' => 'text',
                    'col' => 1,
                    'required' => true
                ],
                [
                    'label' => 'Bairro',
                    'name' => 'agmelhorenvio_shop_address_district',
                    'type' => 'text',
                    'col' => 2,
                    'required' => true
                ],
                [
                    'label' => 'Cidade',
                    'name' => 'agmelhorenvio_shop_address_city',
                    'type' => 'text',
                    'col' => 2,
                    'required' => true
                ],
                [
                    'label' => 'Estado',
                    'name' => 'agmelhorenvio_shop_address_state',
                    'type' => 'select',
                    'col' => 2,
                    'required' => true,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => [
                            [
                                'name' => 'Acre',
                                'id' => 'AC'
                            ],
                            [
                                'name' => 'Alagoas',
                                'id' => 'AL'
                            ],
                            [
                                'id' => 'AP',
                                'name' => 'Amapá'
                            ],
                            [
                                'id' => 'AM',
                                'name' => 'Amazonas'
                            ],
                            [
                                'id' => 'BA',
                                'name' => 'Bahia'
                            ],
                            [
                                'id' => 'CE',
                                'name' => 'Ceará'
                            ],
                            [
                                'id' => 'DF',
                                'name' => 'Distrito Federal'
                            ],
                            [
                                'id' => 'ES',
                                'name' => 'Espírito Santo'
                            ],
                            [
                                'id' => 'GO',
                                'name' => 'Goiás'
                            ],
                            [
                                'id' => 'MA',
                                'name' => 'Maranhão'
                            ],
                            [
                                'id' => 'MT',
                                'name' => 'Mato Grosso'
                            ],
                            [
                                'id' => 'MS',
                                'name' => 'Mato Grosso do Sul'
                            ],
                            [
                                'id' => 'MG',
                                'name' => 'Minas Gerais'
                            ],
                            [
                                'id' => 'PA',
                                'name' => 'Pará'
                            ],
                            [
                                'id' => 'PB',
                                'name' => 'Paraíba'
                            ],
                            [
                                'id' => 'PR',
                                'name' => 'Paraná'
                            ],
                            [
                                'id' => 'PE',
                                'name' => 'Pernambuco'
                            ],
                            [
                                'id' => 'PI',
                                'name' => 'Piauí'
                            ],
                            [
                                'id' => 'RJ',
                                'name' => 'Rio de Janeiro'
                            ],
                            [
                                'id' => 'RN',
                                'name' => 'Rio Grande do Norte'
                            ],
                            [
                                'id' => 'RS',
                                'name' => 'Rio Grande do Sul'
                            ],
                            [
                                'id' => 'RO',
                                'name' => 'Rondônia'
                            ],
                            [
                                'id' => 'RR',
                                'name' => 'Roraima'
                            ],
                            [
                                'id' => 'SC',
                                'name' => 'Santa Cataina'
                            ],
                            [
                                'id' => 'SP',
                                'name' => 'São Paulo'
                            ],
                            [
                                'id' => 'SE',
                                'name' => 'Sergipe'
                            ],
                            [
                                'id' => 'TO',
                                'name' => 'Tocantins'
                            ],
                        ]
                    ]
                ],
                [
                    'label' => 'Número de Telefone',
                    'name' => 'agmelhorenvio_shop_address_phone',
                    'type' => 'text',
                    'col' => 2,
                    'required' => true
                ],
                [
                    'label' => 'CNPJ',
                    'name' => 'agmelhorenvio_cnpj',
                    'type' => 'text',
                    'col' => 2,
                    'help' => 'Este campo é obrigatório para algumas transportadoras.'

                ],
                [
                    'label' => 'CNAE',
                    'name' => 'agmelhorenvio_cnae',
                    'type' => 'text',
                    'col' => 2,
                    'help' => 'Obrigatório para envios com CNPJ na transportadora LaTAM Cargo.'

                ],
                [
                    'label' => 'Inscrição Estadual',
                    'name' => 'agmelhorenvio_state_register',
                    'type' => 'text',
                    'col' => 2,
                    'help' => 'Este campo é obrigatório para algumas transportadoras.'
                ],
                [
                    'label' => 'Agência Jadlog',
                    'name' => 'agmelhorenvio_agency_jadlog',
                    'type' => 'select',
                    'col' => 8,
                    'options' => [
                        'id' => 'id',
                        'name' => 'text',
                        'query' => $agencies_jadlog
                    ]
                ],
                [
                    'label' => 'Agência LaTAM',
                    'name' => 'agmelhorenvio_agency_latam',
                    'type' => 'select',
                    'col' => 8,
                    'options' => [
                        'id' => 'id',
                        'name' => 'text',
                        'query' => $agencies_latam
                    ]
                ],
                [
                    'label' => 'Agência Total Express',
                    'name' => 'agmelhorenvio_agency_total_express',
                    'type' => 'select',
                    'col' => 8,
                    'options' => [
                        'id' => 'id',
                        'name' => 'text',
                        'query' => $agencies_total_express
                    ]
                ],
            ],
            'submit' => [
                'title' => "Salvar",
                "name"  => "agmelhorenvio-config-shop-data",
            ]
        ];


        $helper->fields_value['agmelhorenvio_shop_name'] = AgMelhorEnvioConfiguration::getShopName();
        $helper->fields_value['agmelhorenvio_shop_address'] = AgMelhorEnvioConfiguration::getShopAddress();
        $helper->fields_value['agmelhorenvio_shop_address_number'] = AgMelhorEnvioConfiguration::getShopAddressNumber();
        $helper->fields_value['agmelhorenvio_shop_address_district'] = AgMelhorEnvioConfiguration::getShopAddressDistrict();
        $helper->fields_value['agmelhorenvio_shop_address_city'] = AgMelhorEnvioConfiguration::getShopAddressCity();
        $helper->fields_value['agmelhorenvio_shop_address_state'] = AgMelhorEnvioConfiguration::getShopAddressState();
        $helper->fields_value['agmelhorenvio_shop_address_zipcode'] = AgMelhorEnvioConfiguration::getShopAddressZipcode();
        $helper->fields_value['agmelhorenvio_shop_address_phone'] = AgMelhorEnvioConfiguration::getShopAddressPhone();
        $helper->fields_value['agmelhorenvio_cnpj'] = AgMelhorEnvioConfiguration::getCnpj();
        $helper->fields_value['agmelhorenvio_cnae'] = AgMelhorEnvioConfiguration::getCnae();
        $helper->fields_value['agmelhorenvio_state_register'] = AgMelhorEnvioConfiguration::getStateRegister();
        $helper->fields_value['agmelhorenvio_agency_jadlog'] = AgMelhorEnvioConfiguration::getAgencyJadlog();
        $helper->fields_value['agmelhorenvio_agency_latam'] = AgMelhorEnvioConfiguration::getAgencyLatam();
        $helper->fields_value['agmelhorenvio_agency_total_express'] = AgMelhorEnvioConfiguration::getAgencyTotalExpress();


        return $helper->generateForm($panels);
    }

    protected function renderConfigMappingsTab()
    {
        if (Tools::isSubmit('agmelhorenvio-config-mappings')) {
            $this->getInvoiceNumberMapping()->mapsTo(Tools::getValue('agmelhorenvio_invoice_number'));
            $this->getInvoiceSerieMapping()->mapsTo(Tools::getValue('agmelhorenvio_invoice_serie'));
            $this->getCpfMapping()->mapsTo(Tools::getValue('agmelhorenvio_cpf_mapping'));
            $this->getCnpjMapping()->mapsTo(Tools::getValue('agmelhorenvio_cnpj_mapping'));
            $this->getAddressNumberMapping()->mapsTo(Tools::getValue('agmelhorenvio_address_number_mapping'));
            $this->getAddressComplementMapping()->mapsTo(Tools::getValue('agmelhorenvio_address_complement_mapping'));

            $me_statuses = AgMelhorEnvioLabel::getStatuses();
            foreach ($me_statuses as $me_status) {
                Configuration::updateValue('AGMELHORENVIO_STATUS_MAPPING_' . $me_status, Tools::getValue('AGMELHORENVIO_STATUS_MAPPING_' . $me_status));
            }
            AgMelhorEnvioConfiguration::setAgmelhorenvioStatusMappingEnabled(Tools::getValue('agmelhorenvio_status_mapping_enabled'));
            AgMelhorEnvioConfiguration::setSendTrackingEmail(Tools::getValue('agmelhorenvio_send_tracking_email'));
        }

        $helper = $this->generateDefaultHelperForm();

        $invoice_number_fields = [];
        foreach ($this->getInvoiceNumberMapping()->getColumnsFromTable() as $key => $column) {
            $invoice_number_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $invoice_serie_fields = [];
        foreach ($this->getInvoiceSerieMapping()->getColumnsFromTable() as $key => $column) {
            $invoice_serie_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $cpf_fields = [];
        foreach ($this->getCpfMapping()->getColumnsFromTable() as $key => $column) {
            $cpf_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $cnpj_fields = [];
        foreach ($this->getCnpjMapping()->getColumnsFromTable() as $key => $column) {
            $cnpj_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $number_fields = [];
        foreach ($this->getAddressNumberMapping()->getColumnsFromTable() as $key => $column) {
            $number_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $complement_fields = [];
        foreach ($this->getAddressComplementMapping()->getColumnsFromTable() as $key => $column) {
            $complement_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $panels = [];
        
        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Mapeamento de Campos'
            ],
            'input'  => [
                [
                    'label' => 'Número da Nota Fiscal',
                    'name' => 'agmelhorenvio_invoice_number',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $invoice_number_fields
                    ]
                ],
                [
                    'label' => 'Série da Nota Fiscal',
                    'name' => 'agmelhorenvio_invoice_serie',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $invoice_serie_fields
                    ]
                ]
            ],
            'submit' => [
                'title' => "Salvar",
                "name"  => "agmelhorenvio-config-mappings",
            ]
        ];

        if (!Module::isEnabled('agcustomers')) {
            $panels[0]['form']['input'] = array_merge($panels[0]['form']['input'], [
                [
                    'label' => 'CPF',
                    'name' => 'agmelhorenvio_cpf_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $cpf_fields
                    ]
                ],
                [
                    'label' => 'CNPJ',
                    'name' => 'agmelhorenvio_cnpj_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $cnpj_fields
                    ]
                ],
                [
                    'label' => 'Número do Endereço',
                    'name' => 'agmelhorenvio_address_number_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $number_fields
                    ]
                ],
                [
                    'label' => 'Complemento do Endereço',
                    'name' => 'agmelhorenvio_address_complement_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $complement_fields
                    ]
                ],
            ]);
        }

        $panels[1]['form'] = [
            'legend' => [
                'title' => 'Mapeamento de Status'
            ],
            'description' => 'O mapeamento de status faz com que os pedidos sejam atualizados automaticamente na sua plataforma quando as etiquetas do Melhor Envio sofrerem alterações.',
            'input' => [
                [
                    'type'   => 'switch',
                    'label'  => 'Ativar Mapeamento de status',
                    'name'   => 'agmelhorenvio_status_mapping_enabled',
                    'id'     => 'agmelhorenvio_status_mapping_enabled',
                    'values' => array(
                        array(
                            'id'    => 'agmelhorenvio_status_mapping_enabled_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agmelhorenvio_status_mapping_enabled_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    ),
                ],
                [
                    'type' => 'switch',
                    'label' => 'Enviar e-mail de rastreio para o cliente',
                    'hint' => 'O e-mail será enviado assim que o módulo atribuir um código de postagem ao pedido.',
                    'name' => 'agmelhorenvio_send_tracking_email',
                    'id' => 'agmelhorenvio_send_tracking_email',
                    'values' => [
                        [
                            'id' => 'agmelhorenvio_send_tracking_email_on',
                            'value' => 1,
                            'label' => 'Sim'
                        ],
                        [
                            'id' => 'agmelhorenvio_send_tracking_email_off',
                            'value' => 0,
                            'label' => 'Não'
                        ]
                    ]
                ]
            ],
            'submit' => [
                'title' => "Salvar",
                "name"  => "agmelhorenvio-config-mappings",
            ]
        ];

        $order_states = OrderState::getOrderStates($this->context->language->id);
        $order_states_for_select[] = ['id' => 0, 'name' => 'Não atualizar etiquetas nesse estado'];

        foreach ($order_states as $order_state) {
            $order_states_for_select[] = [
                'id' => $order_state['id_order_state'],
                'name' => $order_state['name']
            ];
        }

        $me_statuses = AgMelhorEnvioLabel::getStatuses();

        foreach ($me_statuses as $me_status) {
            $input = [
                'label' => AgMelhorEnvioLabel::getStatusText($me_status),
                'type' => 'select',
                'name' => "AGMELHORENVIO_STATUS_MAPPING_{$me_status}",
                'col' => 8,
                'options' => [
                    'name' => 'name',
                    'id' => 'id',
                    'query' => $order_states_for_select
                ]
             ];

            $panels[1]['form']['input'][] = $input;

            $helper->fields_value["AGMELHORENVIO_STATUS_MAPPING_{$me_status}"] = Configuration::get("AGMELHORENVIO_STATUS_MAPPING_{$me_status}");
        }

        $helper->fields_value['agmelhorenvio_status_mapping_enabled'] = AgMelhorEnvioConfiguration::getAgmelhorenvioStatusMappingEnabled();
        $helper->fields_value['agmelhorenvio_invoice_number'] = $this->getInvoiceNumberMapping()->getMappedfield();
        $helper->fields_value['agmelhorenvio_invoice_serie'] = $this->getInvoiceSerieMapping()->getMappedfield();
        $helper->fields_value['agmelhorenvio_cpf_mapping'] = $this->getCpfMapping()->getMappedfield();
        $helper->fields_value['agmelhorenvio_cnpj_mapping'] = $this->getCnpjMapping()->getMappedfield();
        $helper->fields_value['agmelhorenvio_address_number_mapping'] = $this->getAddressNumberMapping()->getMappedfield();
        $helper->fields_value['agmelhorenvio_address_complement_mapping'] = $this->getAddressComplementMapping()->getMappedfield();
        $helper->fields_value['agmelhorenvio_send_tracking_email'] = AgMelhorEnvioConfiguration::getSendTrackingEmail();

        return $helper->generateForm($panels);
    }

    protected function renderConfigTab()
    {
        $agcliente = new agcliente;
        agcliente::prepareConfigHelpTab($this->name);

        $auth_tab = $this->renderConfigAuthenticationTab();
        $config_tab = $this->renderConfigConfigurationTab();
        $shop_data_tab = $this->renderConfigShopDataTab();
        $mappings_tab = $this->renderConfigMappingsTab();

        $errors = $this->checkConfigErrors();

        $this->context->smarty->assign([
            'tabs' => [
                'auth' => $auth_tab,
                'config' => $config_tab,
                'shop_data' => $shop_data_tab,
                'mappings' => $mappings_tab,
                'maintenance' => agcliente::renderMaintanceTab($this)
            ],
            'modules_path' => _PS_MODULE_DIR_,
            'config_warnings' => $errors['warnings'],
            'config_errors' => $errors['errors'],


            'url_services' => $this->context->link->getAdminLink('AdminAgMelhorEnvioServices'),
            'url_labels' => $this->context->link->getAdminLink('AdminAgMelhorEnvioLabels'),
            'url_tracking' => $this->context->link->getAdminLink('AdminAgMelhorEnvioLabelsTrack'),
            'url_cache' => $this->context->link->getAdminLink('AdminAgMelhorEnvioCache'),
            'url_discounts' => $this->context->link->getAdminLink('AdminAgMelhorEnvioDiscounts'),
            'url_requests' => $this->context->link->getAdminLink('AdminAgMelhorEnvioRequest'),
        ]);

        $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/configuration.js');
        $this->context->controller->addCss(_PS_MODULE_DIR_ . $this->name . '/views/css/configuration.css');

        $html = $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/admin/configuration.tpl');
        return $html;
    }

    public function createDefaultData()
    {
        AgMelhorEnvioService::installServices();
    }

    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioCommunicatorInvalidResponseBody api_key ou secret_token não foram retornados
     */
    public function initMelhorEnvioGateway()
    {
        AgMelhorEnvioGateway::setSandbox(AgMelhorEnvioConfiguration::getSandboxEnabled() == 1);
        AgMelhorEnvioGateway::setCacheConfig(AgMelhorEnvioConfiguration::getEnabledCache(), AgMelhorEnvioConfiguration::getTimeExpireCache());

        if (AgMelhorEnvioConfiguration::getSandboxEnabled() == 1) {
            AgMelhorEnvioGateway::setToken(AgMelhorEnvioConfiguration::getSandboxToken());
        } else {
            AgMelhorEnvioGateway::setToken(AgMelhorEnvioConfiguration::getToken());
        }
    }

    public function loadMappings()
    {
        $this->cpf_mapping = new AgColumnMapping();

        $this->cpf_mapping->addColumn('dni', 'address.dni');
        $this->cpf_mapping->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'agmelhorenvio_cpf_mapping'
        ));

        $this->cpf_mapping->addColumn('cpfmodulo', 'CPF Modulo por Ehinarr Solutions');
        $this->cpf_mapping->addColumn('djtalbrazilianregister', 'Módulo de Cadastro Brasileiro');
        $this->cpf_mapping->addColumn('modulocpf', 'modulocpf');
        $this->cpf_mapping->addColumn('psmodcpf', 'Módulo CPF/CNPJ por SoliSYS');


        $this->cnpj_mapping = new AgColumnMapping();
        $this->cnpj_mapping->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'agmelhorenvio_cnpj_mapping'
        ));
        $this->cnpj_mapping->addColumn('djtalbrazilianregister', 'Módulo de Cadastro Brasileiro');
        $this->cnpj_mapping->addColumn('modulocpf', 'modulocpf');
        $this->cnpj_mapping->addColumn('psmodcpf', 'Módulo CPF/CNPJ por SoliSYS');

        $this->invoice_number_mapping = new AgColumnMapping();
        $this->invoice_number_mapping->setData(array(
            'table_name' => 'orders',
            'configuration_name' => 'agmelhorenvio_invoice_number_mapping'
        ));
        $this->invoice_number_mapping->addColumn('webmania', 'Módulo Webmania');


        $this->invoice_serie_mapping = new AgColumnMapping();
        $this->invoice_serie_mapping->setData(array(
            'table_name' => 'orders',
            'configuration_name' => 'agmelhorenvio_invoice_serie_mapping'
        ));
        $this->invoice_serie_mapping->addColumn('webmania', 'Módulo Webmania');


        $this->address_number_mapping = new AgColumnMapping();
        $this->address_number_mapping->setData(array(
            'table_name' => 'address',
            'configuration_name' => 'agmelhorenvio_address_number_mapping'
        ));

        $this->address_complement_mapping = new AgColumnMapping();
        $this->address_complement_mapping->setData(array(
            'table_name' => 'address',
            'configuration_name' => 'agmelhorenvio_address_complement_mapping'
        ));

        if (Module::isEnabled('agcustomers')) {
            $this->cpf_mapping->mapsTo("cpf");
            $this->cnpj_mapping->mapsTo("cnpj");
            $this->address_number_mapping->mapsTo("number");
            $this->address_complement_mapping->mapsTo("other");
        }
    }

    public function getCpfMapping()
    {
        return $this->cpf_mapping;
    }

    public function getCnpjMapping()
    {
        return $this->cnpj_mapping;
    }

    public function getInvoiceNumberMapping()
    {
        return $this->invoice_number_mapping;
    }

    public function getInvoiceSerieMapping()
    {
        return $this->invoice_serie_mapping;
    }

    public function getAddressNumberMapping()
    {
        return $this->address_number_mapping;
    }

    public function getAddressComplementMapping()
    {
        return $this->address_complement_mapping;
    }

    public function getMappedStatus($me_state)
    {
        return Configuration::get('AGMELHORENVIO_STATUS_MAPPING_' . $me_state);
    }

    public function calcShippingCost(
        AgMelhorEnvioService $service,
        Address $from,
        Address $to,
        array $products,
        $cart_value = 0,
        $use_db_cache = true,
        $use_local_cache_variable = true
    ) {
        if (!$this->active) {
            return;
        }
        if (!$to->postcode) {
            return;
        }

        if ((Configuration::get('AGMELHORENVIO_AGENCY_JADLOG') <= 0 && strtoupper($service->carrier_name) == 'JADLOG') || (Configuration::get('AGMELHORENVIO_AGENCY_LATAM') <= 0 && strtoupper($service->carrier_name) == 'LATAM')) {
            return false;
        }

        $cache_key = self::getCacheKey($service->id_remote, $from, $to, $products);
        $to_postcode = preg_replace("/[^0-9]/", "", $to->postcode);
        if (Tools::strlen($to_postcode) != 8) {
            self::$cache[$cache_key] = false;
            return false;
        }

        $to_remote = new AgMelhorEnvioRemoteAddress();
        $to->postcode = $to_postcode;
        $to_remote->setPostalCode($to_postcode);

        $this->context->cookie->agmelhorenvio_postcode = $to->postcode;
        $from_remote = new AgMelhorEnvioRemoteAddress();
        $from_remote->setPostalCode($from->postcode);

        if (isset(self::$cache[$cache_key]) && $use_local_cache_variable) {
            return self::$cache[$cache_key];
        }

        // Convertendo cada produto na unidade definida no banco
        if (Configuration::get('PS_WEIGHT_UNIT') == 'g') {
            foreach ($products as $key => $product) {
                if ($products[$key]['weight'] > 0) {
                    $products[$key]['weight'] = $products[$key]['weight'] / 1000;
                }
            }
        }

        $options = new AgMelhorEnvioRemoteOptions();
        if ($service->own_hands) {
            $opt = new AgMelhorEnvioRemoteOption();
            $opt->setName('own_hand');
            $opt->setValue(true);

            $options->addOption($opt);
        }

        if ($service->receipt) {
            $opt = new AgMelhorEnvioRemoteOption();
            $opt->setName('receipt');
            $opt->setValue(true);

            $options->addOption($opt);
        }

        if (!$service->insurance) {
            foreach ($products as $i => $product) {
                if (isset($product['insurance_value'])) {
                    unset($products[$i]['insurance_value']);
                }
            }
        } else {
            foreach ($products as $i => $product) {
                $products[$i]['insurance_value'] = $products[$i]['price_wt'];
            }
        }

        // } elseif ($name === 'CL') {
        //     $opt->setName('collect');
        //     $opt->setValue(true);
        // }        

        try {
            $services = AgMelhorEnvioService::findBy([
                'insurance' => $service->insurance,
                'own_hands' => $service->own_hands,
                'receipt' => $service->receipt
            ]);

            $ids = [];

            foreach ($services as $_service) {
                $carrier = $_service->getCarrier();
                if (!Validate::isLoadedObject($carrier)) {
                    continue;
                }

                //verifica se o serviço está habilitado para a região atual
                if (Module::isInstalled('agzipcodezones') && Module::isEnabled('agzipcodezones')) {
                    require_once _PS_MODULE_DIR_ . 'agzipcodezones/agzipcodezones.php';
                    //instancia o módulo apenas para incluir as suas dependências
                    $instance = new agzipcodezones();


                    $interval = AgZipcodeZonesInterval::getByZipcode($to->postcode);
                    $zone = new Zone($interval->id_zone);

                    if (!Validate::isLoadedObject($zone)) {
                        $zone = new Zone($this->context->country->id_zone);
                    }
                } else {
                    $zone = new Zone($this->context->country->id_zone);
                }

                $sql = new DbQuery;
                $sql->from('carrier_zone')
                    ->where('id_carrier=' . (int)$carrier->id)
                    ->where('id_zone=' . (int)$zone->id);
                $data = Db::getInstance()->getRow($sql);
                if (!is_array($data)) {
                    continue;
                }

                $ids[] = $_service->id_remote;
            }

            $response = AgMelhorEnvioGateway::simulateShipping($from_remote, $to_remote, $options, $products, [], $ids, $use_db_cache);

            $return = [];
            foreach ($response as $price) {
                if (array_search($price->getIdService(), $ids) === false) {
                    continue;
                }

                $cache_key = self::getCacheKey($price->getIdService(), $from, $to, $products);
                self::$cache[$cache_key] = $price;

                //checar essa linha esquisita... parece ser um tratamento para o caso do melhor envio retornar um erro
                if (!is_array($response) || !count($response) || !Validate::isPrice($response[0]->getPrice())) {
                    continue;
                }

                $agmelhorenvio_service = AgMelhorEnvioService::getByIdRemote($price->getIdService());

                if (!$this->ignore_discounts) {
                    $discount = AgMelhorEnvioDiscount::getDiscountByPostcodeAndPrice($to->postcode, $cart_value, $agmelhorenvio_service->id_remote);

                    if (Validate::isLoadedObject($discount)) {
                        $shipping_cost = $discount->applyTo($price->getPrice());
                        $price->setPrice($shipping_cost);
                        self::$cache[$cache_key] = $price;
                    }
                }

                $service_obj = AgMelhorEnvioService::getByIdRemote($price->getIdService());
                $carrier = $service_obj->getCarrier();

                if (!Validate::isLoadedObject($carrier)) {
                    continue;
                }

                self::$delay[$carrier->id][] = ($price->getDeliveryTime() + (int)AgMelhorEnvioConfiguration::getHandlingtime() + (int) $service->additional_time);

        
                if ($price->getIdService() == $service->id_remote) {
                    $return = $price;
                }
                //essa linha deve ser inserida somente após o cálculo do frete via API para nos certificarmos de quais serviços
                //podem atender ao destino
                $free_shipping_price = Configuration::get('PS_SHIPPING_FREE_PRICE');

                if ($cart_value >= (float) ($free_shipping_price) && (float) ($free_shipping_price) > 0) {
                    $price->setPrice(0);
                    self::$cache[$cache_key] = $price;
                    $return = $price;
                }
            }


            //adiciona os serviços indisponíveis ao cache para que eles não sejam
            //buscados nas próximas consultas de preço
            if (empty($ids)) {
                self::$cache[$cache_key] = false;
            }

            foreach ($ids as $id) {
                $cache_key = self::getCacheKey($id, $from, $to, $products);
                if (isset(self::$cache[$cache_key])) {
                    continue;
                }

                self::$cache[$cache_key] = [];
            }

            return $return;
        } catch (Exception $e) {
            
            Logger::addLog('agmelhorenvio - Erro realizando simulação do frete - ' . $e->getMessage(), '3');

            $services = AgMelhorEnvioService::getAll();
            $ids = [];
            foreach ($services as $service) {
                $ids[] = $service->id_remote;
            }

            foreach ($ids as $id) {
                $cache_key = self::getCacheKey($id, $from, $to, $products);
                if (isset(self::$cache[$cache_key])) {
                    continue;
                }

                self::$cache[$cache_key] = [];
            }
        }
    }

    public function getOrderShippingCost($cart, $shipping_cost)
    {
        if (!$this->active) {
            return false;
        }

        $service = AgMelhorEnvioService::getByCarrier(new Carrier($this->id_carrier));
        if (!Validate::isLoadedObject($service)) {
            return false;
        }

        if (Validate::isLoadedObject($this->context->customer)) {
            $customer = $this->context->customer;
        } else {
            $customer = new Customer($cart->id_customer);
        }

        $customer_data = $this->getCustomerData($customer, new Address($cart->id_address_delivery));

        $document = @$customer_data['cnpj'] ?: @$customer_data['cpf'];
        if (!$document && $service->requireCPF()) {
            return false;
        }

        $cart_value = 0;

        $products = array();
        foreach ($cart->getProducts() as $product) {
            $id_product = $product['id_product'];
            $id_product_attribute = $product['id_product_attribute'];

            //soma o peso adicional da combinação do produto
            $attribute_weight = 0;

            if ($id_product_attribute) {
                $sql = new DbQuery;
                $sql->from('product_attribute')
                    ->select('weight')
                    ->where('id_product_attribute=' . (int)$id_product_attribute);
                $attribute_weight = Db::getInstance()->getValue($sql);
            }

            $weight =  max($product['weight'], 0.001) + $attribute_weight;
            //conversão de unidade
            if (Configuration::get('PS_WEIGHT_UNIT') == 'g') {
                $weight /= 1000;
            }

            $products[] = array(
                'id' => "{$id_product}-{$id_product_attribute}",
                'width' => max($product['width'], 0.1),
                'height' => max($product['height'], 0.1),
                'length' => max($product['depth'], 0.1),
                'weight' => $weight,
                'quantity' => $product['cart_quantity'],
                'insurance_value' => $service->insurance ? $product['price_with_reduction'] : 0
            );

            $cart_value += $product['cart_quantity'] * $product['price_with_reduction'];
        }

        try {
            $address = new Address($cart->id_address_delivery);

            $response = $this->calcShippingCost(
                $service,
                $this->getFromAddress(),
                $address,
                $products,
                $cart_value
            );

            if ($response === false) {
                return false;
            }

            $price = $response->getPrice();
            // $price += Configuration::get('PS_SHIPPING_HANDLING');
            // $price += $shipping_cost;

            return $price;
        } catch (Exception $e) {
            Logger::addLog('Erro calculando o custo do frete do carrinho ' . $cart->id . ' através do Melhor Envio - ' . $e->getMessage());
            return false;
        }
    }

    public function simulateAllCarriersForProduct($postcode, $id_product, $id_product_attribute = 0, $quantity = 1, $postcode_origin = null)
    {
        if (!$this->active) {
            return array();
        }

        $prices = array();
        $services = AgMelhorEnvioService::getAll();

        $product          = new Product($id_product);
        $product_carriers = $product->getCarriers();

        foreach ($services as $service) {
            //verifica se a transportadora mapeada ao serviço está ativa e não deletada
            $carrier = $service->getCarrier();

            if (!@$carrier->active || $carrier->deleted) {
                continue;
            }

            //verifica se o produto pode ser enviado pela transportadora do serviço
            $is_available = false;
            foreach ($product_carriers as $product_carrier) {
                if ($product_carrier['id_carrier'] == $carrier->id) {
                    $is_available = true;
                }
            }

            if (!$is_available && count($product_carriers)) {
                continue;
            }

            $this->id_carrier = $carrier->id;
            $price = $this->calcShippingCostForProduct(
                $service,
                $postcode,
                $id_product,
                $id_product_attribute,
                $quantity,
                $postcode_origin
            );
dump($price);
            if ($price === false) {
                continue;
            }


            $price += Configuration::get('PS_SHIPPING_HANDLING');

            $carrier->img = file_exists(_PS_SHIP_IMG_DIR_ . (int) $carrier->id . '.jpg') ? _THEME_SHIP_DIR_ . (int) $carrier->id . '.jpg' : '';

            if ($price > 0) {
                $price_formated = Tools::displayPrice($price);
            } else {
                $price_formated = "Frete Grátis";
            }

            $prices[] = array(
                'carrier' => $carrier,
                'price' => $price_formated,
                'delay' => self::getDelay($carrier->id)
            );
        }

        return $prices;
    }

    public function simulateAllCarriersForCart($postcode, Cart $cart, $postcode_origin = null)
    {
        if (!$this->active) {
            return array();
        }


        $services = AgMelhorEnvioService::getAll();

        //busca as transportadoras que podem postar todos os produtos
        foreach ($services as $service) {
            $carrier = new Carrier($service->id_carrier);

            //verifica se a transportadora mapeada ao serviço está ativa e não deletada
            $carrier = $service->getCarrier();

            if (!@$carrier->active || $carrier->deleted) {
                continue;
            }

            $carriers[] = $carrier->id;
        }

        $carriers = array_unique($carriers);

        foreach ($cart->getProducts() as $product) {
            $obj = new Product($product['id_product']);

            $product_carriers = [];
            foreach ($obj->getCarriers() as $product_carrier) {
                $product_carriers[] = $product_carrier['id_carrier'];
            }

            if (count($product_carriers)) {
                $carriers = array_intersect($carriers, $product_carriers);
            }
        }

        $products = array();
        $cart_value = 0;

        foreach ($cart->getProducts() as $product) {
            if ($product['is_virtual']) {
                continue;
            }

            $id_product = $product['id_product'];
            $id_product_attribute = $product['id_product_attribute'];

            $attribute_weight = 0;
            if ($id_product_attribute) {
                $attribute_weight = AgMelhorEnvioLabelProduct::GetCombinationWeight($id_product_attribute, $this->context->shop->id);
            }

            $products[] = array(
                'id' => "{$id_product}-{$id_product_attribute}",
                'width' => max($product['width'], 0.1),
                'height' => max($product['height'], 0.1),
                'length' => max($product['depth'], 0.1),
                'weight' => max($product['weight'], 0.001) + $attribute_weight,

                'quantity' => $product['cart_quantity'],
                //necessário para o cálculo do seguro separadamente por serviço
                'price_wt' => $product['price_wt']
            );

            $cart_value += $product['total_wt'];
        }

        try {
            if ($postcode_origin) {
                $address = new Address;
                $address->postcode = $postcode_origin;
            }

            $return = $this->simulateAllCarriersForProducts(
                $postcode,
                $products,
                $cart_value,
                $carriers,
                @$address
            );
            return $return;
        } catch (Exception $e) {
            Logger::addLog('Erro calculando os custos de frete para o carrinho de compras ' . $cart->id . ' através do Melhor Envio - ' . $e->getMessage());
            return array();
        }
    }

    public function simulateAllCarriersForProducts($postcode, $products, $cart_value, $carriers = [], Address $from_address = null)
    {
        if (!$this->active) {
            return array();
        }

        if (is_null($from_address)) {
            $from_address = $this->getFromAddress();
        }

        $prices = array();

        foreach ($carriers as $carrier) {
            $to = new Address();
            $to->postcode = $postcode;

            $this->id_carrier = $carrier;
            $service = AgMelhorEnvioService::getByCarrier(new Carrier($carrier));

            $response = $this->calcShippingCost(
                $service,
                $from_address,
                $to,
                $products,
                $cart_value
            );
            
            if ($response === false || is_null($response) || (is_array($response) && count($response) == 0)) {
                continue;
            }

            self::$delay[$this->id_carrier][] = ($response->getDeliveryTime() + (int)AgMelhorEnvioConfiguration::getHandlingtime() + (int) $service->additional_time);


            $carrier = new Carrier($carrier);
            $carrier->img = file_exists(_PS_SHIP_IMG_DIR_ . (int) $carrier->id . '.jpg') ? _THEME_SHIP_DIR_ . (int) $carrier->id . '.jpg' : '';

            $price = $response->getPrice();
            if ($price > 0) {
                $price += Configuration::get('PS_SHIPPING_HANDLING');
                $price_formated = Tools::displayPrice($price);
            } else {
                $price_formated = "Frete Grátis";
            }

            $prices[] = array(
                'carrier' => $carrier,
                'price' => $price_formated,
                'delay' => self::getDelay($carrier->id),
                'price_unformatted' => $price
            );
        }

        return $prices;
    }

    public function calcShippingCostForProduct($service, $postcode, $id_product, $id_product_attribute = 0, $quantity = 1, $postcode_origin = null)
    {
        $product = new Product($id_product);

        try {
            $to = new Address();
            $to->postcode = $postcode;
            if (Module::isEnabled('cdcombinationdimensions') && file_exists(_PS_MODULE_DIR_ . 'cdcombinationdimensions/cdcombinationdimensions.php')) {
                require_once _PS_MODULE_DIR_ . 'cdcombinationdimensions/classes/CdOverrideHelper.php';

                $product = CdOverrideHelper::getAvailableCarrierListCarrier($product, $id_product_attribute);

                $height = $product->height;
                $length = $product->depth;
                $width = $product->width;
            } elseif ($id_product_attribute && file_exists(_PS_MODULE_DIR_ . 'combinationdimensions/combinationdimensions.php')) {
                require_once _PS_MODULE_DIR_ . 'combinationdimensions/classes/CombinationDimension.php';

                $dimensions = CombinationDimension::getAttributeDimensions($id_product_attribute);

                $height = $dimensions[$id_product_attribute]['height'];
                $length = $dimensions[$id_product_attribute]['depth'];
                $width = $dimensions[$id_product_attribute]['width'];
            } else {
                $height = $product->height;
                $length = $product->depth;
                $width = $product->width;
            }

            $attribute_weight = 0;
            if ($id_product_attribute) {
                $attribute_weight = AgMelhorEnvioLabelProduct::GetCombinationWeight($id_product_attribute, $this->context->shop->id);
            }

            $products = [
                [
                    'id' => "{$id_product}-{$id_product_attribute}",
                    'weight' => max($product->weight, 0.001) + $attribute_weight,
                    'width' => max($width, 0.1),
                    'height' => max($height, 0.1),
                    'length' => max($length, 0.1),
                    'quantity' => $quantity,
                    'insurance_value' => $service->insurance ? Product::getPriceStatic($id_product, true, $id_product_attribute) : 0
                ]
            ];

            $cart_value = $quantity * Product::getPriceStatic($id_product, true, $id_product_attribute);

            if ($postcode_origin && $postcode_origin != -1) {
                $address = new Address;
                $address->postcode = $postcode_origin;
            } else {
                $address = $this->getFromAddress();
            }

            $response = $this->calcShippingCost(
                $service,
                $address,
                $to,
                $products,
                $cart_value
            );

            if (!$response) {
                return false;
            }

            self::$delay[$this->id_carrier][] = ($response->getDeliveryTime() + (int)AgMelhorEnvioConfiguration::getHandlingtime() + (int) $service->additional_time);
            return $response->getPrice();
        } catch (Exception $e) {
            Logger::addLog('Erro calculando os custos de frete para o produto ' . $id_product . ' (combinaçao ' . $id_product_attribute . ') através do Melhor Envio - ' . $e->getMessage(), true);
            return false;
        }
    }

    public static function getDelay($id_carrier)
    {
        if (isset(self::$delay[$id_carrier])) {

            //verifica se o recurso novo de formatar o prazo de entrega está disponível
            require_once _PS_MODULE_DIR_ . 'agcliente/agcliente.php';
            $agcliente = new agcliente;
            if (version_compare($agcliente->version, '1.13.0', '>=')) {
                $formatter = DeliveryTimeFormatterFactory::createFormatter(Configuration::get('AGTI_SIMULATION_DELIVERY_DATE_MODE'));
                return $formatter->format(max(self::$delay[$id_carrier]), Configuration::get('AGTI_SIMULATION_DELIVERY_DATE_CUSTOM_FORMAT'));
            } else {
                return max(self::$delay[$id_carrier]) . ' dias úteis.';
            }
        }

        return false;
    }

    public function addLabelToCart($id_agmelhorenvio_label)
    {
        $instance = new AgMelhorEnvioLabel($id_agmelhorenvio_label);
        if ($instance->status !== AgMelhorEnvioLabelsStatusesEnum::TO_BE_GENERATED) {
            throw new Exception("A etiqueta $id_agmelhorenvio_label já foi solicitada junto ao Melhor Envio.");
        }

        $from_remote = $this->getFromRemoteAddress($instance);
        $from_remote->setPostalCode($instance->zipcode_origin);

        $to_remote = $instance->getToRemote();

        if (strlen($to_remote->getAddress()) > 64) {
            throw new Exception("Erro validando a etiqueta {$id_agmelhorenvio_label}. O endereço de entrega é muito grande, o Melhor Envio não aceita endereços com mais de 64 caracteres.");
        }

        $service = $instance->getService();

        if ((int) $service->id_remote === 12 && $from_remote->getDocument() && !$from_remote->getCnae()) {
            throw new Exception('O campo CNAE é obrigatório para envios com CNPJ na transportadora LaTAM Cargo. Preencha em "Dados do Remetente".');
        }

        if ($service->carrier_name != 'Correios' && (int) $service->insurance === 0) {
            throw new Exception("É necessário ativar o valor declarado(VD) no serviço para gerar essa etiqueta, acesse a aba de serviços do Melhor Envio e caso a opção VD do serviço solicitado ainda esteja desativada, clique no icone x em vermelho e pronto.");
        }

        $order = new Order($instance->id_order);
        $cart = Cart::getCartByOrderId($instance->id_order);

        $id_br = Country::getByIso('br');

        if (strpos(strtoupper(AddressFormat::getAddressCountryFormat($id_br)), 'STATE') === false) {
            if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||  isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }

            $current_link = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $current_link = explode('index.php', $current_link)[0];

            if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
                $redirect_url = $this->context->link->getAdminLink('AdminCountries') . '&updatecountry=&id_country=' . $id_br;
            } else {
                $redirect_url = $current_link . $this->context->link->getAdminLink('AdminCountries') . '&updatecountry=&id_country=' . $id_br;
            }

            $msg_error = "É necessário adicionar um campo da aba STATE no layout do endereço, para fazer isso <a href='{$redirect_url}' target='_blank'>clique aqui</a>, acesse a aba state e adicione alguma das opções";

            $this->context->controller->errors[] = $msg_error;

            return;;
        }
        

        try {
            $dt = $instance->getDataToMelhorEnvio($this);

            //constrói os itens a serem adicionados no carrinho de compras do MelhorEnvio, um para cada pacote
            $return = [];
            $products_to_package = AgMelhorEnvioLabelProduct::getFromLabel($id_agmelhorenvio_label);

            for ($i = 0; $i < count($products_to_package); $i++) {
                //busca os demais dados do produto (valor do seguro e nome)
                if ($service->insurance) {
                    $products_to_package[$i]['insurance_value'] = round($products_to_package[$i]['unitary_value'] * $products_to_package[$i]['quantity'], 2);
                }
            }

            $cart_item = new AgMelhorEnvioRemoteCartItem;
            $cart_item->setService($service->id_remote)
                ->setFrom($from_remote)
                ->setTo($to_remote)
                ->setProducts($products_to_package)
                ->addPackage($instance->getRemotePackage());


            //verifica se a agencia deve ser obtida da configuração padrão ou do produto
            $is_total_express = stripos((string) $service->carrier_name, 'TOTAL EXPRESS') !== false;
            if (!$instance->zipcode_origin || str_replace('-','',$instance->zipcode_origin) == str_replace('-','',AgMelhorEnvioConfiguration::getShopAddressZipcode())) {
                if ($service->id_remote == 3 || $service->id_remote == 4) {
                    $cart_item->setAgency(AgMelhorEnvioConfiguration::getAgencyJadlog());
                } elseif ($service->id_remote == 12) {
                    $cart_item->setAgency(AgMelhorEnvioConfiguration::getAgencyLatam());
                } elseif ($is_total_express) {
                    $cart_item->setAgency(AgMelhorEnvioConfiguration::getAgencyTotalExpress());
                }
            } else {
                $sellerData = AgMelhorEnvioSellerDataZipcode::getByZipcode($instance->zipcode_origin);

                if ($service->id_remote == 3 || $service->id_remote == 4) {
                    $cart_item->setAgency($sellerData->agency_jadlog);
                } elseif ($service->id_remote == 12) {
                    $cart_item->setAgency($sellerData->agency_latam);
                } elseif ($is_total_express) {
                    // Atualmente só existe configuração global para Total Express
                    $cart_item->setAgency(AgMelhorEnvioConfiguration::getAgencyTotalExpress());
                }
            }

            //opcionais do item
            $options = new AgMelhorEnvioRemoteOptions;
            foreach ($dt->getAdditionalServices() as $option) {
                $options->addOption($option);
            }

            if ($this->getInvoiceNumberMapping()->isMappingEnabled()) {

                $invoice = new stdClass;

                $sql = new DbQuery;
                $sql->from('orders')
                    ->where('id_order=' . (int)$order->id);

                $db_data = Db::getInstance()->getRow($sql);

                if ($this->getInvoiceNumberMapping()->getMappedField() === 'webmania') {
                    $nfe_info = unserialize($db_data['nfe_info']);
                    $invoice->number = $nfe_info[0]['n_nfe'];
                } else {
                    $invoice->number = @$db_data[$this->getInvoiceNumberMapping()->getMappedfield()];
                }

                if ($this->getInvoiceSerieMapping()->getMappedField() === 'webmania') {
                    $nfe_info = unserialize($db_data['nfe_info']);
                    $invoice->key = $nfe_info[0]['chave_acesso'];
                } else {
                    $invoice->key = @$db_data[$this->getInvoiceSerieMapping()->getMappedfield()];
                }

                if ($invoice->number && $invoice->key) {
                    $option = new AgMelhorEnvioRemoteOption;
                    $option->setName('invoice');
                    $option->setValue($invoice);

                    $options->addOption($option);
                }

                if ($invoice->number && $invoice->key) {
                    $option = new AgMelhorEnvioRemoteOption;
                    $option->setName('non_commercial');
                    $option->setValue(false);

                    $options->addOption($option);
                } else {
                    $option = new AgMelhorEnvioRemoteOption;
                    $option->setName('non_commercial');
                    $option->setValue(true);

                    $options->addOption($option);
                }
            } else {
                $option = new AgMelhorEnvioRemoteOption;
                $option->setName('non_commercial');
                $option->setValue(true);

                $options->addOption($option);
            }

            $cart_item->setOptions($options);

            //AQUI
            try {
                $response = AgMelhorEnvioGateway::addShippingToCart($cart_item);
                $instance->id_order_remote = $response->getId();
                $instance->protocol = $response->getProtocol();
                $instance->discount = $response->getDiscount();
                $instance->delivery_time = $response->getDeliveryTime();
                $instance->price = $response->getPrice();
                $instance->status = $response->getStatus();
                $instance->insurance_value = $response->getInsuranceValue();

                $instance->format = $response->getFormat();
                $instance->created_at = $response->getCreatedAt();
                $instance->updated_at = $response->getUpdatedAt();

                if (!$instance->save()) {
                    $msg_error = "Erro salvando a etiqueta no banco de dados.";
                    $msg_error .= Db::getInstance()->getMsgError();
                    throw new Exception($msg_error);
                }

                $return[] = $instance;
                return $return;
            } catch (Exception $e) {
                $msg_error = "Erro adicionando etiqueta ao carrinho de compras - {$e->getMessage()}  ({$e->getFile()}-{$e->getLine()})";
                throw new Exception($msg_error);
            }
        } catch (Exception $e) {
            $msg_error = "Erro adicionando etiqueta ao carrinho de compras - {$e->getMessage()}  ({$e->getFile()}-{$e->getLine()})";
            throw new Exception($msg_error);
        }

        return $return;
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $service = AgMelhorEnvioService::getByCarrier(new Carrier($order->id_carrier));

        //se a compra não foi feita com uma transportadora utilizada pelo módulo, ela é ignorada
        if (!Validate::isLoadedObject($service)) {
            return false;
        }

        AgMelhorEnvioLabel::generateLabelsForOrder($order);
        return;
    }

    public function hookDashboardZoneTwo()
    {
    }

    public function hookActionOrderStatusUpdate($param)
    {
        //evita loop com essa função sendo chamada duas vezes para o mesmo pedido
        static $orders = [];

        if (isset($orders[$param['id_order']])) {
            return;
        }

        $orders[$param['id_order']] = true;
        if (!AgMelhorEnvioConfiguration::getAutoGenerateLabels() || AgMelhorEnvioConfiguration::getPaymentMode() != 0) {
            return;
        }

        $configuredStatusIds = array_map('intval', AgMelhorEnvioConfiguration::getAutoGenerateLabelStates());
        $newStatusId = isset($param['newOrderStatus']->id) ? (int) $param['newOrderStatus']->id : 0;
        if ($newStatusId <= 0 || !in_array($newStatusId, $configuredStatusIds)) {
            return;
        }

        try {
            $id_br = Country::getByIso('br');
            if (strpos(strtoupper(AddressFormat::getAddressCountryFormat($id_br)), 'STATE') === false) {
                Logger::addLog('agmelhorenvio - não foi possível gerar a etiqueta porque está faltando adicionar o nome do estado no formulário do endereço' . $param['id_order'], 3);
            }

            $labels = AgMelhorEnvioLabel::getByIdOrder($param["id_order"]);
            //se o pedido não possui nenhuma etiqueta do Melhor Envio, não gera uma nova.
            //as etiquetas são geradas no método validateOrder
            if (count($labels) == 0) {
                return;;
            }

            //utiliza sempre a etiqueta mais recente
            $label = array_reverse($labels)[0];

            $this->addLabelToCart($label['id_agmelhorenvio_label']);

            $obj = new AgMelhorEnvioLabel($label['id_agmelhorenvio_label']);

            if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||  isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }

            $current_link = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $current_link = explode('index.php', $current_link)[0];

            $redirect_url = $current_link . $this->context->link->getAdminLink('AdminAgMelhorEnvioLabels') . '&returnGateway&id_agmelhorenvio_label=' . $obj->id;

            $response = AgMelhorEnvioGateway::buyLabels([$obj->id_order_remote], 0, $redirect_url);
            
            if ($response === true) {
                $obj->status = 'released';
                $obj->update();
            }
        } catch (Exception $e) {
            Logger::addLog('agmelhorenvio - Erro adicionando etiqueta ao carrinho de compras para o pedido ' . $param['id_order'] . ' - ' . $e->getMessage(), 3);
        }
    }


    protected static function getCacheKey(
        $service_id,
        Address $from,
        Address $to,
        $products
    ) {
        
        $ids = [];
        $quantities = [];
        foreach ($products as $product) {
            $ids[] = @$product['id'] ?: $product['id_product'] . '-' . $product['id_product_attribute'];
            $quantities[] = $product['quantity'];
        }

        $keys = [
            implode(';', $ids),
            implode(';', $quantities),
            preg_replace("/[^0-9]/", "", $from->postcode),
            preg_replace("/[^0-9]/", "", $to->postcode),
            $service_id,
        ];

        return 'agmelhornenvio_cache_' . implode('_', $keys);
    }

    public function getFromAddress()
    {
        $address = new Address();
        $address->company = AgMelhorEnvioConfiguration::getShopName();
        $address->address = AgMelhorEnvioConfiguration::getShopAddress();
        $address->number = AgMelhorEnvioConfiguration::getShopAddressNumber();
        $address->city = AgMelhorEnvioConfiguration::getShopAddresscity();
        $address->state = AgMelhorEnvioConfiguration::getShopAddressState();
        $address->postcode = preg_replace("/[^0-9]/", "", AgMelhorEnvioConfiguration::getShopAddressZipcode());
        $address->phone = AgMelhorEnvioConfiguration::getShopAddressPhone();
        $address->district = AgMelhorEnvioConfiguration::getShopAddressDistrict();

        return $address;
    }

    public function getFromRemoteAddress(AgMelhorEnvioLabel $label = null)
    {
        $from = $this->getFromAddress();

        $from_remote = new AgMelhorEnvioRemoteAddress();

         //a etiqueta será postada para o endereço padrão do módulo
         if ($from->postcode == $label->zipcode_origin || !$label->zipcode_origin) {
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
                ->setCnae(AgMelhorEnvioConfiguration::getCnae())
                ->setStateRegister(AgMelhorEnvioConfiguration::getStateRegister());
        } else {
            $sellerData = AgMelhorEnvioSellerDataZipcode::getByZipcode($label->zipcode_origin);

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
                ->setCnae(AgMelhorEnvioConfiguration::getCnae())
                ->setStateRegister($sellerData->state_register);
        }

        return $from_remote;
    }

    public function getCustomerData(Customer $customer, Address $address)
    {
        $cpf = '';
        $cnpj = '';

        $cpf_mapping  = $this->getCpfMapping();
        $cnpj_mapping = $this->getCnpjMapping();

        if ($cpf_mapping->getMappedField() === 'djtalbrazilianregister') {
            $sql = new DbQuery;
            $sql->from('djtalbrazilianregister')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);

            $cpf = @$data['cpf'];
        } elseif ($cpf_mapping->getMappedfield() === 'dni') {
            $sql = new DbQuery;
            $sql->from('address')
                ->select('dni')
                ->where('id_address=' . (int)$address->id);

            $cpf = Db::getInstance()->getValue($sql);
        } elseif ($cpf_mapping->getMappedField() == 'cpfmodulo') {
            $sql = new DbQuery;
            $sql->from('cpfmodule_data')
                ->select('doc')
                ->where('id_customer=' . (int)$customer->id);

            $cpf = Db::getInstance()->getValue($sql);
        } elseif ($cpf_mapping->getMappedField() == 'psmodcpf') {
            $sql = new DbQuery;
            $sql->from('modulo_cpf')
                ->select('documento')
                ->where('id_customer=' . (int)$customer->id)
                ->where('tp_documento="1"');

            $cpf = Db::getInstance()->getValue($sql);
        } elseif ($cpf_mapping->getMappedField() == 'modulocpf') {
            $sql = new DbQuery;
            $sql->from('modulo_cpf')
                ->select('nu_cpf_cnpj')
                ->where('ps_customer_id_customer=' . (int)$customer->id)
                ->where('doc_type="2"');

            $cpf = Db::getInstance()->getValue($sql);
        } elseif ($cpf_mapping->isMappingEnabled()) {
            $sql = new DbQuery;
            $sql->from('customer')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);

            $cpf = @$data[$cpf_mapping->getMappedField()];
        }


        //mapeamento de CNPJ
        if ($cnpj_mapping->getMappedField() == 'modulocpf') {
            $sql = new DbQuery;
            $sql->from('modulo_cpf')
                ->select('nu_cpf_cnpj')
                ->where('ps_customer_id_customer=' . (int)$customer->id)
                ->where('doc_type="1"');

            $cnpj = Db::getInstance()->getValue($sql);
        }elseif ($cpf_mapping->getMappedField() == 'psmodcpf') {
            $sql = new DbQuery;
            $sql->from('modulo_cpf')
                ->select('documento')
                ->where('id_customer=' . (int)$customer->id)
                ->where('tp_documento="2"');
            $cnpj = Db::getInstance()->getValue($sql);
        } elseif ($cnpj_mapping->isMappingEnabled()) {
            $sql = new DbQuery;
            $sql->from('customer')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);

            $cnpj = @$data[$cnpj_mapping->getMappedField()];
        }

        //se o CPF for igual ao CNPJ então tenta descobrir qual dos dois dados está realmente sendo usado
        if ($cpf == $cnpj) {
            $digits  = preg_replace('/[^0-9]+/', '', $cpf);
            //o documento é um CPF. Anula o CNPJ.
            if (Tools::strlen($digits) == 11) {
                $cnpj = '';
            } else {
                $cpf = '';
            }
        }

        return [
            'cpf' => @$cpf,
            'cnpj' => @$cnpj,
        ];
    }

    public function getPackageShippingCost($cart, $shipping_cost, $products)
    {
        $address = new Address($cart->id_address_delivery);

        $cart_value = 0;

        $service = AgMelhorEnvioService::getByCarrier(new Carrier($this->id_carrier));
        foreach ($products as $product) {
            $cart_value += $product['total_wt'];
        }

        if (isset($cart->postcode_origin) && $cart->postcode_origin != '-1') {
            $from_address = new Address;
            $from_address->postcode = $cart->postcode_origin;

            if (!$from_address->postcode) {
                return false;
            }
        } elseif (Module::isEnabled('agmarketplace')) {
            require_once _PS_MODULE_DIR_ . 'agmarketplace/classes/AgMarketplaceProduct.php';
            //verifica se carrinho produto pertence a um seller do marketplace ou é do admin
            $is_seller = false;
            foreach ($products as $product) {
                $seller = (new AgMarketplaceProduct)->findSellerByPsProduct(new Product($product['id_product']));
                if (Validate::isLoadedObject($seller)) {
                    $is_seller = true;
                    break;
                }
            }

            if ($is_seller) {
                return false;
            }

            $cart->postcode_origin = preg_replace("/[^0-9]/", "", AgMelhorEnvioConfiguration::getShopAddressZipcode());
        }
        

        foreach ($products as $i => $product) {
            $products[$i]['id'] = $product['id_product'] . '-' . $product['id_product_attribute'];

            $id_product_attribute = $product['id_product_attribute'];

            $attribute_weight = 0;
            if ($id_product_attribute) {
                $attribute_weight = AgMelhorEnvioLabelProduct::GetCombinationWeight($id_product_attribute, $this->context->shop->id);
            }

            $products[$i]['weight'] = max($product['weight'], 0.001) ;
        }
        $return = $this->simulateAllCarriersForProducts(
            $address->postcode,
            $products,
            $cart_value,
            [$this->id_carrier],
            @$from_address
        );
        
        
        if (!count($return)) {
            return false;
        }

        $price = $return[0]['price_unformatted'];
        if ($price) {
            if ($service->additional_cost) {
                if ($service->additional_cost_type == 0) {
                    $price *= (1 + $service->additional_cost / 100);
                } else {
                    $price += $service->additional_cost;
                }
            }
            // $price += Configuration::get('PS_SHIPPING_HANDLING');
            // $price += $shipping_cost;
        }

        return $price;
    }


    //*************************** HOOKS ******************************/
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('action') == 'agmelhorenvio_save_hook_extra_product') {
            ob_end_flush();

            AgClienteProductZipCode::setZipcodeToProduct(Tools::getValue('id_product'), Tools::getValue('zipcode'));
            $obj = AgMelhorEnvioSellerDataZipcode::getByZipcode(Tools::getValue('zipcode'));
            
            $obj->zipcode = Tools::getValue('zipcode');
            $obj->seller_name = Tools::getValue('seller_name');
            $obj->address = Tools::getValue('address');
            $obj->shop_name = Tools::getValue('shop_name');
            $obj->number = Tools::getValue('number');
            $obj->district = Tools::getValue('district');
            $obj->city = Tools::getValue('city');
            $obj->uf = Tools::getValue('uf');
            $obj->phone = Tools::getValue('phone');
            $obj->cnpj = Tools::getValue('cnpj');
            $obj->state_register = Tools::getValue('state_register');
            $obj->agency_jadlog = Tools::getValue('agency_jadlog');
            $obj->agency_latam = Tools::getValue('agency_latam');

            $obj->save();
            
            echo json_encode([
                'success' => true
            ]);
            exit();
        } elseif (Tools::getValue('action') == 'agmelhorenvio_search_address') {
            ob_end_flush();
            $postcode = Tools::getValue('zipcode');

            try {
                $obj = AgMelhorEnvioSellerDataZipcode::getByZipcode($postcode);
                if (!Validate::isLoadedObject($obj)) {
                    $address = AddressFinder::findByPostcode(preg_replace("/[^0-9]/", '', $postcode));
                    echo json_encode([
                        'success' => true,
                        'type' => 'address',
                        'data' => $address
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'type' => 'seller',
                        'data' => $obj
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
                exit();
            }

            exit();
        }

        $this->prepareNotifications();

        $controller = $this->context->controller;
        // addJquery is not available / not needed in PS9; only call for older PS versions
        if (!defined('_PS_VERSION_') || version_compare(_PS_VERSION_, '9.0.0', '<')) {
            $controller->addJquery();
        }

        $return = '<script type="text/javascript">';

        if ($controller->controller_name === 'AdminOrders' && Tools::getIsSet('vieworder')) {
            // if ($controller->controller_name === 'AdminOrders') {
            $order = new Order(Tools::getValue('id_order'));
            $controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/admin_orders_view.js');

            $return .= "var agmelhorenvio_token='" . Tools::getAdminTokenLite('AdminAgMelhorEnvioLabels') . "';";

            $sql = new DbQuery;
            $sql->from('orders')->where('id_order=' . (int)Tools::getValue('id_order'));
            $invoice_data = Db::getInstance()->getRow($sql);

            if ($this->getInvoiceNumberMapping()->isMappingEnabled()) {
                $return .= "var agmelhorenvio_invoice_number='" . $invoice_data[$this->getInvoiceNumberMapping()->getMappedField()] . "';";
            } else {
                $return .= "var agmelhorenvio_invoice_number='';";
            }

            if ($this->getInvoiceSerieMapping()->isMappingEnabled()) {
                $return .= "var agmelhorenvio_invoice_serie='" . $invoice_data[$this->getInvoiceSerieMapping()->getMappedField()]  . "';";
            } else {
                $return .= "var agmelhorenvio_invoice_serie='';";
            }

            if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
                $controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/admin_orders_list.1.7.7.js');
                $controller->addCss(_PS_MODULE_DIR_ . $this->name . '/views/css/admin_orders_list.css');
            } else {
                $controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/admin_orders_list.js');
            }

            $return .= "var agmelhorenvio_token='" . Tools::getAdminTokenLite('AdminAgMelhorEnvioLabels') . "';";
            $this->context->controller->page_header_toolbar_btn['melhorenvio_label'] = array(
                'href' => '#',
                'desc' => 'Criar Etiqueta do Melhor Envio',
                'icon' => 'process-icon- icon-truck'
            );
        } elseif ($controller->controller_name === 'AdminOrders') {
            if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
                $controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/admin_orders_list.1.7.7.js');
                $controller->addCss(_PS_MODULE_DIR_ . $this->name . '/views/css/admin_orders_list.css');
            } else {
                $controller->addJs(_PS_MODULE_DIR_ . $this->name . '/views/js/admin_orders_list.js');
            }

            $return .= "var agmelhorenvio_token='" . Tools::getAdminTokenLite('AdminAgMelhorEnvioLabels') . "';";
        }

        $this->context->controller->addJs([
            $this->_path . 'views/js/admin_products_extra.js'
        ]);

        $return .= '</script>';
        return $return;
    }

    public function hookActionAdminOrdersListingResultsModifier()
    {
        $this->context->controller->addRowAction('createMelhorEnvioLabel');
    }

    public function hookDisplayOrderPreview($params)
    {
        $this->context->smarty->assign(['id_order' => $params['order_id']]);
        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/hook/display_order_preview.tpl');
    }

    public function hookActionGetAdminOrderButtons($params)
    {
        $bar = $params['actions_bar_buttons_collection'];

        $bar->add(
            new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                'agmelhorenvio-generate-label btn-action',
                ['data-id-order' => $params['id_order']],
                'Atualizar dados da etiqueta'
            )
        );
    }

    public function hookDisplayAdminOrderTabLink($params)
    {
        $invoice_number_mapping = $this->getInvoiceNumberMapping();
        $invoice_serie_mapping = $this->getInvoiceSerieMapping();

        if ($invoice_number_mapping->getMappedfield() != 'agmelhorenvio_invoice_number' || $invoice_serie_mapping->getMappedfield() != 'agmelhorenvio_invoice_serie') {
            return;
        }

        $this->context->smarty->assign(['id_order' => $params['id_order']]);
        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/admin/orders/preview/tabs_fields.tpl');
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $invoice_number_mapping = $this->getInvoiceNumberMapping();
        $invoice_serie_mapping = $this->getInvoiceSerieMapping();

        $invoice_number = '';
        $invoice_serie = '';

        if ($invoice_number_mapping->getMappedfield() != 'agmelhorenvio_invoice_number' || $invoice_serie_mapping->getMappedfield() != 'agmelhorenvio_invoice_serie') {
            return;
        }

        if (Tools::isSubmit('agmelhorenvio-invoices')) {
            $id_order = Tools::getValue('id_order');

            if (!$invoice_number_mapping->isMappingEnabled()) {
                throw new Exception("O mapeamento do número da nota fiscal não está configurado.");
            }

            $invoice_serie_mapping = $this->getInvoiceSerieMapping();
            if (!$invoice_serie_mapping->isMappingEnabled()) {
                throw new Exception("O mapeamento da série da nota fiscal não está configurado.");
            }

            $update_data = [];

            $update_data[$invoice_number_mapping->getMappedfield()] = Tools::getValue('agmelhorenvio_invoice_number');
            $update_data[$invoice_serie_mapping->getMappedfield()] = Tools::getValue('agmelhorenvio_invoice_serie');

            $r = Db::getInstance()->update('orders', $update_data, 'id_order=' . (int)$id_order);
            if (!$r) {
                $msg_error = Db::getInstance()->getMsgError();

                Logger::addLog("agmelhorenvio - Erro atualizando dados do pedido {$id_order} no banco de dados - $msg_error");
                throw new Exception("Erro atualizando dados do pedido no banco de dados.");
            }

            $this->module->confirmations[] = "Informações de nota fiscal atualizadas com sucesso!";
            echo json_encode([
                'success' => true
            ]);
        }

        $sql = new DbQuery;
        $sql->from('orders')->where('id_order=' . (int)Tools::getValue('id_order'));
        $invoice_data = Db::getInstance()->getRow($sql);

        if ($this->getInvoiceNumberMapping()->isMappingEnabled()) {
            $invoice_number = $invoice_data[$this->getInvoiceNumberMapping()->getMappedField()];
        }

        if ($this->getInvoiceSerieMapping()->isMappingEnabled()) {
            $invoice_serie = $invoice_data[$this->getInvoiceSerieMapping()->getMappedField()];
        }

        $this->context->smarty->assign(
            [
                // 'url' => $this->context->link,
                'id_order' => $params['id_order'],
                'agmelhorenvio_invoice_number' => $invoice_number,
                'agmelhorenvio_invoice_serie' => $invoice_serie,
            ]
        );
        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/admin/orders/preview/content_fields.tpl');
    }

    public function hookDisplayAdminProductsExtra()
    {
        try {
            if(version_compare(_PS_VERSION_, '1.7', '>=')){
                global $kernel;
    
                $requestStack = $kernel->getContainer()->get('request_stack');
                $request      = $requestStack->getCurrentRequest();
                $id_product   = $request->get('id');
            }else {
                $id_product   = Tools::getValue('id_product');
            }
    
            $zipcode = AgClienteProductZipCode::getZipcodeByProduct($id_product);
            $obj = AgMelhorEnvioSellerDataZipcode::getByZipcode($zipcode);

            $agencies = AgMelhorEnvioGateway::getAgencies();
            usort($agencies, function ($a1, $a2) {
                $address1 = $a1->getAddress();
                $address2 = $a2->getAddress();

                if ($address1->getUf() == '' && $address2->getUf() != '') {
                    return 1;
                }

                if ($address2->getUf() == '' && $address1->getUf() != '') {
                    return -1;
                }

                if ($address1->getUf() == '') {
                    return strcmp($a1->getName(), $a2->getName());
                }

                if ($address1->getUf() != $address2->getUf()) {
                    return strcmp($address1->getUf(), $address2->getUf());
                }

                return strcmp($address1->getCity(), $address2->getCity());
            });


            $agencies_jadlog[] = [
                'id' => 0,
                'text' => 'SELECIONE A AGÊNCIA'
            ];

            $agencies_latam[] = [
                'id' => 0,
                'text' => 'SELECIONE A AGÊNCIA'
            ];

            foreach ($agencies as $agency) {
                $agency_text = '';
                if ($agency->getAddress()->getUf() != '') {
                    $agency_text .= $agency->getAddress()->getUf() . ' - ';
                }

                if ($agency->getAddress()->getCity() != '') {
                    $agency_text .= $agency->getAddress()->getCity() . ' - ';
                }

                if ($agency->getCompanyName()) {
                    $agency_text .= $agency->getCompanyName() . ' - ';
                } else {
                    $agency_text .= $agency->getName() . ' - ';
                }

                if ($agency->getAddress()->getAddress() != '') {
                    $agency_text .= $agency->getAddress()->getAddress();
                }

                //verifica se é uma ag. jadlog
                foreach ($agency->getCompanies() as $company) {
                    if ($company->getId() == 2) {
                        $agencies_jadlog[] = [
                            'id' => $agency->getId(),
                            'text' => $agency_text
                        ];
                    }

                    if ($company->getId() == 6) {
                        $agencies_latam[] = [
                            'id' => $agency->getId(),
                            'text' => $agency_text
                        ];
                    }
                }
            }
                
            $this->context->smarty->assign([
                    'zipcode' => $zipcode,
                    'id_product' => $id_product,
                    'zipcode' => $obj->zipcode,
                    'shop_name' => $obj->shop_name,
                    'address' => $obj->address,
                    'number' => $obj->number,
                    'district' => $obj->district,
                    'city' => $obj->city,
                    'uf' => $obj->uf,
                    'phone' => $obj->phone,
                    'cnpj' => $obj->cnpj,
                    'state_register' => $obj->state_register,
                    'agency_jadlog' => $obj->agency_jadlog,
                    'agency_latam' => $obj->agency_latam,
                    'agencies_jadlog' => $agencies_jadlog,
                    'agencies_latam' => $agencies_latam
            ]);
            return $this->display(_PS_MODULE_DIR_ . $this->name, 'admin_tab_products_extra.tpl');
        } catch (Exception $e){}
    }
    
}
