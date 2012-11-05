<?php

class ATechAPI {
	protected $apiuser;
	protected $apikey;
	protected $hostname;
//	protected $protect_201;
	public $http_code;

	private function request($url, $body = '', $method = 0) {
		$ch = curl_init($this->url . $url);

		if ($method == 2) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		} else if ($method == 1) {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}
		$headers = array(
			'Content-Type: application/xml',
			'Accept: application/xml',
			'Authorization: Basic ' . base64_encode($this->hostname .'/' . $this->apiuser . ':'. $this->apikey)
		);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeader'));

		$response = curl_exec($ch);

		$handle = fopen('raw', 'a');
		fwrite($handle, print_r($response,true));
		fclose($handle);


		if ($response === FALSE) {
			// no response
			throw new Exception(curl_error($ch));
		}

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//		echo 'the code: ' . $code;
//		echo 'the code: ' . $response;
		libxml_use_internal_errors(TRUE);

		curl_close($ch);

		$this->http_code = $code;

		if ($code == 200 || $code == 201) {
			// convert
			try {
//				$response = new SimpleXMLElement($response);
				// temp
//				$response = str_replace(array('<201>', '</201>'), array('<' . $this->protect_201 . '>', '</' . $this->protect_201 . '>'), $response);
				$response = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)));
			} catch (\Exception $error2) {
				throw new Exception('XML was not returned, an error has occured');
			}
		} else if ($code == 404) {
			throw new Exception('Resource Not Found');
		} else if ($code == 403) {
			throw new Exception('Access Denied');
		} else if ($code == 406) {
			throw new Exception('Wrong HTTP Verb');
		} else if ($code == 409) {
			throw new Exception('Conflict, Unmet Dependancies');
		} else if ($code == 422) {
			try {
				$response = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)));
				$error = $response->error;
			} catch (\Exception $error2) {
				$error = 'Unprocessable Entity';
			}
			throw new Exception($error);
		} else {
			// error
//			throw new Exception $response;
			throw new Exception('Unknown Error HTTP Code: ' . $code);
		}

		return $response;
	}

	/**
	calls
	*/
	protected function get($url, $is_set = FALSE) {
		$resp = $this->request($url);
		// is a set expected
		if ($is_set) {
			// check if a set is returned
			if (!isset($resp->$is_set)) {
				// no results
				$resp->$is_set = array();
			} else if (!is_int(key($resp->$is_set))) {
				// its not a set, make it one
				$resp->$is_set = array($resp->$is_set);
			}
//			if (!is_array($resp->$is_set)) {
				// not a set
//				$item = array($resp->$is_set);
//				unset($resp->$is_set);
//				$resp->$is_set->addChild($item);
//			}
		}
		return $resp;
	}
	protected function post($url, $body, $is_set = FALSE) {
//		$this->protect_201 = $is_set;
		return $this->request($url, $body, 1);
	}
	protected function delete($url) {
		$this->request($url, null, 2);
		if ($this->http_code == 200) {
			return TRUE;
		}
	}

	/**
	Utility
	*/
	public function readHeader($ch, $string) {
		$handle = fopen('header', 'a');
		fwrite($handle, print_r($string,true));
		fclose($handle);
//		fwrite(fopen('header', 'a'), $string);

/*
		if (strpos($string, 'Location:') !== FALSE) {
			$this->location = rtrim(str_replace('Location: ', '', $string));
		}
		*/

		return strlen($string);
	}
}
