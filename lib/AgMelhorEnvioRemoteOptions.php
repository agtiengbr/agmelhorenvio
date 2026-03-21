<?php

class AgMelhorEnvioRemoteOptions
{
    protected $options;

    public function __construct()
    {
        $this->options = array();
    }

    public function addOption(AgMelhorEnvioRemoteOption $option)
    {
        $this->options[] = $option;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
