<?php

class AgMelhorEnvioCompany
{
    private $id;
    private $name;
    private $hasGroupedVolumes;
    private $available;
    private $status;
    private $picture;
    private $useOwnContract;
    private $batchSize;


    /**
     * Get the value of batchSize
     */ 
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Set the value of batchSize
     *
     * @return  self
     */ 
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * Get the value of useOwnContract
     */ 
    public function getUseOwnContract()
    {
        return $this->useOwnContract;
    }

    /**
     * Set the value of useOwnContract
     *
     * @return  self
     */ 
    public function setUseOwnContract($useOwnContract)
    {
        $this->useOwnContract = $useOwnContract;

        return $this;
    }

    /**
     * Get the value of picture
     */ 
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set the value of picture
     *
     * @return  self
     */ 
    public function setPicture($picture)
    {
        $this->picture = $picture;

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
     * Get the value of available
     */ 
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * Set the value of available
     *
     * @return  self
     */ 
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get the value of hasGroupedVolumes
     */ 
    public function getHasGroupedVolumes()
    {
        return $this->hasGroupedVolumes;
    }

    /**
     * Set the value of hasGroupedVolumes
     *
     * @return  self
     */ 
    public function setHasGroupedVolumes($hasGroupedVolumes)
    {
        $this->hasGroupedVolumes = $hasGroupedVolumes;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}