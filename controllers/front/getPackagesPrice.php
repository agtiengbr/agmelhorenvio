<?php

class AgMelhorEnviogetPackagesPriceModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $sql = new DbQuery;
        $sql->from('agmelhorenvio_label', 'a')
            ->where('status = "to_be_generated"')
            ->innerJoin('orders', 'o', 'o.id_order=a.id_order')
            ->where('a.price = 0');

        $labels = Db::getInstance()->executeS($sql);

        if (!count($labels)) {
            exit();
        }

        foreach ($labels as $label) {
            try {
                $order = new Order($label['id_order']);
                if ($order->hasBeenDelivered()) {
                    continue;
                }

                $service = new AgMelhorEnvioService($label['service_id']);
                $customer_address = new Address($label['id_address_delivery']);

                $from_remote = new AgMelhorEnvioRemoteAddress();
                $from_remote->setPostalCode(preg_replace("/[^0-9]/", "", AgMelhorEnvioConfiguration::getShopAddressZipcode()));

                $to_remote = new AgMelhorEnvioRemoteAddress();
                $to_remote->setPostalCode(preg_replace("/[^0-9]/", "", $customer_address->postcode));

                $package['width'] = $label['width'];
                $package['height'] = $label['height'];
                $package['length'] = $label['length'];
                $package['weight'] = $label['weight'];

                $options = new AgMelhorEnvioRemoteOptions();

                if ($service->own_hands) {
                    $option = new AgMelhorEnvioRemoteOption();
                    $option->setName('own_hand');
                    $option->setValue(true);

                    $options->addOption($option);
                }

                if ($service->receipt) {
                    $option = new AgMelhorEnvioRemoteOption();
                    $option->setName('receipt');
                    $option->setValue(true);

                    $options->addOption($option);
                }

                if ($service->insurance) {
                    $package['insurance_value'] = $label['total_products'];
                }

                $response = AgMelhorEnvioGateway::simulateShipping(
                    $from_remote,
                    $to_remote,
                    $options,
                    [],
                    $package,
                    [$service->id]
                );

                $agme_label = new AgMelhorEnvioLabel($label['id_agmelhorenvio_label']);
                if (Validate::isLoadedObject($agme_label) && is_array($response) && method_exists($response[0], 'getPrice')) {
                    $agme_label->price = $response[0]->getPrice();
                    $agme_label->discount = $response[0]->getDiscount();

                    $agme_label->update();
                }
            } catch (Exception $e) {
                AgClienteLogger::addLog("agmelhorenvio - Erro ao atualizar a etiqueta: " . $e->getMessage(), 3, $e->getCode(), "AgMelhorEnvioLabel", "", true);
            }
        }


        exit();
    }
}
