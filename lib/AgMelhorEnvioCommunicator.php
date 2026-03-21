<?php

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;

class AgMelhorEnvioCommunicator
{
    protected $api_base;
    protected $token;
    protected $is_sandbox;

    public function __construct($is_sandbox)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('MelhorEnvio: cURL library is required.');
        }

        $this->is_sandbox = $is_sandbox;

        if ($this->is_sandbox == false) {
            $this->api_base = 'https://www.melhorenvio.com.br/api/v2/me/';
        } else {
            $this->api_base = 'https://sandbox.melhorenvio.com.br/api/v2/me/';
        }

        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
       
    }

    /**
     * @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     */
    protected function doRequest($method, $resource, $data = array())
    {
        if (!$method) {
            throw new AgMelhorEnvioMissingArgumentsException('Tipo de requisição não informada ao realizar requisição.');
        }

        $url = $this->api_base . $resource;
        if (strtoupper($method) === 'POST') {
            $payload = json_encode($data);
            $extraHeaders[] = 'Content-Type:application/json';

            $methodOptions = array(
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_POST => 1
            );

        } else {
            $contentLength = null;
            $methodOptions = array(
                CURLOPT_HTTPGET => true
            );
        }

        $extraHeaders[] = 'User-Agent: AgMelhorEnvio PrestaShop Module';
        if ($this->token){
            $extraHeaders[] = "Authorization: Bearer " . $this->token;
        }

        $extraHeaders[] = 'Accept: application/json';

        $options = array(
            CURLOPT_HTTPHEADER => $extraHeaders,
            CURLOPT_RETURNTRANSFER => true
        );
        $options = ($options + $methodOptions);

        $curl = curl_init($url);
        curl_setopt_array($curl, $options);
        $r = curl_exec($curl);
        $info = curl_getinfo($curl);

        $resp['body'] = $r;

        $obj = new AgMelhorEnvioRequest;

        $obj->endpoint = $url;
        $obj->headers = $this->normalizeLogData($extraHeaders);
        $obj->method = $method;
        $obj->body = $this->normalizeLogData($data);
        $obj->http_code = (int) $info['http_code'];
        $obj->response = $resp['body'];

        $obj->save();
    
        if ($resp['body'] == false) {
            $e = new Exception('Resposta do MelhorEnvio Inválida: "' . var_export($resp['body'], true) . '" - dados enviados: ' . json_encode($data));
            throw $e;
        }

        if ($obj->http_code > 400) {
            $e = new Exception("Erro retornado pelo Melhor Envio: {$obj->response}.");
            throw $e;
        }

        return $resp['body'];

    }

    /**
     * Garantir que headers/body fiquem salvos de forma legível e segura.
     */
    protected function normalizeLogData($data)
    {
        if ($data === null) {
            return '';
        }

        if (is_string($data)) {
            return $data;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json !== false) {
            return $json;
        }

        return print_r($data, true);
    }
}
