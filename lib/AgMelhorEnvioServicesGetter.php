<?php

class AgMelhorEnvioServicesGetter extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function getServices()
    {
        $response = $this->doRequest('GET', 'shipment/services');
        $parsed_response = json_decode($response);

        $return = array();

        foreach ($parsed_response as $service) {
            $service_obj = new AgMelhorEnvioRemoteService();
            $service_obj->setId($service->id)
                ->setName($service->name)
                ->setType($service->type)
                ->setRange($service->range)
                ->setPicture($service->company->picture);

            $company = new AgMelhorEnvioRemoteCompany;
            $company->setId($service->company->id)
                    ->setName($service->company->name)
                    ->setPicture($service->company->picture);

            $service_obj->setCompany($company);

            if (is_array($service->requirements)) {
                foreach ($service->requirements as $requirement) {
                    $requirement_obj = new AgMelhorEnvioRemoteOption();
                    $requirement_obj->setName($requirement);
                    $service_obj->addRequirement($requirement_obj);
                }
            }

            if (is_array($service->optionals)) {
                foreach ($service->optionals as $optional) {
                    $optional_obj = new AgMelhorEnvioRemoteOption();
                    $optional_obj->setName($optional);
                    $service_obj->addOptional($optional_obj);
                }
            }

            $return[] = $service_obj;
        }

        return $return;
    }
}
