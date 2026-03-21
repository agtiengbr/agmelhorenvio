<?php

class AgMelhorEnvioShippingCancelReturn
{
	protected $order_id;
	protected $time;
	protected $canceled;

	public function setOrderId($order_id)
	{
		$this->order_id = $order_id;
		return $this;
	}

	public function getOrderId()
	{
		return $this->order_id;
	}

	public function setTime($time)
	{
		$this->time = $time;
		return $this;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function setCanceled($canceled)
	{
		$this->canceled = $canceled;
		return $this;
	}

	public function getCanceled()
	{
		return $this->canceled;
	}
}
