<?php
/**
 * Trata da pré-visualização das etiquetas
 */

class AgMelhorEnvioShippingPreviewer extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function preview(array $orders) {
        $response = $this->doRequest('POST', 'shipment/preview', ['orders' => $orders]);
        $response = json_decode($response);

        return $response->url;
    }
}