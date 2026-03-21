<?php

class AdminAgMelhorEnvioCacheController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap    = true;
        $this->table        = 'agmelhorenvio_cache';
        $this->identifier   = 'id_agmelhorenvio_cache';
        $this->className    = 'AgMelhorEnvioCache';
        $this->noLink       = true;
        $this->list_no_link = true;
        $this->_defaultOrderBy = 'date_add';
        $this->_defaultOrderWay = 'DESC';
        parent::__construct();

        $this->module->prepareNotifications();

        $this->setFieldsList();

        $this->_select = "CONCAT(s.carrier_name, ' - ', s.service_name) AS service_name";
        $this->_join = 'INNER JOIN ' . _DB_PREFIX_ . 'agmelhorenvio_service s ON s.id_remote=a.id_remote';
        $this->_where = ' AND delivery_time >= 0';
        $this->_group = " GROUP BY a.cache_key";

        $this->actions = ['delete'];
    }



    private function setFieldsList()
    {
        $this->fields_list = [
            'id_agmelhorenvio_cache' => [
                'type'  => 'int',
                'title' => 'ID',
                'class' => 'fixed-width-sm'
            ],
            'cache_key' => [
                'type'  => 'text',
                'title' => 'Chave',
                'maxlength' => 20
            ],
            'service_name' => [
                'type'  => 'text',
                'title' => 'Serviço',
                'maxlength' => 200
            ],
            'shipping_cost_data' => [
                'type'  => 'text',
                'title' => 'Custos',
                'class' => 'fixed-width-lg',
                'callback' => 'ShowShippingCosts',
                'search' => false
            ],
            'delivery_time' => [
                'type'  => 'int',
                'title' => 'Prazo de Entrega',
                'class' => 'fixed-width-lg',
                'suffix' => 'dias úteis'
            ],
            'date_add' => [
                'type' => 'datetime',
                'title' => 'Data do Cálculo'
            ]
        ];
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['clear_cache'] = array(
            'desc' => 'Limpar o cache',
            'icon' => 'process-icon- icon-trash',
        );

        $this->page_header_toolbar_btn['configuration'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name,
            'desc' => 'Configurações',
            'icon' => 'process-icon- icon-cogs',
        );
    }

    public function ajaxProcessClearShippingCache()
    {
        if (Tools::getIsSet('clearCache') && Tools::getValue('clearCache') == 1) {
            if (AgMelhorEnvioConfiguration::getEnabledCache()) {
                $resp = AgMelhorEnvioCache::ClearShippingCache(AgMelhorEnvioConfiguration::getTimeExpireCache());

                echo json_encode(['result' => $resp]);
            }
        }

        exit();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef(array(
            'token' => Tools::getAdminTokenLite('AdminAgMelhorEnvioCache'),
            'btn_clear_cache' => 'page-header-desc-' . $this->table . '-clear_cache',
        ));

        $this->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/custom.js');
    }

    public function ShowShippingCosts($value, $obj = null)
    {
        $decode_value = json_decode($value, true);
        $result = "Preço: " . $decode_value['price'] . "<br />";
        $result .= "Desconto: " . $decode_value['discount'];

        return $result;
    }
}
