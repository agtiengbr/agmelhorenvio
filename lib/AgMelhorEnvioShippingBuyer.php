<?php
/**
 * Trata da compra (checkout) das etiquetas junto ao MelhorEnvio.
 */

class AgMelhorEnvioShippingBuyer extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function buy(
        $orders = array(),
        $payment_mode = 0,
        $redirect_url="",
        $wallet = 0
    ) {

        $data_to_server = [
            'orders' => $orders,
            'redirect' => $redirect_url,
            'wallet' => $wallet
        ];

        if ($payment_mode) {
            $data_to_server['gateway'] = $payment_mode;
        }

        $response = $this->doRequest('POST', 'shipment/checkout', $data_to_server);
        $response = json_decode($response);
        
        $status = $response->purchase->status;

        if ($status === 'paid') {
            return true;
        } else {
            return $response->redirect;
        }
    }
}