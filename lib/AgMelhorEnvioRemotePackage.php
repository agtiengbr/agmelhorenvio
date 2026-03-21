<?php

class AgMelhorEnvioRemotePackage
{
    protected $width;
    protected $height;
    protected $length;
    protected $weight;
    protected $insurance_value;
    protected $format;
    protected $products;
    protected $price;
    protected $discount;
    protected $delivery_time;

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function setInsuranceValue($insurance_value)
    {
        $this->insurance_value = $insurance_value;
        return $this;
    }

    public function getInsuranceValue()
    {
        return $this->insurance_value;
    }

    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function addProduct($product)
    {
        @$this->products[] = $product;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
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
        return $this;
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
}
