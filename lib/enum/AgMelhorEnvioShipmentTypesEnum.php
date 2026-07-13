<?php

class AgMelhorEnvioShipmentTypesEnum
{
    const NON_COMMERCIAL = 'non_commercial';
    const COMMERCIAL = 'commercial';
    const HYBRID = 'hybrid';

    public static function getAll()
    {
        return [
            self::NON_COMMERCIAL,
            self::COMMERCIAL,
            self::HYBRID,
        ];
    }

    public static function getLabels()
    {
        return [
            self::NON_COMMERCIAL => 'Não comercial (sem NF-e)',
            self::COMMERCIAL => 'Comercial (com NF-e)',
            self::HYBRID => 'Híbrido (usar NF-e se houver dados)',
        ];
    }
}
