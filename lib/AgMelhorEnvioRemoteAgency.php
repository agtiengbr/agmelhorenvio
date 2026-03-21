<?php

class AgMelhorEnvioRemoteAgency
{
	protected $id;
	protected $name;
	protected $initials;
	protected $code;
	protected $companies;
	protected $company_name;
	protected $status;
	protected $email;
	protected $address;

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function getId()
	{
		return $this->id;
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

	public function setInitials($initials)
	{
		$this->initials = $initials;
		return $this;
	}

	public function getInitials()
	{
		return $this->initials;
	}

	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function setCompanyName($company_name)
	{
		$this->company_name = $company_name;
		return $this;
	}

	public function getCompanyName()
	{
		return $this->company_name;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	public function getStatus()
	{
		return $this->status;
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

	public function setAddress(AgMelhorEnvioRemoteAddress $address)
	{
		$this->address = $address;
		return $this;
	}

	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * Get the value of companies
	 */ 
	public function getCompanies()
	{
		return $this->companies;
	}

	/**
	 * Set the value of companies
	 *
	 * @return  self
	 */ 
	public function setCompanies($companies)
	{
		$this->companies = $companies;

		return $this;
	}
}
