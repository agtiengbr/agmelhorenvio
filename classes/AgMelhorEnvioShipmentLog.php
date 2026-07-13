<?php

class AgMelhorEnvioShipmentLog extends AgObjectModel
{
    const EVENT_LABEL_CREATED = 'label_created';
    const EVENT_CART_ATTEMPT = 'cart_attempt';
    const EVENT_CART_WAIT = 'cart_wait';
    const EVENT_LABEL_PAID = 'label_paid';
    const EVENT_LABEL_PRINTED = 'label_printed';
    const EVENT_LABEL_TRACKED = 'label_tracked';
    const EVENT_XML_UPLOADED = 'xml_uploaded';
    const EVENT_XML_REMOVED = 'xml_removed';
    const EVENT_LABEL_CANCELED = 'label_canceled';

    public static $definition = [
        'table' => 'agmelhorenvio_shipment_log',
        'primary' => 'id_agmelhorenvio_shipment_log',
        'multilang' => false,
        'fields' => [
            'id_agmelhorenvio_shipment_log' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'required' => true,
            ],
            'id_agmelhorenvio_label' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'default' => 0,
            ],
            'event' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'db_type' => 'varchar(64)',
                'required' => true,
            ],
            'success' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'db_type' => 'tinyint(1)',
                'allow_null' => true,
            ],
            'message' => [
                'type' => self::TYPE_STRING,
                'db_type' => 'text',
                'required' => true,
            ],
            'details' => [
                'type' => self::TYPE_HTML,
                'db_type' => 'mediumtext',
                'allow_null' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type' => 'datetime',
            ],
        ],
        'indexes' => [
            [
                'fields' => ['id_order'],
                'name' => 'idx_order',
            ],
            [
                'fields' => ['id_agmelhorenvio_label'],
                'name' => 'idx_label',
            ],
        ],
    ];

    public $id_agmelhorenvio_shipment_log;
    public $id_order;
    public $id_agmelhorenvio_label;
    public $event;
    public $success;
    public $message;
    public $details;
    public $date_add;

    /**
     * @param int $id_order
     * @param string $event
     * @param string $message
     * @param bool|null $success
     * @param int|null $id_label
     * @param mixed $details
     * @return bool
     */
    public static function addLog($id_order, $event, $message, $success = null, $id_label = null, $details = null)
    {
        $log = new self();
        $log->id_order = (int) $id_order;
        $log->id_agmelhorenvio_label = (int) $id_label;
        $log->event = (string) $event;
        $log->message = (string) $message;
        $log->success = $success === null ? null : ((int) (bool) $success);
        $log->date_add = date('Y-m-d H:i:s');

        if ($details !== null) {
            if (is_string($details)) {
                $log->details = $details;
            } else {
                $log->details = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return (bool) $log->add();
    }

    /**
     * @param int $id_order
     * @return array
     */
    public static function getByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_shipment_log')
            ->where('id_order = ' . (int) $id_order)
            ->orderBy('date_add ASC, id_agmelhorenvio_shipment_log ASC');

        $rows = Db::getInstance()->executeS($sql);
        return is_array($rows) ? $rows : [];
    }
}
