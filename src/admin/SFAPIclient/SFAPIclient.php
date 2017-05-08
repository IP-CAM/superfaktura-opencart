<?php

	require_once('Requests.php');

	class SFAPIclient{

		private
			$email,
			$apikey,
			$headers,
			$className;

		public
			$data = array(
				'Invoice' => array(),
				'Client' => array(),
				'InvoiceItem' => array()
			);

		const
			API_AUTH_KEYWORD = 'SFAPI',
			SFAPI_URL = 'https://moja.superfaktura.sk';

		public function __construct($email, $apikey){
			$this->className = get_class($this);
			$this->email     = $email;
			$this->apikey    = $apikey;
			$this->headers   = array(
				'Authorization' => self::API_AUTH_KEYWORD." email=".$this->email."&apikey=".$this->apikey
			);
		}

		private function _setData($dataSet, $key, $value){
			if(is_array($key)){
				$this->data[$dataSet] = array_merge($this->data[$dataSet], $key);
			} else {
				$this->data[$dataSet][$key] = $value;
			}
		}

		private function _getRequestParams($params, $list_info = true){
			$request_params = "";
			if($list_info){
				$request_params .= "/listinfo:1";
			}
			if(isset($params['search'])){
				$params['search'] = base64_encode($params['search']);
			}
			foreach ($params as $k => $v) {
				$request_params .= "/$k:$v";
			}
			return $request_params;
		}

		public function addItem($item = array()){
			$this->data['InvoiceItem'][] = $item;
		}

		public function addTags($tag_ids = array()){
			$this->data['Tag']['Tag'] = $tag_ids;
		}

		public function clients($params = array(), $list_info = true){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();

			$response = Requests::get($this->getConstant('SFAPI_URL').'/clients/index.json'.$this->_getRequestParams($params, $list_info), $this->headers);
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function delete($id){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();

			$response = Requests::get($this->getConstant('SFAPI_URL').'/invoices/delete/'.$id, $this->headers);
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function expenses($params = array(), $list_info = true){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();

			$response = Requests::get($this->getConstant('SFAPI_URL').'/expenses/index.json'.$this->_getRequestParams($params, $list_info), $this->headers);
			$response_data = json_decode($response->body);
			return $response_data;
		}

		private function getConstant($const){
			return constant(get_class($this)."::".$const);
		}

		public function getCountries(){
			Requests::register_autoloader();
			$response = Requests::get($this->getConstant('SFAPI_URL').'/countries', $this->headers);
			return json_decode($response->body);
		}

		public function getSequences(){
			Requests::register_autoloader();
			$response = Requests::get($this->getConstant('SFAPI_URL').'/sequences/index.json', $this->headers);
			return json_decode($response->body);
		}

		public function getTags(){
			Requests::register_autoloader();
			$response = Requests::get($this->getConstant('SFAPI_URL').'/tags/index.json', $this->headers);
			return json_decode($response->body);
		}

		public function getPDF($invoice_id, $token, $language = 'slo'){
			//mozne hodnoty language [eng,slo,cze]
			Requests::register_autoloader();
			$response = Requests::get($this->getConstant('SFAPI_URL').'/'.$language.'/invoices/pdf/'.$invoice_id.'/token:'.$token, $this->headers);
			return $response->body;
		}

		public function invoice($id){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();

			$response = Requests::get($this->getConstant('SFAPI_URL').'/invoices/view/'.$id.'.json', $this->headers);
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function invoices($params = array(), $list_info = true){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();

			$response = Requests::get($this->getConstant('SFAPI_URL').'/invoices/index.json'.$this->_getRequestParams($params, $list_info), $this->headers);
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function stockItems($params = array(), $list_info = true){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();

			$response = Requests::get($this->getConstant('SFAPI_URL').'/stock_items/index.json'.$this->_getRequestParams($params, $list_info), $this->headers);
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function markAsSent($invoice_id, $email, $subject = '', $message = ''){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}

			$request_data['InvoiceEmail'] = array(
				'invoice_id' => $invoice_id,
				'email' 	 => $email,
				'subject' 	 => $subject,
				'message' 	 => $message,
			);

			Requests::register_autoloader();
			$response = Requests::post($this->getConstant('SFAPI_URL').'/invoices/mark_as_sent', $this->headers, array('data' => json_encode($request_data)));
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function payInvoice($invoice_id, $amount, $currency = 'EUR', $date = null, $payment_type = 'transfer'){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}

			if(is_null($date)){
				$date = date('Y-m-d');
			}

			$request_data['InvoicePayment'] = array(
				'invoice_id' => $invoice_id,
				'payment_type' => $payment_type,
				'amount' => $amount,
				'currency' => $currency,
				'created' => date('Y-m-d', strtotime($date))
			);

			Requests::register_autoloader();
			$response = Requests::post($this->getConstant('SFAPI_URL').'/invoice_payments/add/ajax:1/api:1', $this->headers, array('data' => json_encode($request_data)));
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function save(){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();
			$response = Requests::post($this->getConstant('SFAPI_URL').'/invoices/create', $this->headers, array('data' => json_encode($this->data)));
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function saveClient(){
			if(!class_exists('Requests')){
				trigger_error("Unable to load Requests class", E_USER_WARNING);
				return false;
			}
			Requests::register_autoloader();
			$response = Requests::post($this->getConstant('SFAPI_URL').'/clients/create', $this->headers, array('data' => json_encode($this->data)));
			$response_data = json_decode($response->body);
			return $response_data;
		}

		public function setClient($key, $value = ''){
			return $this->_setData('Client', $key, $value);
		}

		public function setInvoice($key, $value = ''){
			return $this->_setData('Invoice', $key, $value);
		}

	}

	class SFAPIclientCZ extends SFAPIclient{

		const
			SFAPI_URL = 'https://moje.superfaktura.cz';

	}


