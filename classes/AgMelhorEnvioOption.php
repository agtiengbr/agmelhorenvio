<?php

class AgMelhorEnvioOption extends AgObjectModel
{
    public static $definition = array(
        'table'     => 'agmelhorenvio_option',
        'primary'   => 'id_agmelhorenvio_option',
        'multilang' => false,
        'fields'    => array(
            'id_agmelhorenvio_option' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'name' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
        ),
        'indexes' => array(
            array(
                'fields' => array('name'),
                'prefix' => 'unique',
                'name' => 'unique_name'
            )
        )
    );

    public $id_agmelhorenvio_option;
    public $name;

    public static function getByName($name)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_option');
        $sql->where('name = "' . pSQL($name) . '"');

        $db_data = Db::getInstance()->getRow($sql);

        if (!is_array($db_data)) {
            $db_data = array();
        }

        $return = new AgMelhorEnvioOption();
        $return->hydrate($db_data);

        return $return;
    }

    /**
     * @throws AgMelhorEnvioOptionSavingException - Erro ao salvar no BD
     */
    public static function saveName($name)
    {
        $instance = self::getByName($name);
        $instance->name = $name;

        if (!$instance->save()) {
            $msg_error = Db::getInstance()->getMsgError();

            throw new AgMelhorEnvioOptionSavingException("Erro salvando parâmetro {$name} do Melhor Envio - {$msg_error}.");
        }
    }
}
