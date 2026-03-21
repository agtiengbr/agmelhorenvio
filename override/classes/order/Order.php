<?php

class Order extends OrderCore
{

    public $agmelhorenvio_invoice_number;
    public $agmelhorenvio_invoice_serie;

    public function __construct($id = null) 
    {
        self::$definition['fields']['agmelhorenvio_invoice_number'] = array('type' => self::TYPE_STRING, 'required' => false, 'size' => 20);
        self::$definition['fields']['agmelhorenvio_invoice_serie'] = array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255);

        parent::__construct($id);
    }
    
    public function getFields() {
        $add_field = parent::getFields();

        if (Context::getContext()->language->iso_code == 'br') {
            $add_field['agmelhorenvio_invoice_number'] = pSQL($this->agmelhorenvio_invoice_number);
            $add_field['agmelhorenvio_invoice_serie'] = pSQL($this->agmelhorenvio_invoice_serie);
        }

        return $add_field;
    }
}
