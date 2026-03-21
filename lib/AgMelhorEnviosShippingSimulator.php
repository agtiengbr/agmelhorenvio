<?php

class AgMelhorEnviosShippingSimulator extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function simulateShipping(
        AgMelhorEnvioRemoteAddress $from,
        AgMelhorEnvioRemoteAddress $to,
        AgMelhorEnvioRemoteOptions $options,
        array $products,
        array $package = [],
        array $services = []
    ) {
        $data_to_server = array(
            'from' => array(
                'postal_code' => $from->getPostalCode(),
            ),
            'to' => array(
                'postal_code' => $to->getPostalCode(),
            ),
            'products' => $products,
            'options' => [],
            'services' => implode(',', $services)
        );

        if ($products) {
            $data_to_server['products'] = $products;
        } else {
            $data_to_server['package'] = $package;
        }

        //agora só um serviço pode ser utilizado em cada cálculo.

        $agmelhorenvio_service = AgMelhorEnvioService::getByIdRemote($services[0]);
        foreach ($data_to_server['products'] as &$product) {
            if (!isset($product['length'])) $product['length'] = @$product['depth'];
            if (!(@$product['insurance_value']) && $agmelhorenvio_service->insurance) $product['insurance_value'] = @$product['price_wt'];
        }

        $return = [];

        foreach ($options->getOptions() as $option) {
            $data_to_server['options'][$option->getName()] = $option->getValue();
        }
        
        $response = $this->doRequest('POST', 'shipment/calculate', $data_to_server);
        $parsed_response = json_decode($response);

        if (!is_array($parsed_response)) {
            $parsed_response = [$parsed_response];
        }

        foreach ($parsed_response as $parsed_carrier) {
            if (@$parsed_carrier->error) {
                continue;
            }

            $row = new AgMelhorEnvioRemoteShippingResponse;
            $row->setIdService($parsed_carrier->id)
                ->setName($parsed_carrier->name)
                ->setPrice($parsed_carrier->price)
                ->setDiscount($parsed_carrier->discount)
                ->setCurrency($parsed_carrier->currency);

            if (isset($parsed_carrier->delivery_time)) {
                $row->setDeliveryTime($parsed_carrier->delivery_time);
            }

            //pacotes
            foreach ($parsed_carrier->packages as $package) {
                $package_obj = new AgMelhorEnvioRemotePackage;

                $package_obj->setInsuranceValue($package->insurance_value)
                    ->setWeight($package->weight)
                    ->setWidth($package->dimensions->width)
                    ->setHeight($package->dimensions->height)
                    ->setLength($package->dimensions->length)
                    ->setFormat($package->format)
                    ->setPrice(@$package->price)
                    ->setDiscount(@$package->discount)
                    ->setDeliveryTime(@$package->delivery_time);

                if (isset($package->products)) {
                    if (!$package->products) {
                        $package->products = [];

                        foreach ($products as $product) {
                            $package->products[] = (object)$product;
                        }
                    }

                    foreach ($package->products as $product) {
                        $package_obj->addProduct($product);
                    }
                }

                $row->addPackage($package_obj);
            }

            //opções adicionais (AR, VD, MP, CL)
            foreach ((array) $parsed_carrier->additional_services as $name => $value) {
                $opt = new AgMelhorEnvioRemoteOption;
                $opt->setName($name)
                    ->setValue($value);

                $row->addAdditionalService($opt);
            }
            
            //transportadora
            $company = new AgMelhorEnvioRemoteCompany;
            $company->setName($parsed_carrier->company->name)
                ->setId($parsed_carrier->company->id)
                ->setPicture($parsed_carrier->company->picture);

            $row->setCompany($company);

            $return[] = $row;
        }

        return $return;
    }
}
