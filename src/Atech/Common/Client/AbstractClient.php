<?php
/**
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Atech\Common\Client;

/**
 * Base Client/HTTP Engine
 *
 */
class AbstractClient
{
    private $_data_type;
    private $_url;
    private $_apiuser;
    private $_apikey;
    public $http_code;

    /**
    * Spawn
    *
    * @param string $data_type what Data type to accept/send
    * @param string $url       Base API Url
    * @param string $apiuser   API Username
    * @param string $apikey    API Key
    *
    * @return nothing
    */
    protected function build($data_type, $url, $apiuser, $apikey)
    {
        $this->_data_type = $data_type;
        $this->_url = $url;
        $this->_apiuser = $apiuser;
        $this->_apikey = $apikey;
    }

    /**
    * Make a request
    *
    * @param string     $url    URL route to call
    * @param string|xml $body   XML body to pass
    * @param int        $method which method to call using
    *
    * @return the response
    */
    private function _request($url, $body = '', $method = 0)
    {
        $ch = curl_init($this->_url . $url);

        if ($method == 2) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($method == 1) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $headers = array(
            'Content-Type: ' . $this->_data_type,
            'Accept: ' . $this->_data_type,
            'Authorization: Basic ' . base64_encode(
                $this->_apiuser . ':'. $this->_apikey
            )
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeader'));

        $response = curl_exec($ch);

        $handle = fopen('raw', 'a');
        fwrite($handle, print_r($response, true));
        fclose($handle);

        if ($response === false) {
            // no response
            throw new \Exception(curl_error($ch));
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        libxml_use_internal_errors(true);

        curl_close($ch);

        $this->http_code = $code;

        if ($code == 200 || $code == 201) {
            // convert
            try {
                $response = json_decode(
                    json_encode(
                        simplexml_load_string(
                            $response, 'SimpleXMLElement', LIBXML_NOCDATA
                        )
                    )
                );
            } catch (\Exception $error2) {
                throw new \Exception('XML was not returned, an error has occured');
            }
        } elseif ($code == 404) {
            throw new \Exception('Resource Not Found');
        } elseif ($code == 403) {
            throw new \Exception('Access Denied');
        } elseif ($code == 406) {
            throw new \Exception('Wrong HTTP Verb');
        } elseif ($code == 409) {
            throw new \Exception('Conflict, Unmet Dependancies');
        } elseif ($code == 422) {
            try {
                $response = json_decode(
                    json_encode(
                        simplexml_load_string(
                            $response, 'SimpleXMLElement', LIBXML_NOCDATA
                        )
                    )
                );
                $error = $response->error;
            } catch (\Exception $error2) {
                $error = 'Unprocessable Entity';
            }
            throw new \Exception($error);
        } else {
            // error
            throw new \Exception('Unknown Error HTTP Code: ' . $code);
        }

        return $response;
    }

    /**
    * Get Function
    *
    * @param string $url    Full URL to call
    * @param string $is_set response key to catch for group data
    *
    * @return the response
    */
    protected function get($url, $is_set = false)
    {
        $resp = $this->_request($url);
        // is a set expected
        if ($is_set) {
            // check if a set is returned
            if (!isset($resp->$is_set)) {
                // no results
                $resp->$is_set = array();
            } elseif (!is_int(key($resp->$is_set))) {
                // its not a set, make it one
                $resp->$is_set = array($resp->$is_set);
            }
        }
        return $resp;
    }
    /**
    * Post Function
    *
    * @param string $url    Full URL to call
    * @param string $body   Body to pas
    * @param string $is_set response key to catch for group data
    *
    * @return the response
    */
    protected function post($url, $body, $is_set = false)
    {
        return $this->request($url, $body, 1);
    }
    /**
    * Delete Function
    *
    * @param string $url Full URL to call
    *
    * @return the response
    */
    protected function delete($url)
    {
        $this->request($url, null, 2);
        if ($this->http_code == 200) {
            return true;
        }
    }

    /**
    * Read/Parse Header Function
    *
    * @param object $ch     curl object
    * @param string $string header string
    *
    * @return the response
    */
    public function readHeader($ch, $string)
    {
        $handle = fopen('header', 'a');
        fwrite($handle, print_r($string, true));
        fclose($handle);
        /*
        if (strpos($string, 'Location:') !== false) {
            $this->location = rtrim(str_replace('Location: ', '', $string));
        }
        */

        return strlen($string);
    }
}
