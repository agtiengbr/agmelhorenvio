<?php
/**
 * Trata da impressão das etiquetas
 */

class AgMelhorEnvioShippingPrinter extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function preview(array $orders, $mode = 'private') {
        $response = $this->doRequest('POST', 'shipment/print', ['mode' => $mode, 'orders' => $orders]);
        $response = json_decode($response);

        if (isset($response->url)) {
            return $response->url;
        }

        throw new Exception("A etiquetas não foram confirmadas ainda.");
    }
}