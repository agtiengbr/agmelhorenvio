<?php
class AdminAgMelhorEnvioLabelsPrintController extends ModuleAdminController
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

        $this->_orderBy = 'created_at';
        $this->_orderWay = 'ASC';

        parent::__construct();

        $this->module->prepareNotifications();

        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON a.id_order=o.id_order';
        $this->_select .= 'o.date_add as order_date,';

        $this->_select .= 'CONCAT(length, "x", height, "x", width, "cm - ", weight, "kg") dimensions';

        $this->fields_list = [
            'id_agmelhorenvio_label' => [
                'title' => 'ID',
                'class' => 'center fixed-width-xs'
            ],
            'id_order' => [
                'title' => 'Pedido',
                'class' => 'center fixed-width-xs'
            ],
            'order_date' => [
                'title' => 'Data do Pedido',
                'type' => 'datetime',
                'class' => 'center fixed-width-xs'
            ],
            'delivery_time' => [
                'title' => 'Prazo de Entrega',
                'hint' => 'Dias úteis',
                'type' => 'int',
                'class' => 'center fixed-width-xs'
            ],
            'price' => [
                'title' => 'Custo do Frete',
                'type' => 'price',
                'class' => 'center fixed-width-xs'
            ],
            'dimensions' => [
                'title' => 'Dimensões',
                'hint' => 'LxAxC - Peso',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'class' => 'center fixed-width-xs'
            ],
            'discount' => [
                'title' => 'Desconto',
                'type' => 'price',
                'class' => 'center fixed-width-xs'
            ],
            'status' => [
                'title' => 'Status',
                'type' => 'select',
                'list' => [
                    'pending' => AgMelhorEnvioLabel::getStatusText('pending'),
                    'released' => AgMelhorEnvioLabel::getStatusText('released'),
                    'printed' => AgMelhorEnvioLabel::getStatusText('printed')
                ],
                'filter_key' => 'a!status'
            ],
        ];

        $this->_where = ' AND status IN("pending", "released", "printed")';

        $this->actions = [
            'viewOrder',
            'previewLabel',
            'buyLabel',
            'printLabel',
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
                $this->_list[$i]['status'] = AgMelhorEnvioLabel::getStatusText($this->_list[$i]['status']);
            }
        }
    }

    public function initContent()
    {
        if (Tools::getIsSet('buylabel')) {
            $id = Tools::getValue('id_agmelhorenvio_label');
            $instance = new AgMelhorEnvioLabel($id);
            
            try {
                if (!Validate::isLoadedObject($instance)) {
                    throw new Exception("Etiqueta {$id} não localizada.");
                }

                if (!$instance->id_order_remote) {
                    throw new Exception("Etiqueta {$id} não foi gerada ainda.");
                }

                if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||  isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                    $protocol = 'https://';
                }
                else {
                    $protocol = 'http://';
                }

                $current_link = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $current_link = explode('index.php', $current_link)[0];

                $redirect_url = $current_link . $this->context->link->getAdminLink('AdminAgMelhorEnvioLabels') . '&returnGateway&id_agmelhorenvio_label=' . $id;

                $response = AgMelhorEnvioGateway::buyLabels(
                    [$instance->id_order_remote],
                    AgMelhorEnvioConfiguration::getPaymentMode(),
                    $redirect_url
                );

                if ($response === true) {
                    $instance->status = 'released';
                    $instance->update();
                    
                    $this->module->confirmations[] = 'Etiqueta paga com sucesso!';
                } else {
                    $instance->payment_link = $response;
                    $instance->update();
                    header("Location: $response");
                    exit();
                }
            } catch(Exception $e) {
                Logger::addLog($e->getMessage(), 3);
                $this->module->errors[] = $e->getMessage();
            }

            $this->module->saveNotifications();
            Tools::redirectAdmin(self::$currentIndex);
        } elseif (Tools::getIsSet('previewlabel')) {
            $id = Tools::getValue('id_agmelhorenvio_label');
            $instance = new AgMelhorEnvioLabel($id);

            try {
                if (!Validate::isLoadedObject($instance)) {
                    throw new Exception("Etiqueta {$id} não localizada.");
                }

                if (!$instance->id_order_remote) {
                    throw new Exception("Etiqueta {$id} não foi gerada ainda.");
                }

                $response = AgMelhorEnvioGateway::previewLabels(
                    [$instance->id_order_remote]
                );

                header("Location: $response");
                exit();
            } catch(Exception $e) {
                Logger::addLog($e->getMessage(), 3);
                $this->module->errors[] = $e->getMessage();
            }

            $this->module->saveNotifications();
            Tools::redirectAdmin(self::$currentIndex);
        } elseif (Tools::getIsSet('printlabel')) {
            $id = Tools::getValue('id_agmelhorenvio_label');
            $instance = new AgMelhorEnvioLabel($id);

            try {
                if (!Validate::isLoadedObject($instance)) {
                    throw new Exception("Etiqueta {$id} não localizada.");
                }

                if (!$instance->id_order_remote) {
                    throw new Exception("Etiqueta {$id} não foi gerada ainda.");
                }

                try {
                    $response = AgMelhorEnvioGateway::printLabels(
                        [$instance->id_order_remote]
                    );
                } catch (Exception $e) {
                    if (isset($e->http_code) && $e->http_code != 404) {
                        throw $e;
                    }

                    AgMelhorEnvioGateway::generateLabels([$instance->id_order_remote]);
                    $response = AgMelhorEnvioGateway::printLabels([$instance->id_order_remote]);
                }
                // Marca como impressa para permitir reimpressão e bloquear novo add ao carrinho
                $instance->status = AgMelhorEnvioLabelsStatusesEnum::PRINTED;
                $instance->update();
                
                header("Location: $response");
                exit();
            } catch(Exception $e) {
                Logger::addLog($e->getMessage(), 3);
                $this->module->errors[] = $e->getMessage();
            }

            $this->module->saveNotifications();
            Tools::redirectAdmin(self::$currentIndex);
        }

        parent::initContent();

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
    

    public function displayBuyLabelLink($token, $id)
    {
        $obj = new AgMelhorEnvioLabel($id);
        if ($obj->status !== 'pending') {
            return;
        }

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminAgMelhorEnvioLabelsPrint') . '&buylabel&id_agmelhorenvio_label=' . $id,
            'new_tab' => AgMelhorEnvioConfiguration::getPaymentMode() != 0 //processa o pagamento em nova aba se for escolhido um gateway externo
        ]);

        
        if ($obj->payment_link) {
            $this->context->smarty->assign([
                'url' => $obj->payment_link
            ]);
        }

        $tpl = $this->createTemplate('helpers/list/buy_label.tpl');
        return $tpl->fetch();
    }

    public function displayPreviewLabelLink($token, $id)
    {
        $obj = new AgMelhorEnvioLabel($id);
        if (!in_array($obj->status, ['released', 'printed'])) {
            return;
        }

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminAgMelhorEnvioLabelsPrint') . '&previewlabel&id_agmelhorenvio_label=' . $id
        ]);

        $tpl = $this->createTemplate('helpers/list/preview_label.tpl');
        return $tpl->fetch();
    }

    public function displayPrintLabelLink($token, $id)
    {
        $obj = new AgMelhorEnvioLabel($id);
        if (!in_array($obj->status, ['released', 'printed'])) {
            return;
        }
        
        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminlink('AdminAgMelhorEnvioLabelsPrint') . '&printlabel&id_agmelhorenvio_label=' . $id
        ]);

        $tpl = $this->createTemplate('helpers/list/print_label.tpl');
        return $tpl->fetch();
    }
}