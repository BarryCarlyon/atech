<?php
/**
 * Copyright 2012 Barry Carlyon. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Atech\Common\Notification;

/**
 * Base Notification Handler
 *
 */
class AbstractNotification
{
	public $payload;
	private $_signature;
	protected $_keyFile;

	/**
	* Load data from $_POST
	*
	* @return nothing
	*/
	function __construct() {
		$this->payload = isset($_POST['payload']) ? $_POST['payload'] : false;
		$this->_signature = isset($_POST['signature']) ? $_POST['signature'] : false;
	}

	/**
	* Validate a payload against a signature
	*
	* @return bool ok/not ok
	*/
	protected function validateSignature() {
		if ($this->payload && $this->_signature) {
			if (is_file($this->_keyFile)) {
				$key = file_get_contents($this->_keyFile);
				$ok = openssl_verify($this->payload, base64_decode($this->_signature), $key);
				if ($ok == 1) {
					return true;
				}
			}
		}

		return false;
	}
}
