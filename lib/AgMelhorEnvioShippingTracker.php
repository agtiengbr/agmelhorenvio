<?php
/**
 * Trata do rastreamento das etiquetas
 */

class AgMelhorEnvioShippingTracker extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function track(array $orders) {

		$response = $this->doRequest('POST', 'shipment/tracking', ['orders' => $orders]);
		$response = json_decode($response);
		$return = [];
		
		foreach (@$response as $cart_item) {
			$row = new AgMelhorEnvioRemoteCartItem;
			
			$row->setId($cart_item->id)
				->setProtocol($cart_item->protocol)
				->setStatus($cart_item->status)
				->setCreatedAt($cart_item->created_at)
				->setPaidAt($cart_item->paid_at)
				->setGeneratedAt($cart_item->generated_at)
				->setPostedAt($cart_item->posted_at)
				->setDeliveredAt($cart_item->delivered_at)
				->setCanceledAt($cart_item->canceled_at)
				->setUpdatedAt(@$cart_item->updated_at)
				->setExpiredAt($cart_item->expired_at)
				->setTracking($cart_item->tracking)
				->setSelfTracking($cart_item->melhorenvio_tracking);
			
			$return[$cart_item->id] = $row;
		}
		
		return $return;
    }
}