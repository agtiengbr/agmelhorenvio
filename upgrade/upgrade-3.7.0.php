<?php

function upgrade_module_3_7_0($module)
{
    //configura o nome do remetente
    AgMelhorEnvioConfiguration::setShopName(Context::getContext()->shop->name);

    //atualiza URL do Melhor Rastreio
    $services = AgMelhorEnvioService::getAll();
    foreach ($services as $service) {
        /** @var Carrier */
        $carrier = $service->getCarrier();
        if (Validate::isLoadedObject($carrier)) {
            $carrier->url = 'https://melhorrastreio.com.br/meu-rastreio/@';
            $carrier->update();
        }
    }

    return true;
}
