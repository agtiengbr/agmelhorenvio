<?php

class AgMelhorEnvioRemoteShippingResponse
{
    protected $id_service;
    protected $name;
    protected $price;
    protected $discount;
    protected $currency;
    protected $delivery_time;
    protected $packages;
    protected $additional_services;
    protected $company;

    public function setIdService($id_service)
    {
        $this->id_service = $id_service;
        return $this;
    }

    public function getIdService()
    {
        return $this->id_service;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setDeliveryTime($delivery_time)
    {
        $this->delivery_time = $delivery_time;
        return $this;
    }

    public function getDeliveryTime()
    {
        return $this->delivery_time;
    }

    public function addPackage(AgMelhorEnvioRemotePackage $package)
    {
        @$this->packages[] = $package;
        return $this;
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function addAdditionalService(AgMelhorEnvioRemoteOption $option)
    {
        @$this->additional_services[] = $option;
        return $this;
    }

    public function getAdditionalServices()
    {
        return $this->additional_services;
    }

    public function setCompany(AgMelhorEnvioRemoteCompany $company)
    {
        $this->company = $company;
        return $this;
    }

    public function getCompany()
    {
        return $this->company;
    }
}