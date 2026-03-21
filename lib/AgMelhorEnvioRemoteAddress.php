<?php
class AgMelhorEnvioRemoteAddress
{
    protected $postal_code;
    protected $address;
    protected $number;
    protected $name;
    protected $phone;
    protected $district;
    protected $country_id;
    protected $city;
    protected $uf;
    protected $complement;
    protected $note;
    protected $cnpj;
    protected $cpf;
    protected $ie;
    protected $email;
    protected $cnae;

    protected $document;
    protected $company_document;
    protected $state_register;

    public function getCnae()
    {
        return $this->cnae;
    }

    public function setCnae($cnae)
    {
        $this->cnae = $cnae;
        return $this;
    }

    public function getPostalCode()
    {
        return $this->postal_code;
    }

    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function getDistrict()
    {
        return $this->district;
    }

    public function setDistrict($district)
    {
        $this->district = $district;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getUf()
    {
        return $this->uf;
    }

    public function setUf($uf)
    {
        $this->uf = $uf;
        return $this;
    }

    public function setCountryid($country_id)
    {
        $this->country_id = $country_id;
        return $this;
    }

    public function getCountryId()
    {
        return $this->country_id;
    }

    public function getComplement()
    {
        return $this->complement;
    }

    public function setComplement($complement)
    {
        $this->complement = $complement;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function getCnpj()
    {
        return $this->cnpj;
    }

    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;
        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function getIe()
    {
        return $this->ie;
    }

    public function setIe($ie)
    {
        $this->ie = $ie;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setCompanyDocument($company_document)
    {
        $this->company_document = $company_document;
        return $this;
    }

    public function getCompanyDocument()
    {
        return $this->company_document;
    }

    public function setStateRegister($state_register)
    {
        $this->state_register = $state_register;
        return $this;
    }

    public function getStateRegister()
    {
        return $this->state_register;
    }
}
