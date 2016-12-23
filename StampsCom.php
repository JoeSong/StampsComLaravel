<?php

class StampsCom {
	private $Authenticator;
	public $client;
	public $output;
	private $wsdl = 'https://swsim.stamps.com/swsim/swsimv42.asmx?wsdl';

	private function connect() {
		$authData = array(
			'Credentials' => array(
				'IntegrationID' => env('STAMPS_INTEGRATION_ID'),
				'Username'      => env('STAMPS_USERNAME'),
				'Password'      => env('STAMPS_PASSWORD')
			)
		);

		$opts = array(
			'http' => array(
				'user_agent' => 'PHPSoapClient'
			),
			'ssl'  => array(
				'verify_peer'       => false,
				'verify_peer_name'  => false,
				'allow_self_signed' => true
			)
		);

		$context = stream_context_create($opts);

		ini_set('soap.wsdl_cache_enabled', '1');

		try {
			$this->client = new SoapClient($this->wsdl, array(
				'stream_context' => $context,
				'trace'          => 1,
				'encoding'       => 'UTF-8'
			));
		} catch (SoapFault $E) {
			var_dump($E->getMessage());
		}

		$auth                = $this->client->AuthenticateUser($authData);
		$this->Authenticator = $auth->Authenticator;
	}

	public function GetRates($ToZIPCode, $ToCountry, $FromZIPCode, $FromCountry, $WeightLb, $ShipDate = false, $PackageType = 'Package') {
		$this->connect();

		if (!$ShipDate) {
			$ShipDate = date('Y-m-d');
		}
		$data = array(
			'Authenticator' => $this->Authenticator,
			'Rate'          => array(
				'FromZIPCode' => $FromZIPCode,
				'ToZIPCode'   => $ToZIPCode,
				'WeightLb'    => $WeightLb,
				'PackageType' => $PackageType,
				'ShipDate'    => $ShipDate,
				'ToCountry'   => $ToCountry,
				'FromCountry' => $FromCountry,
			)
		);

		$r = $this->client->GetRates($data);

		return array(
			'rates'  => $r->Rates->Rate,
			'params' => $data
		);
	}
}

