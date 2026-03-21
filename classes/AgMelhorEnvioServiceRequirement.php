<?php

class AgMelhorEnvioServiceRequirement extends AgObjectModel
{
    public static $definition = array(
        'table'     => 'agmelhorenvio_service_requirement',
        'primary'   => 'id_agmelhorenvio_service_requirement',
        'multilang' => false,
        'fields'    => array(
            'id_agmelhorenvio_service_requirement' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_agmelhorenvio_option' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'required' => true),
            'id_agmelhorenvio_service' => array('type' => self::TYPE_INT, 'db_type' => 'int unsigned', 'required' => true)
        ),
        'indexes' => array(
            array(
                'fields' => array('id_agmelhorenvio_option', 'id_agmelhorenvio_service'),
                'prefix' => 'unique',
                'name' => 'unique_option_per_service'
            )
        )
    );

    public $id_agmelhorenvio_service_requirement;
    public $id_agmelhorenvio_option;
    public $id_agmelhorenvio_service;

    
    /**
     *  @throws AgMelhorEnvioServiceRequirementSavingException - Erro salvando parâmetro para o serviço
     *  @throws AgMelhorEnvioServiceRequirementDeletingException - Erro removendo parâmetros inativos
     *  @throws AgMelhorEnvioServiceRequirementFindingException
     */
    public static function updateForService(AgMelhorEnvioService $service, AgMelhorEnvioRemoteOptions $options)
    {
        //remove todas as opções que não estão no array $options
        $ids = array();

        foreach ($options->getOptions() as $option) {
            AgMelhorEnvioOption::saveName($option->getName());
            $local_option = AgMelhorEnvioOption::getByName($option->getName());

            if (!Validate::isLoadedObject($local_option)) {
                $name = $option->getName();
                throw new AgMelhorEnvioOptionFindingException("Parâmetro {$name} não encontrado.");
            }

            $ids[] = (int) $local_option->id;
        }
        if (count($ids)) {
            if (!Db::getInstance()->delete(
                'agmelhorenvio_service_requirement',
                'id_agmelhorenvio_service = ' . (int) $service->id . ' AND ' . 'id_agmelhorenvio_option NOT IN (' . implode(',', $ids) . ')'
            )) {
                $msg_error = Db::getInstance()->getMsgError();

                throw new AgMelhorEnvioServiceRequirementDeletingException("Erro removendo parâmetros inativos para o serviço {$service->name} - {$msg_error}.");
            }
        }

        //salva todas as opções que não estejam salvas no banco ainda
        foreach ($options->getOptions() as $option) {
            $local_option = AgMelhorEnvioOption::getByName($option->getName());

            if (!Validate::isLoadedObject($local_option)) {
                $name = $option->getName();
                throw new AgMelhorEnvioOptionFindingException("Parâmetro {$name} não encontrado.");
            }

            $instance = self::getByServiceAndOption($service, $local_option);
            $instance->id_agmelhorenvio_option = $local_option->id;
            $instance->id_agmelhorenvio_service = $service->id;

            if (!$instance->save()) {
                throw new AgMelhorEnvioServiceRequirementSavingException("Erro salvando parâmetro {$local_option->name} para o serviço {$service->name}");
            }
        }
    }

    /**
      *  @throws AgMelhorEnvioServiceRequirementFindingException
      */
    public static function getByServiceAndOption(AgMelhorEnvioService $service, AgMelhorEnvioOption $option)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_service_requirement')
            ->where('id_agmelhorenvio_option = ' . (int) $option->id)
            ->where('id_agmelhorenvio_service = ' . (int) $service->id);

        $db_data = Db::getInstance()->getRow($sql);
        if (!is_array($db_data)) {
            $msg_error = Db::getInstance()->getMsgError();

            if ($msg_error) {
                throw new AgMelhorEnvioServiceRequirementFindingException("Erro buscando parâmetro {$option->name} para o serviço {$service->name} - {$msg_error}");
            }

            $db_data = array();
        }

        $return = new AgMelhorEnvioServiceRequirement();
        $return->hydrate($db_data);

        return $return;
    }

    /**
     * @throws AgMelhorEnvioServiceRequirementFindingException
     */
    public static function getByService(AgMelhorEnvioService $service)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_service_requirement')
            ->where('id_agmelhorenvio_service = ' . (int) $service->id);

        $db_data = Db::getInstance()->executeS($sql);
        if (!is_array($db_data)) {
            $msg_error = Db::getInstance()->getMsgError();

            if ($msg_error) {
                throw new AgMelhorEnvioServiceRequirementFindingException("Erro buscando parâmetros obrigatórios para o serviço {$service->name} - {$msg_error}");
            }

            $db_data = array();
        }

        return ObjectModel::hydrateCollection('AgMelhorEnvioServiceRequirement', $db_data);
    }

    public function getOption()
    {
        return new AgMelhorEnvioOption($this->id_agmelhorenvio_option);
    }
}
