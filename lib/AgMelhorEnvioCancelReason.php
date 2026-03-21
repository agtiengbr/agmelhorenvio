<?php

class AgMelhorEnvioLabelCancelReason
{
    /** @var int */
    protected $id;

    /** @var description */
    protected $description;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public static function getDefault()
    {
        $return = new AgMelhorEnvioLabelCancelReason;
        $return->setId(2)->setDescription("Motivo não informado.");
        return $return;
    }
}