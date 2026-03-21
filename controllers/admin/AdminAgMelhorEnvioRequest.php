<?php

class AdminAgMelhorEnvioRequestController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap        = true;
        $this->table            = 'agmelhorenvio_request';
        $this->className        = 'agmelhorenvioRequest';
        $this->identifier       = 'id_agmelhorenvio_request';
        $this->list_no_link     = true;
        $this->_defaultOrderBy  = 'id_agmelhorenvio_request';
        $this->_defaultOrderWay = 'DESC';


        parent::__construct();

        $this->module->prepareNotifications();

        $this->fields_list = [
            'id_agmelhorenvio_request' => [
                'title' => 'ID',
                'align' => 'center',
                'type' => 'int',
                'class' => 'fixed-width-xs',
            ],
            'http_code' => [
                'title' => 'Código HTTP',
                'type' => 'int',
                'class' => 'fixed-width-md'
            ],
            'method' => [
                'title' => 'Método',
                'type' => 'text',
                'class' => 'fixed-width-md'
            ],
            'endpoint' => [
                'title' => 'URL',
                'type' => 'text'
            ],
            'date_add' => [
                'title' => 'Data',
                'type' => 'datetime'
            ]
        ];

        $this->actions = ['view'];
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
        parent::initContent();

        if (Tools::getIsSet('view' . $this->table)) {
            $request = $this->loadObject();
            $request->response = json_decode($request->response);
            $request->headers_display = $this->formatLogValue($request->headers);
            $request->body_display = $this->formatLogValue($request->body);

            $html  = $this->content;

            //contéudo geral da ação VER
            $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/ag_melhor_envio_request/view.tpl');
            $tpl->assign(['obj' => $request]);
            $html .= $tpl->fetch();

            $this->content = $html;
            $this->context->smarty->assign(['content' => $html]);

            return;
        }
    }

    /**
     * Exibe dados armazenados aceitando serialized, JSON ou texto puro.
     */
    protected function formatLogValue($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        $unserialized = @unserialize($value);
        if ($unserialized !== false || $value === 'b:0;') {
            return print_r($unserialized, true);
        }

        $json = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return print_r($json, true);
        }

        return (string) $value;
    }
}
