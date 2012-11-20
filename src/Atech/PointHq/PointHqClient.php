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
    public function zones()
    {
        return $this->get('zones');//, 'zone');
    }

    /**
    * Get a zone
    *
    * @param integer $zone zone id
    *
    * @return a zone
    */
    public function getZone($zone)
    {
        return $this->get('zones/' . $zone);
    }

    /**
    * Create a zone
    *
    * @param string  $name    The name to use, most commonly the domain name
    * @param integer $ttl     The Time to Live to use in seconds 3600 is an hour for example
    * @param string  $group   Optional Descriptive words
    * @param integer $user_id optional user id to assign to
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
    * @param integer $zone zone id
    * @param array   $data data to update, you cannot update the name/domain
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
    * @param integer $zone zone id    
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

    /**
    * Get records for a zone
    *
    * @param integer $zone zone id
    *
    * @return an array of records
    */
    function getZoneRecords($zone)
    {
        return $this->get('/zones/' . $zone . '/records');
    }

    /**
    * Get a record for a zone
    *
    * @param integer $zone   zone id
    * @param integer $record record id
    *
    * @return the record
    */
    function getZoneRecord($zone, $record)
    {
        return $this->get('/zones/' . $zone . '/records/' . $record);
    }

    /**
    * Create a record for a zone
    *
    * @param integer $zone                zone id to add record to
    * @param string  $data                data for the record
    * @param string  $name                name for the record
    * @param string  $type                record type
    * @param string  $ttl                 record ttl
    * @param string  $redirect_to         record redirect to if a redirect
    * @param string  $redirection_counter record redirect counter
    * 
    * @return the created record
    */
    public function createZoneRecord($zone, $data, $name, $type, $ttl = '', $redirect_to = '', $redirection_counter = '')
    {
        $payload = array('zone-record' => array(
            'data'          => $data,
            'name'          => $name,
            'record-type'   => $type,
            'zone-id'       => $zone
        ));
        if ($ttl) {
            $payload['zone-record']['ttl'] = $ttl;
        }
        if ($redirect_to) {
            $payload['zone-record']['redirect-to'] = $redirect_to;
        }
        if ($redirection_counter) {
            $payload['zone-record']['redirection-counter'] = $redirection_counter;
        }
        $payload = json_encode($payload);
        return $this->post('/zones/' . $zone . '/records', $payload);
    }

    /**
    * Update a zone record
    *
    * @param integer $zone   zone id
    * @param integer $record record id
    * @param array   $data   data to update
    *
    * @return the new zone on success
    */
    public function updateZoneRecord($zone, $record, $data)
    {
        $payload = array('zone-record' => $data);
        $payload = json_encode($payload);
        return $this->put('zones/' . $zone . '/record/' . $record, $payload);
    }
}
