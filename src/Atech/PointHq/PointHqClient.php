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

namespace Atech\PointHq;

use Atech\Common\Client\AbstractClient;

/**
 * Client to interact with CodeBase
 *
 */
class PointHqClient extends AbstractClient
{
    static $url = 'http://pointhq.com';
    static $dataType = 'application/json';// api supports xml too

    /**
    * Spawn
    *
    * @param string $apiuser API Username normally your normal username
    * @param string $apikey  API Key
    *
    * @return class object
    */
    public function __construct($apiuser, $apikey)
    {
        return parent::build(PointHqClient::$dataType, PointHqClient::$url, $apiuser, $apikey);
    }

    /**
    Zones
    */

    /**
    * Get all Zones
    *
    * @return an array of zones
    */
    public function zones() {
        return $this->get('zones');//, 'zone');
    }

    /**
    * Get a zone
    *
    * @param int $zone zone id
    *
    * @return a zone
    */
    public function getZone($zone) {
        return $this->get('zones/' . $zone);
    }

    /**
    * Create a zone
    *
    * @param string $name    The name to use, most commonly the domain name
    * @param int    $ttl     The Time to Live to use in seconds 3600 is an hour for example
    * @param string $group   Optional Descriptive words
    * @param int    $user_id optional user id to assign to
    *
    * @return the new zone on success
    */
    public function createZone($name, $ttl, $group = false, $user_id = false)
    {
        $payload = array('zone' => array(
            'name'  => $name,
            'ttl'   => $ttl,
        ));
        if ($group) {
            $payload['zone']['group'] = $group;
        }
        if ($user_id) {
            $payload['zone']['user-id'] = $user_id;
        }
        $payload = json_encode($payload);
        return $this->post('zones', $payload);
    }

    /**
    * Update a zone
    *
    * @param int   $zone zone id    
    * @param array $data data to update
    *
    * @return the new zone on success
    */
    public function updateZone($zone, $data)
    {
        $payload = array('zone' => $data);
        $payload = json_encode($payload);
        return $this->put('zones/' . $zone, $payload);
    }

    /**
    * Delete a zone
    *
    * @param int   $zone zone id    
    *
    * @return true on success
    */
    public function deleteZone($zone)
    {
        return $this->delete('zones/' . $zone);
    }

    /**
    Zone Records
    */
}
