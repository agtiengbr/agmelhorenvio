<?php
class AdminAgMelhorEnvioLabelsTrackController extends ModuleAdminController
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

        $this->_orderBy = 'date_upd';
        $this->_orderWay = 'DESC';

        parent::__construct();
        


        $this->actions = ['trackings', 'delete'];

        $this->bulk_actions = array(
            "delete" => array(
                'text' => 'Excluir Etiquetas',
                'icon' => 'icon-trash',
                "href" => "#",
            )
        );

        $this->module->prepareNotifications();

        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON a.id_order=o.id_order';
        $this->_select .= 'o.date_add as order_date,';

        $this->_select .= 'CONCAT(length, "x", height, "x", width, "cm - ", weight, "kg") dimensions';

        $this->_where = " AND status != 'pending' AND status != 'canceled'";

        $this->fields_list = [
            'id_agmelhorenvio_label' => [
                'title' => 'ID',
                'type' => 'int',
                'class' => 'center fixed-width-xs'
            ],
            'id_order' => [
                'title' => 'Pedido',
                'class' => 'center fixed-width-xs',
                'filter_key' => 'a!id_order',
            ],
            'order_date' => [
                'title' => 'Data do Pedido',
                'type' => 'date',
                'class' => 'fixed-width-sm',
                'filter_key' => 'o!date_add'
            ],
            'tracking' => [
                'title' => 'Código de Rastreamento',
                'type' => 'text',
                'class' => 'fixed-width-md'
            ],
            'paid_at' => [
                'title' => 'Pagamento da Etiqueta',
                'type' => 'date',
                'class' => 'fixed-width-sm'
            ],
            'posted_at' => [
                'title' => 'Postagem',
                'type' => 'date',
                'class' => 'fixed-width-sm'
            ],
            'delivered_at' => [
                'title' => 'Entrega',
                'type' => 'date',
                'class' => 'fixed-width-sm'
            ],
            'delivery_time' => [
                'title' => 'Prazo de Entrega',
                'hint' => 'Dias úteis',
                'type' => 'int',
                'class' => 'center fixed-width-xs',
                'filter_key' => 'a!delivery_time'
            ],
            'status' => [
                'title' => 'Status',
                'type' => 'select',
                'list' => [
                    'pending' => AgMelhorEnvioLabel::getStatusText('pending'),
                    'released' => AgMelhorEnvioLabel::getStatusText('released'),
                    'posted' => AgMelhorEnvioLabel::getStatusText('posted'),
                    'printed' => AgMelhorEnvioLabel::getStatusText('printed'),
                    'canceled' => AgMelhorEnvioLabel::getStatusText('canceled'),
                    'to_be_generated' => AgMelhorEnvioLabel::getStatusText('to_be_generated'),
                    'delivered' => AgMelhorEnvioLabel::getStatusText('delivered'),
                ],
                'filter_key' => 'a!status'
            ],
            'date_upd' => [
                'title' => 'Data de Atualização',
                'type' => 'datetime',
                'class' => 'center fixed-width-xs',
                'filter_key' => 'a!date_upd'
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

    public function displayTrackingsLink($token, $id, $a)
    {
        try{
            $instance = new AgMelhorEnvioLabel($id);

            if(!empty($id) && !empty($token)){

                if(!empty($instance->id_order_remote) && !empty($instance->protocol)) {
                    if (!in_array($instance->status, [AgMelhorEnvioLabelsStatusesEnum::TO_BE_SHIPPED, AgMelhorEnvioLabelsStatusesEnum::SHIPPED, AgMelhorEnvioLabelsStatusesEnum::RELEASED, AgMelhorEnvioLabelsStatusesEnum::PAID])) {
                        return;
                    }

                    if(!empty($instance->tracking) || !empty($instance->self_tracking)) {

                        $this->context->smarty->assign([
                            'url' => 'https://melhorrastreio.com.br/rastreio/' . ( $instance->tracking != null ? $instance->tracking : $instance->self_tracking )
                        ]);

                        $tpl = $this->createTemplate('helpers/list/tracking_link.tpl');
                        return $tpl->fetch();
                    }
                }
            }
        } catch (Exception $e) {
            $redirect_url = $current_link . $this->context->link->getAdminLink('AdminLogs');

            $this->context->controller->errors[] = "A um problema na etiqueta {$id}, <a href='{$redirect_url}' target='_blank' rel='noopener noreferrer'>clique aqui</a> para mais informações";

            AgClienteLogger::addLog("agmelhorenvio - Erro no rastreamento da etiqueta {$id}: " . $e->getMessage(), 3);
        }
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {

        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);
        $nb = count($this->_list);

        for ($i = 0; $i < $nb; $i++) {            
            $this->_list[$i]['status'] = AgMelhorEnvioLabel::getStatusText($this->_list[$i]['status']);
        }
    }
}