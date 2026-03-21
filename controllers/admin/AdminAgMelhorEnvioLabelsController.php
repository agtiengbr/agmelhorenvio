<?php
class AdminAgMelhorEnvioLabelsController extends ModuleAdminController
{
    protected $statuses_array = array();

    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'agmelhorenvio_label';
        $this->identifier = 'id_agmelhorenvio_label';
        $this->className  = 'AgMelhorEnvioLabel';
        $this->noLink = true;
        $this->list_no_link = true;

        $this->_orderBy = 'order_date';
        $this->_orderWay = 'DESC';

        parent::__construct();

        if (Tools::getIsSet('returnGateway')) {
            $this->handleReturnGateway();            
        }

        if (Tools::getIsSet($this->identifier)) {
            $this->loadObject();
        }

        //@todo mensagens de erro não estão sendo exibidas
        $this->module->prepareNotifications();

        $this->_join = 'INNER JOIN ' . _DB_PREFIX_ . 'agmelhorenvio_service s ON s.id_remote=a.service_id';
        $this->_select .= 'CONCAT(carrier_name, " ", service_name) as service_name,';
        $this->_select .= 'CONCAT(width, "x", length, "x", height, "x", weight) as dimensions,';

        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON a.id_order=o.id_order';
        $this->_select .= 'o.date_add as order_date,';
        
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = o.`current_state`)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
        $this->_select .= '
            osl.`name` AS `osname`,
            os.`color`';

    $this->_where = ' AND status IN ("pending", "released", "to_be_generated", "to_be_shipped", "printed")';


        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = [
            'id_agmelhorenvio_label' => [
                'title' => 'ID',
                'class' => 'center fixed-width-xs'
            ],
            'id_order' => [
                'title' => 'Pedido',
                'class' => 'center fixed-width-xs',
                'filter_key' => 'o!id_order'
            ],
            'zipcode_origin' => [
                'title' => 'CEP Origem',
                'class' => 'center fixed-width-xs',
            ],
            'service_name' => [
                'title' => 'Serviço',
                'havingFilter' => true
            ],
            'osname' => [
                'title' => 'Status do Pedido',
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ],
            'dimensions' => [
                'title' => 'Dimensões',
                'hint' => 'Largura x Comprimento x Altura x Peso. As dimensões só serão obtidas após a utilização do botão de Gerar Etiqueta.',
                'search' => false
            ],
            'price' => [
                'title' => 'Valor da Etiqueta',
                'type' => 'price',
                'class' => 'center fixed-width-md',
                'filter_key' => 'a!price'
            ],
            'discount' => [
                'title' => 'Desconto',
                'type' => 'price',
                'class' => 'center fixed-width-md'
            ],
            'status' => [
                'title' => 'Status da Etiqueta',
                'type' => 'select',
                // 'color' => 'color',
                'list' => AgMelhorEnvioLabelsStatusesEnum::status_names,
                'filter_key' => 'a!status',

            ],
            'order_date' => [
                'title' => 'Data do Pedido',
                'type' => 'datetime',
                'havingFilter' => true
            ],
        ];

        $disabled = Validate::isLoadedObject($this->object) && $this->object->status != AgMelhorEnvioLabelsStatusesEnum::TO_BE_GENERATED;

        $this->fields_form = [
            'legend' => [
                'title' => 'Etiqueta'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'name' => 'width',
                    'label' => 'Largura',
                    'col' => 1,
                    'suffix' => 'cm',
                    'disabled' => $disabled
                ],
                [
                    'type' => 'text',
                    'name' => 'length',
                    'label' => 'Comprimento',
                    'col' => 1,
                    'suffix' => 'cm',
                    'disabled' => $disabled
                ],
                [
                    'type' => 'text',
                    'name' => 'height',
                    'label' => 'Altura',
                    'col' => 1,
                    'suffix' => 'cm',
                    'disabled' => $disabled
                ],
                [
                    'type' => 'text',
                    'name' => 'weight',
                    'label' => 'Peso',
                    'col' => 1,
                    'suffix' => 'kg',
                    'disabled' => $disabled
                ]
            ],
            'submit' => [
                'title' => 'Salvar',
            ]
        ];

        $this->actions = [
            'edit',
            'viewOrder',
            'generateLabel',
            'printLabel',
            'cancelLabel',
            'delete'
        ];

        $this->bulk_actions = array(
            "GenerateLabels" => array(
                'text' => 'Gerar Etiquetas',
                'icon' => 'icon-truck',
                "href" => "#",
            ),
            "PrintLabels" => array(
                'text' => 'Imprimir Etiquetas',
                'icon' => 'icon-print',
                "href" => "#",
            ),
            "divider" => array(
                'text' => 'divider',
            ),
            "CancelLabels" => array(
                'text' => 'Cancelar Etiquetas',
                'icon' => 'icon-times',
                "href" => "#",
            ),
            'delete' => [
                'text' => 'Excluir',
                'icon' => 'icon-trash',
                'href' => '#'
            ]
        );
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
        if (Tools::getIsSet('generatelabel')) {
            $id = Tools::getValue('id_agmelhorenvio_label');
            try {
                $instance = new AgMelhorEnvioLabel($id);
                if ($instance->payment_link) {
                    header("Location: $instance->payment_link");
                    exit();
                }

                if ($instance->status == AgMelhorEnvioLabelsStatusesEnum::TO_BE_GENERATED) {
                    $packages = $this->addLabelToCart($id);
                } else {
                    $packages = [$instance];
                }

                $this->generateLabels($packages);

                $this->module->confirmations[] = "Etiqueta gerada com sucesso!";
            } catch(Exception $e) {
                Logger::addLog($e->getMessage(), 3);
                $this->module->errors[] = $e->getMessage();
            }

            $this->module->saveNotifications();
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
            exit();
        } elseif (Tools::getIsSet('printlabel')) {
            $id = Tools::getValue('id_agmelhorenvio_label');
            try {
                $instance = new AgMelhorEnvioLabel($id);

                if ($instance->status == AgMelhorEnvioLabelsStatusesEnum::TO_BE_GENERATED) {
                    $this->addLabelToCart($id);
                }

                $return = $this->printLabels([$instance]);
                header("Location: $return");
            } catch(Exception $e) {
                Logger::addLog($e->getMessage(), 3);
                $this->module->errors[] = $e->getMessage();
            }

            $this->module->saveNotifications();
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
            exit();
        } elseif (Tools::getIsSet('cancellabel')) {
            $id = Tools::getValue('id_agmelhorenvio_label');
            try {
                $instance = new AgMelhorEnvioLabel($id);
                
                if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::TO_BE_SHIPPED, AgMelhorEnvioLabelsStatusesEnum::SHIPPED, AgMelhorEnvioLabelsStatusesEnum::RELEASED, AgMelhorEnvioLabelsStatusesEnum::PAID, AgMelhorEnvioLabelsStatusesEnum::PRINTED])) {
                    throw new Exception("A etiqueta não pode ser cancelada.");
                }

                $this->cancelLabels([$instance]);
                $this->module->confirmations[] = "Etiqueta cancelada com sucesso!";
            } catch(Exception $e) {
                Logger::addLog($e->getMessage(), 3);
                $this->module->errors[] = $e->getMessage();
            }

            $this->module->saveNotifications();
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
            exit();
        }

        parent::initContent();

        // Include JS for floating sum of selected labels
        // This script finds the "Valor da Etiqueta" column and sums checked rows
        $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/admin_labels_list_sum.js');

        $this->content .= '';
        $this->context->smarty->assign(['content', $this->content]);
    }

    protected function generateLabels($labels = [])
    {
        $ids_orders = [];
        $ids_labels = [];

        foreach ($labels as $label) {
            if ($label->status != AgMelhorEnvioLabelsStatusesEnum::PENDING) {
                throw new Exception("A etiqueta $label->id não está no estado \"Aguardando Pagamento\"");
            }

            $ids_orders[] = $label->id_order_remote;
            $ids_labels[] = $label->id;
        }

        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||  isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }

        $current_link = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $current_link = explode('index.php', $current_link)[0];

        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            $redirect_url = $this->context->link->getAdminLink('AdminAgMelhorEnvioLabels') . '&returnGateway&id_agmelhorenvio_label=' . implode(',', $ids_labels);
        } else {
            $redirect_url = $current_link . $this->context->link->getAdminLink('AdminAgMelhorEnvioLabels') . '&returnGateway&id_agmelhorenvio_label=' . implode(',', $ids_labels);
        }

        
        $response = AgMelhorEnvioGateway::buyLabels($ids_orders, AgMelhorEnvioConfiguration::getPaymentMode(), $redirect_url);
        
        foreach ($labels as $label) {
            if ($response === true) {
                $label->status = 'released';
                $label->update();
            } else {
                $label->payment_link = $response;
                $r = $label->update();
            }
        }

        if ($response !== true) {
            header("Location: $response");
            exit();
        }

        $this->module->confirmations[] = 'Etiquetas geradas com sucesso! Agora você pode imprimí-las.';
        $this->module->saveNotifications();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
        exit();
    }

    protected function printLabels($labels = [])
    {
        $ids_orders = [];
        $ids_labels = [];

        foreach ($labels as $label) {
            $ids_orders[] = $label->id_order_remote;
            $ids_labels[] = $label->id;
        }

        try {
            $response = AgMelhorEnvioGateway::generateLabels($ids_orders);
        } catch (Exception $e) {
            $this->module->errors[] = 'Erro ao imprimir as etiquetas - ' . $e->getMessage();
        }

        try {
            $response = AgMelhorEnvioGateway::printLabels($ids_orders);

            foreach ($labels as $label) {
                $label->status = AgMelhorEnvioLabelsStatusesEnum::PRINTED;
                $label->update();
            }

            header("Location: $response");
            exit();
        } catch (Exception $e) {
            $this->module->errors[] = 'Erro ao imprimir as etiquetas - ' . $e->getMessage();
        }

        $this->module->saveNotifications();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);

        exit();
    }

    protected function cancelLabels($labels = [])
    {
        $orders = [];

        foreach ($labels as $label) {
            $orders[] = [
                'id' => $label->id_order_remote,
                'reason_id' => 4,
                'description' => 'Cancelamento manual por parte do lojista.'
            ];
        }
        try {
            $response = AgMelhorEnvioGateway::cancelLabels($orders);

            foreach ($response as $cancellation) {
                $label = AgMelhorEnvioLabel::getByIdOrderRemote($cancellation->getOrderId());

                if ($cancellation->getCanceled()) {
                    $label->status = AgMelhorEnvioLabelsStatusesEnum::CANCELED;
                    $label->update();

                    $this->module->confirmations[] = "Etiqueta $label->id cancelada com sucesso.";
                } else {
                    $this->module->confirmations[] = "Etiqueta $label->id não cancelada.";
                }
            }
            /* todo */
        } catch (Exception $e) {
            $this->module->errors[] = 'Erro ao cancelar  as etiquetas - ' . $e->getMessage();
        }

        $this->module->saveNotifications();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);

        exit();
    }

    protected function addLabelToCart($id_agmelhorenvio_label)
    {
        return $this->module->addLabelToCart($id_agmelhorenvio_label);
    }

    //************************************ Ações Individuais **********************/
    public function displayViewOrderLink($token, $id)
    {
        $obj = new AgMelhorEnvioLabel($id);

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminOrders') . '&vieworder&id_order=' . $obj->id_order
        ]);

        $tpl = $this->createTemplate('helpers/list/view_order.tpl');
        return $tpl->fetch();
    }

    public function displayGenerateLabelLink($token, $id)
    {
        $instance = new AgMelhorEnvioLabel($id);

        if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::TO_BE_GENERATED, AgMelhorEnvioLabelsStatusesEnum::PENDING])) {
            return;
        }

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminAgMelhorEnvioLabels') . '&generatelabel&id_agmelhorenvio_label=' . $id
        ]);

        $tpl = $this->createTemplate('helpers/list/generate_label.tpl');
        return $tpl->fetch();
    }

    public function displayPrintLabelLink($token, $id)
    {
        $instance = new AgMelhorEnvioLabel($id);
        if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::RELEASED, AgMelhorEnvioLabelsStatusesEnum::PAID, AgMelhorEnvioLabelsStatusesEnum::TO_BE_SHIPPED, AgMelhorEnvioLabelsStatusesEnum::PRINTED])) {
            return;
        }

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminAgMelhorEnvioLabels') . '&printlabel&id_agmelhorenvio_label=' . $id
        ]);

        $tpl = $this->createTemplate('helpers/list/print_label.tpl');
        return $tpl->fetch();
    }

    public function displayCancelLabelLink($token, $id)
    {
        $instance = new AgMelhorEnvioLabel($id);

        if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::TO_BE_SHIPPED, AgMelhorEnvioLabelsStatusesEnum::SHIPPED, AgMelhorEnvioLabelsStatusesEnum::RELEASED, AgMelhorEnvioLabelsStatusesEnum::PAID, AgMelhorEnvioLabelsStatusesEnum::PRINTED])) {
            return;
        }

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminAgMelhorEnvioLabels') . '&cancellabel&id_agmelhorenvio_label=' . $id
        ]);

        $tpl = $this->createTemplate('helpers/list/cancel_label.tpl');
        return $tpl->fetch();
    }

    //************************** Ações em Massa ************************/
    public function processBulkGenerateLabels()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $objs = [];

            try {
                foreach ($this->boxes as $id) {
                    $instance = new AgMelhorEnvioLabel($id);

                    if ($instance->status == AgMelhorEnvioLabelsStatusesEnum::TO_BE_GENERATED) {
                        $packages = $this->addLabelToCart($id);
                    } else {
                        $packages = [$instance];
                    }

                    $objs = array_merge($objs, $packages);
                }
                
                //delay para o Melhor Envio detectar que as etiquetas estão no carrinho
                $this->generateLabels($objs);
            } catch (Exception $e) {
                $this->module->errors[] = $e->getMessage();
            }
        }
        
        $this->module->saveNotifications();

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function processBulkPrintLabels()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $objs = [];

            try {
                foreach ($this->boxes as $id) {
                    $instance = new AgMelhorEnvioLabel($id);

                    if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::RELEASED, AgMelhorEnvioLabelsStatusesEnum::PAID, AgMelhorEnvioLabelsStatusesEnum::TO_BE_SHIPPED, AgMelhorEnvioLabelsStatusesEnum::PRINTED])) {
                        throw new Exception("A etiqueta $id não está em um estado que permita a impressão.");
                    }

                    $objs[] = $instance;
                }

                $this->printLabels($objs);
            } catch (Exception $e) {
                $this->module->errors[] = $e->getMessage();
            }
        }
        
        $this->module->saveNotifications();

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
        exit();
    }

    public function processBulkCancelLabels()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $objs = [];

            try {
                foreach ($this->boxes as $id) {
                    $instance = new AgMelhorEnvioLabel($id);

                    if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::TO_BE_SHIPPED, AgMelhorEnvioLabelsStatusesEnum::SHIPPED, AgMelhorEnvioLabelsStatusesEnum::RELEASED, AgMelhorEnvioLabelsStatusesEnum::PAID])) {
                        throw new Exception("A etiqueta $id não pode ser cancelada.");
                    }

                    $objs[] = $instance;
                }

                $this->cancelLabels($objs);
            } catch (Exception $e) {
                $this->module->errors[] = $e->getMessage();
            }
        }
        
        $this->module->saveNotifications();

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
        exit();
    }

    //*************************** Ações Ajax ******************/
    public function ajaxProcessSaveInvoiceData()
    {
        try {
            $id_order = Tools::getValue('id_order');

            $invoice_number_mapping = $this->module->getInvoiceNumberMapping();
            if (!$invoice_number_mapping->isMappingEnabled()) {
                throw new Exception("O mapeamento do número da nota fiscal não está configurado.");
            }

            $invoice_serie_mapping = $this->module->getInvoiceSerieMapping();
            if (!$invoice_serie_mapping->isMappingEnabled()) {
                throw new Exception("O mapeamento da série da nota fiscal não está configurado.");
            }

            $update_data = [];

            $update_data[$invoice_number_mapping->getMappedfield()] = Tools::getValue('invoice_number');
            $update_data[$invoice_serie_mapping->getMappedfield()] = Tools::getValue('invoice_serie');            

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
        } catch (Exception $e) {
            $this->module->errors[] = $e->getMessage();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        $this->module->saveNotifications();
        exit();
    }

    public function ajaxProcessCreateLabelForOrder()
    {
        $id_order = Tools::getValue('id_order');
        $order  = new Order($id_order);

        $carrier = new Carrier($order->id_carrier);
        $carrier = Carrier::getCarrierByReference($carrier->id_reference);
        $service = AgMelhorEnvioService::getByCarrier($carrier);

        //se a compra não foi feita com uma transportadora utilizada pelo módulo, ela é ignorada
        if (!Validate::isLoadedObject($service)) {
            echo json_encode([
                'success' => false,
                'error' => 'Esse pedido não foi feito com uma transportadora do Melhor Envio.'
            ]);
            exit();

            return false;
        }

        //exclui as etiquetas que já existem para o pedido
        $labels = AgMelhorEnvioLabel::getByIdOrder($order->id);
        foreach ($labels as $label) {
            $obj = new AgMelhorEnvioLabel($label['id_agmelhorenvio_label']);
            $obj->delete();
        }

        $error = AgMelhorEnvioLabel::generateLabelsForOrder($order);
        if ($error !== true) {
            Logger::addLog("agmelhorenvio - Erro gerando etiqueta para o pedido {$order->id} - {$error}", 3);

            echo json_encode([
                'success' => false,
                'error' => $error
            ]);
            exit();
        }

        echo json_encode([
            'success' => true
        ]);

        exit();
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

        if (is_array($this->_list)) {
            $nb = count($this->_list);
            
            for ($i = 0; $i < $nb; $i++) {
                $this->_list[$i]['status'] = AgMelhorEnvioLabelsStatusesEnum::status_names[$this->_list[$i]['status']];
            }
        }
    }

    /* Trata do retorno do Melhor Envio do pagamento das etiquetas */
    protected function handleReturnGateway()
    {
        $id = Tools::getValue('id_agmelhorenvio_label');
        $label = new AgMelhorEnvioLabel($id);

        if (!Validate::isLoadedObject($label)) {
            $this->module->errors[] = "Etiqueta {$id} não encontrada.";
            $this->module->saveNotifications();
            // Tools::redirectAdmin($this->context->link->getAdminLink('AdminAgMelhorEnvioLabelsPrint'));
        }

        Logger::addLog('agmelhorenvio - get - ' . json_encode($_GET));
        Logger::addLog('agmelhorenvio - post - ' . json_encode($_POST));

        $label->payment_link = '';
        $label->status = 'released';
        $label->update();
        
        $this->module->confirmations[] = 'Etiqueta paga com sucesso!';        
        $this->module->saveNotifications();
        // Tools::redirectAdmin($this->context->link->getAdminLink('AdminAgMelhorEnvioLabelsPrint'));
    }
}
