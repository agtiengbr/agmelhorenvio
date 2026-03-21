<?php

class AgMelhorEnvioShippingAddToCart extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */

    public function addToCart(AgMelhorEnvioRemoteCartItem $cart_item)
    {
        $data_to_server = array();
        $data_to_server['service'] = $cart_item->getService();
        $data_to_server['coupon'] = AgMelhorEnvioConfiguration::getCoupon();

        $agency = $cart_item->getAgency();
        if ($agency) {
            $data_to_server['agency'] = $agency;
        }

        $from = $cart_item->getFrom();
        $data_to_server['from'] = [
            'name' => $from->getName(),
            'economic_activity_code' => $from->getCnae() ?: '',
            'phone' => $from->getPhone(),
            'email' => $from->getEmail(),
            'company_document' => $from->getDocument(),
            'state_register' => $from->getStateRegister(),
            'address' => $from->getAddress(),
            'complement' => $from->getComplement(),
            'number' => $from->getNumber(),
            'district' => $from->getDistrict(),
            'city' => $from->getCity(),
            'state_abbr' => $from->getUf(),
            'country_id' => $from->getCountryId(),
            'postal_code' => $from->getPostalCode()
        ];

        //o servidor do Melhor Envio gera um erro se for enviado um e-mail nulo
        if (!$data_to_server['from']['email']) {
            unset($data_to_server['from']['email']);
        }

        $to = $cart_item->getTo();
        $data_to_server['to'] = [
            'name' => $to->getName(),
            'phone' => $to->getPhone(),
            'email' => $to->getEmail(),
            'address' => $to->getAddress(),
            'complement' => $to->getComplement(),
            'number' => $to->getNumber(),
            'district' => $to->getDistrict(),
            'city' => $to->getCity(),
            'state_abbr' =>$to->getUf(),
            'country_id' => $to->getCountryId(),
            'postal_code' => $to->getPostalCode()
        ];

        if ($to->getDocument()) {
            $data_to_server['to']['document'] = $to->getDocument();
        }

        if ($to->getCompanyDocument()) {
            $data_to_server['to']['company_document'] = $to->getCompanyDocument();
        }

        $insurance_value = 0;

        $products = $cart_item->getProducts();
        $data_to_server['products'] = [];        
        foreach ($products as $product) {
            $data_to_server['products'][] = [
                'name' => mb_substr($product['name'],0,60),
                'quantity' =>  $product['quantity'],
                'unitary_value' => round($product['unitary_value'], 2),
                'weight' => @$product['weight']
            ];

            $insurance_value += $product['insurance_value'];
        }

        $packages = $cart_item->getPackages();
        $data_to_server['volumes'] = [];
        foreach ($packages as $package) {
            $data_to_server['volumes'][] = [
                'weight' => max($package->getWeight(), 0.01),
                'height' => $package->getHeight(),
                'width' => $package->getWidth(),
                'length' => $package->getLength()
            ];
        }

        $options = $cart_item->getOptions();
        foreach ($options->getOptions() as $option) {
            $data_to_server['options'][$option->getName()] = $option->getValue();    
        }

        if (!isset($data_to_server['options']) || !is_array($data_to_server['options'])) {
            $data_to_server['options'] = [];
        }

        $agmelhorenvio_service = AgMelhorEnvioService::getByIdRemote($cart_item->getService());
        if ($insurance_value && $agmelhorenvio_service->insurance) {
            //o valor máximo do seguro para encomendas sem NF é R$ 1000,00
            if (!empty($data_to_server['options']['non_commercial'])) {
                $insurance_value = min($insurance_value, 1000);
            }

            $data_to_server['options']['insurance_value'] = $insurance_value;
        }

        $response = $this->doRequest('POST', 'cart', $data_to_server);
        $parsed_response = json_decode($response);
        //analiza a resposta
        $from = new AgMelhorEnvioRemoteAddress;

        if (isset($parsed_response->from)) {
            $from->setName($parsed_response->from->name);
            $from->setPhone($parsed_response->from->phone);
            $from->setEmail($parsed_response->from->email);
            $from->setDocument($parsed_response->from->company_document);
            $from->setStateRegister($parsed_response->from->state_register);
            $from->setPostalCode($parsed_response->from->postal_code);
            $from->setAddress($parsed_response->from->address);
            $from->setNumber($parsed_response->from->location_number);
            $from->setComplement($parsed_response->from->complement);
            $from->setDistrict($parsed_response->from->district);
            $from->setCity($parsed_response->from->city);
            $from->setUf($parsed_response->from->state_abbr);
            $from->setCountryId($parsed_response->from->country_id);
        }



        $to = new AgMelhorEnvioRemoteAddress;
        
        if (isset($parsed_response->to)) {
            $to->setName($parsed_response->to->name);
            $to->setPhone($parsed_response->to->phone);
            $to->setEmail($parsed_response->to->email);
            $to->setDocument($parsed_response->to->document);
            $to->setStateRegister($parsed_response->to->state_register);
            $to->setPostalCode($parsed_response->to->postal_code);
            $to->setAddress($parsed_response->to->address);
            $to->setNumber($parsed_response->to->location_number);
            $to->setComplement($parsed_response->to->complement);
            $to->setDistrict($parsed_response->to->district);
            $to->setCity($parsed_response->to->city);
            $to->setUf($parsed_response->to->state_abbr);
            $to->setCountryId($parsed_response->to->country_id);
        }
        
        $service = new AgMelhorEnvioRemoteService;
        if (isset($parsed_response->service)) {
            $service->setId($parsed_response->service->id)
                ->setName($parsed_response->service->name)
                ->setPicture($parsed_response->service->picture)
                ->setType($parsed_response->service->type)
                ->setRange($parsed_response->service->range);

            $company = new AgMelhorEnvioRemoteCompany;
            $company->setId($parsed_response->service->company->id);
            $company->setName($parsed_response->service->company->name);
            $company->setPicture($parsed_response->service->company->picture);
            $service->setCompany($company);
            
            $optionals = json_decode($parsed_response->service->optionals);
            if (is_array($optionals)) {
                foreach ($optionals as $optional) {
                    $option = new AgMelhorEnvioRemoteOption;
                    $option->setName($optional);
                    $option->setValue(true);

                    $service->addOptional($option);
                }
            }


            $requirements = json_decode($parsed_response->service->requirements);
            if (is_array($requirements)) {
                foreach (@$requirements as $requirement) {
                    $option = new AgMelhorEnvioRemoteOption;
                    $option->setName($requirement);
                    $option->setValue(true);

                    $service->addRequirement($option);
                }
            }
        }


        $package = new AgMelhorEnvioRemotePackage;


        $cart_item = new AgMelhorEnvioRemoteCartItem;
        $cart_item->setProducts($parsed_response->products)
            ->setService($service)
            ->setAgency(@$parsed_response->agency)
            ->setFrom($from)
            ->setTo($to)
            ->setId($parsed_response->id)
            ->setProtocol($parsed_response->protocol)
            ->setPrice($parsed_response->price)
            ->setDiscount($parsed_response->discount)
            ->setDeliveryTime($parsed_response->delivery_max)
            ->setStatus($parsed_response->status)
            ->setInsuranceValue($parsed_response->insurance_value)
            ->setWeight($parsed_response->weight)
            ->setWidth($parsed_response->width)
            ->setHeight($parsed_response->height)
            ->setLength($parsed_response->length)
            ->setDiameter($parsed_response->diameter)
            ->setFormat($parsed_response->format)
            ->setReceipt($parsed_response->receipt)
            ->setOwnHand($parsed_response->own_hand)
            ->setCollect($parsed_response->collect)
            ->setReverse($parsed_response->reverse)
            ->setCreatedAt($parsed_response->created_at)
            ->setUpdatedAt($parsed_response->updated_at);

        foreach ($parsed_response->volumes as $volume) {
            $cart_item->addVolume($volume);
        }

        return $cart_item;
    }
}
