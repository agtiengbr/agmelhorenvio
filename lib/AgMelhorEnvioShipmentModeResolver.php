<?php

class AgMelhorEnvioShipmentModeResolution
{
    /** @var string commercial|non_commercial */
    public $mode;

    /** @var bool */
    public $should_wait = false;

    /** @var string */
    public $wait_reason = '';

    /** @var string|null */
    public $invoice_number;

    /** @var string|null */
    public $invoice_key;

    /** @var string|null */
    public $xml_content;

    public function hasInvoiceData()
    {
        return !empty($this->invoice_number) && !empty($this->invoice_key);
    }

    public function isCommercial()
    {
        return $this->mode === AgMelhorEnvioShipmentTypesEnum::COMMERCIAL;
    }
}

class AgMelhorEnvioShipmentModeResolver
{
    /**
     * @param AgMelhorEnvioService $service
     * @param object|null $invoice {number, key}
     * @param string|null $xml_content
     * @return AgMelhorEnvioShipmentModeResolution
     */
    public static function resolve(AgMelhorEnvioService $service, $invoice = null, $xml_content = null)
    {
        $result = new AgMelhorEnvioShipmentModeResolution();
        $result->invoice_number = isset($invoice->number) ? trim((string) $invoice->number) : null;
        $result->invoice_key = isset($invoice->key) ? trim((string) $invoice->key) : null;
        $result->xml_content = $xml_content ?: null;

        if (!$result->invoice_key && $xml_content) {
            $extracted = AgMelhorEnvioOrderNfe::extractNfeKey($xml_content);
            if ($extracted) {
                $result->invoice_key = $extracted;
            }
        }

        if (!$result->invoice_number && $xml_content) {
            $extractedNumber = AgMelhorEnvioOrderNfe::extractNfeNumber($xml_content);
            if ($extractedNumber) {
                $result->invoice_number = $extractedNumber;
            }
        }

        $shipmentType = $service->shipment_type ?: AgMelhorEnvioShipmentTypesEnum::HYBRID;
        if (!$service->allowsNonCommercial()) {
            $shipmentType = AgMelhorEnvioShipmentTypesEnum::COMMERCIAL;
        }

        $hasInvoice = $result->hasInvoiceData();
        $hasXml = !empty($result->xml_content);
        $waitXml = (bool) $service->wait_nfe_xml || $service->isAzulCargo();

        if ($shipmentType === AgMelhorEnvioShipmentTypesEnum::NON_COMMERCIAL) {
            $result->mode = AgMelhorEnvioShipmentTypesEnum::NON_COMMERCIAL;
            return $result;
        }

        if ($shipmentType === AgMelhorEnvioShipmentTypesEnum::HYBRID) {
            if ($hasInvoice) {
                $result->mode = AgMelhorEnvioShipmentTypesEnum::COMMERCIAL;
            } else {
                $result->mode = AgMelhorEnvioShipmentTypesEnum::NON_COMMERCIAL;
            }
            return $result;
        }

        // commercial
        $result->mode = AgMelhorEnvioShipmentTypesEnum::COMMERCIAL;

        if ($waitXml && !$hasXml) {
            $result->should_wait = true;
            $result->wait_reason = 'Aguardando carregamento do XML da NF-e para emissão comercial.';
            return $result;
        }

        if (!$hasInvoice) {
            $result->should_wait = true;
            $result->wait_reason = 'Os dados da nota fiscal (número e chave) são obrigatórios para envio comercial.';
            return $result;
        }

        return $result;
    }
}
