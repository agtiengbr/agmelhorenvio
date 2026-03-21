<?php
/**
 * Trata da geração das etiquetas
 */

class AgMelhorEnvioLabelsGenerator extends AgMelhorEnvioCommunicator
{
    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public function generate(array $orders) {
        $response = $this->doRequest('POST', 'shipment/generate', ['orders' => $orders]);
        $response = json_decode($response);

        return $response->url;
    }
}