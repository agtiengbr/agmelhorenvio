<?php

class AgMelhorEnvioRemoteCartItem
{
    protected $service;
    protected $agency;
    protected $from;
    protected $to;
    protected $products;
    protected $packages;
    protected $volumes;
    protected $options;
    protected $coupon;

    protected $id;
    protected $protocol;
    protected $price;
    protected $discount;
    protected $delivery_time;
    protected $status;
    protected $insurance_value;
    protected $weight;
    protected $width;
    protected $height;
    protected $length;
    protected $diameter;
    protected $format;
    protected $billed_weight;
    protected $receipt;
    protected $own_hand;
    protected $collect;
    protected $collect_scheduled_at;
    protected $reverse;
    protected $authorization_code;
    protected $tracking;
    protected $self_tracking;
    protected $paid_at;
    protected $generated_at;
    protected $posted_at;
    protected $delivered_at;
    protected $canceled_at;
    protected $expired_at;
    protected $created_at;
    protected $updated_at;

    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setAgency($agency)
    {
        $this->agency = $agency;
        return $this;
    }

    public function getAgency()
    {
        return $this->agency;
    }

    public function setFrom(AgMelhorEnvioRemoteAddress $from)
    {
        $this->from = $from;
        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setTo(AgMelhorEnvioRemoteAddress $to)
    {
        $this->to = $to;
        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function addPackage(AgMelhorenvioRemotePackage $package)
    {
        $this->packages[] = $package;
        return $this;
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function setReverse($reverse)
    {
        $this->reverse = $reverse;
        return $this;
    }

    public function getReverse()
    {
        return $this->reverse;
    }

    public function setOptions(AgMelhorEnvioRemoteOptions $optionals)
    {
        $this->options = $optionals;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function addProduct($product)
    {
        @$this->products[] = $product;
        return $this;
    }

    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function getProtocol()
    {
        return $this->protocol;
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
    

    /**
     * Get the value of discount
     */ 
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set the value of discount
     *
     * @return  self
     */ 
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get the value of delivery_time
     */ 
    public function getDeliveryTime()
    {
        return $this->delivery_time;
    }

    /**
     * Set the value of delivery_time
     *
     * @return  self
     */ 
    public function setDeliveryTime($delivery_time)
    {
        $this->delivery_time = $delivery_time;

        return $this;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the value of insurance_value
     */ 
    public function getInsuranceValue()
    {
        return $this->insurance_value;
    }

    /**
     * Set the value of insurance_value
     *
     * @return  self
     */ 
    public function setInsuranceValue($insurance_value)
    {
        $this->insurance_value = $insurance_value;

        return $this;
    }

    /**
     * Get the value of weight
     */ 
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set the value of weight
     *
     * @return  self
     */ 
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get the value of width
     */ 
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the value of width
     *
     * @return  self
     */ 
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get the value of height
     */ 
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the value of height
     *
     * @return  self
     */ 
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get the value of length
     */ 
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set the value of length
     *
     * @return  self
     */ 
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get the value of diameter
     */ 
    public function getDiameter()
    {
        return $this->diameter;
    }

    /**
     * Set the value of diameter
     *
     * @return  self
     */ 
    public function setDiameter($diameter)
    {
        $this->diameter = $diameter;

        return $this;
    }

    /**
     * Get the value of format
     */ 
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the value of format
     *
     * @return  self
     */ 
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the value of billed_weight
     */ 
    public function getBilledWeight()
    {
        return $this->billed_weight;
    }

    /**
     * Set the value of billed_weight
     *
     * @return  self
     */ 
    public function setBilledWeight($billed_weight)
    {
        $this->billed_weight = $billed_weight;

        return $this;
    }

    /**
     * Get the value of receipt
     */ 
    public function getReceipt()
    {
        return $this->receipt;
    }

    /**
     * Set the value of receipt
     *
     * @return  self
     */ 
    public function setReceipt($receipt)
    {
        $this->receipt = $receipt;

        return $this;
    }

    /**
     * Get the value of own_hand
     */ 
    public function getOwnHand()
    {
        return $this->own_hand;
    }

    /**
     * Set the value of own_hand
     *
     * @return  self
     */ 
    public function setOwnHand($own_hand)
    {
        $this->own_hand = $own_hand;

        return $this;
    }

    /**
     * Get the value of collect
     */ 
    public function getCollect()
    {
        return $this->collect;
    }

    /**
     * Set the value of collect
     *
     * @return  self
     */ 
    public function setCollect($collect)
    {
        $this->collect = $collect;

        return $this;
    }

    /**
     * Get the value of collect_scheduled_at
     */ 
    public function getCollectScheduledAt()
    {
        return $this->collect_scheduled_at;
    }

    /**
     * Set the value of collect_scheduled_at
     *
     * @return  self
     */ 
    public function setCollectScheduledAt($collect_scheduled_at)
    {
        $this->collect_scheduled_at = $collect_scheduled_at;

        return $this;
    }

    /**
     * Get the value of authorization_code
     */ 
    public function getAuthorizationCode()
    {
        return $this->authorization_code;
    }

    /**
     * Set the value of authorization_code
     *
     * @return  self
     */ 
    public function setAuthorizationCode($authorization_code)
    {
        $this->authorization_code = $authorization_code;

        return $this;
    }

    /**
     * Get the value of tracking
     */ 
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * Set the value of tracking
     *
     * @return  self
     */ 
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;

        return $this;
    }

    /**
     * Get the value of self_tracking
     */ 
    public function getSelfTracking()
    {
        return $this->self_tracking;
    }

    /**
     * Set the value of self_tracking
     *
     * @return  self
     */ 
    public function setSelfTracking($self_tracking)
    {
        $this->self_tracking = $self_tracking;

        return $this;
    }

    /**
     * Get the value of paid_at
     */ 
    public function getPaidAt()
    {
        return $this->paid_at;
    }

    /**
     * Set the value of paid_at
     *
     * @return  self
     */ 
    public function setPaidAt($paid_at)
    {
        $this->paid_at = $paid_at;

        return $this;
    }

    /**
     * Get the value of generated_at
     */ 
    public function getGeneratedAt()
    {
        return $this->generated_at;
    }

    /**
     * Set the value of generated_at
     *
     * @return  self
     */ 
    public function setGeneratedAt($generated_at)
    {
        $this->generated_at = $generated_at;

        return $this;
    }

    /**
     * Get the value of posted_at
     */ 
    public function getPostedAt()
    {
        return $this->posted_at;
    }

    /**
     * Set the value of posted_at
     *
     * @return  self
     */ 
    public function setPostedAt($posted_at)
    {
        $this->posted_at = $posted_at;

        return $this;
    }

    /**
     * Get the value of delivered_at
     */ 
    public function getDeliveredAt()
    {
        return $this->delivered_at;
    }

    /**
     * Set the value of delivered_at
     *
     * @return  self
     */ 
    public function setDeliveredAt($delivered_at)
    {
        $this->delivered_at = $delivered_at;

        return $this;
    }

    /**
     * Get the value of canceled_at
     */ 
    public function getCanceledAt()
    {
        return $this->canceled_at;
    }

    /**
     * Set the value of canceled_at
     *
     * @return  self
     */ 
    public function setCanceledAt($canceled_at)
    {
        $this->canceled_at = $canceled_at;

        return $this;
    }

    /**
     * Get the value of expired_at
     */ 
    public function getExpiredAt()
    {
        return $this->expired_at;
    }

    /**
     * Set the value of expired_at
     *
     * @return  self
     */ 
    public function setExpiredAt($expired_at)
    {
        $this->expired_at = $expired_at;

        return $this;
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */ 
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of updated_at
     */ 
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */ 
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function addVolume($volume)
    {
        $this->volumes[] = $volume;
        return $this;
    }

    public function getVolumes()
    {
        return $this->volumes;
    }
}

