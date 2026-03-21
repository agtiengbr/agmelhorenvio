<?php
class AgMelhorEnvioRemoteService
{
    protected $id;
    protected $name;
    protected $picture;
    protected $type;
    protected $range;
    protected $requirements;
    protected $optionals;
    protected $price;
    protected $delivery_time;
    protected $discount;

    //@AgMelhorEnvioRemoteCompany
    protected $company;

    public function __construct()
    {
        $this->requirements = new AgMelhorEnvioRemoteOptions();
        $this->optionals = new AgMelhorEnvioRemoteOptions();
    }

    public function setId($id)
    {
        $this->id  = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function setName($name)
    {
        $this->name  = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setType($type)
    {
        $this->type  = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setRange($range)
    {
        $this->range  = $range;
        return $this;
    }

    public function getRange()
    {
        return $this->range;
    }

    public function addRequirement(AgMelhorEnvioRemoteOption $option)
    {
        $this->requirements->addOption($option);
        return $this;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function addOptional(AgMelhorEnvioRemoteOption $option)
    {
        $this->optionals->addOption($option);
        return $this;
    }

    public function getOptional()
    {
        return $this->optionals;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getDeliveryTime()
    {
        return $this->delivery_time;
    }

    public function setDeliveryTime($delivery_time)
    {
        $this->delivery_time = $delivery_time;
        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }
    
    public function getPicture()
    {
        return $this->picture;
    }

    public function setCompany(AgMelhorEnvioRemoteCompany $company)
    {
        $this->company = $company;

        if (!$this->picture) {
            $this->picture = $company->getPicture();
        }

        return $this;
    }

    public function getCompany()
    {
        return $this->company;
    }
}
