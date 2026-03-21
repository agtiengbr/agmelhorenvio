<?php
/**
 * Trata do rastreamento das etiquetas
 */

class AgMelhorEnvioShippingCancellation extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function cancel(array $orders) {
        $reason = AgMelhorEnvioLabelCancelReason::getDefault();
        foreach ($orders as &$order) {
            $order['reason_id'] = $reason->getId();
            $order['description'] = $reason->getDescription();
        }

        $response = $this->doRequest('POST', 'shipment/cancel', $orders);
        $response = json_decode($response);

    	$return = [];

    	foreach (@$response as $id_order=>$order) {
            $obj = new AgMelhorEnvioShippingCancelReturn;

            $obj->setOrderId($id_order)
                ->setTime(@$order->time)
                ->setCanceled(@$order->canceled);

            $return[] = $obj;
        }
    		

        return $return;
    }
}