<?php

require_once 'PlatronSignature.php';

class OfdReceiptRequest
{
	const SCRIPT_NAME = 'receipt.php';

	public $merchantId;
	public $operationType = 'payment';
	public $paymentId;
	public $items = array();

	private $params = array();

	public function __construct($merchantId, $paymentId)
	{
		$this->merchantId = $merchantId;
		$this->paymentId = $paymentId;
	}

	public function sign($secretKey)
	{
		$params = $this->toArray();
		$params['pg_salt'] = 'salt';
		$params['pg_sig'] = PlatronSignature::make(self::SCRIPT_NAME, $params, $secretKey);
		$this->params = $params;
	}

	public function toArray()
	{
		$result = array();

		$result['pg_merchant_id'] = $this->merchantId;
		$result['pg_operation_type'] = $this->operationType;
		$result['pg_payment_id'] = $this->paymentId;

		foreach ($this->items as $item) {
			$result['pg_items'][] = $item->toArray();
		}

		return $result;
	}

	public function makeXml()
	{
		//var_dump($this->params);
		$xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request></request>');

		foreach ($this->params as $paramName => $paramValue) {
			if ($paramName == 'pg_items') {
				//$itemsElement = $xmlElement->addChild($paramName);
				foreach ($paramValue as $itemParams) {
					$itemElement = $xmlElement->addChild($paramName);
					foreach ($itemParams as $itemParamName => $itemParamValue) {
						$itemElement->addChild($itemParamName, $itemParamValue);
					}
				}
				continue;
			}

			$xmlElement->addChild($paramName, $paramValue);
		}

		return $xmlElement->asXML();
	}
}

class OfdReceiptItem
{
	public $label;
	public $amount;
	public $price;
	public $quantity;
	public $vat;

	public function toArray()
	{
		return array(
			'pg_label' => $this->label,
			'pg_amount' => $this->amount,
			'pg_price' => $this->price,
			'pg_quantity' => $this->quantity,
			'pg_vat' => $this->vat,
		);
	}
}
