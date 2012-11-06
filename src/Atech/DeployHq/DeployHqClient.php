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

namespace Atech\DeployHq;

use Atech\Common\Client\AbstractClient;

/**
 * Client to interact with CodeBase
 *
 */
class DeployHqClient extends AbstractClient
{
    static $dataType = 'application/json';
    /**
    * Spawn
    *
    * @param string $domain  SubDomain of the deployhq account
    * @param string $apiuser API Username normally a email address
    * @param string $apikey  API Key
    *
    * @return class object
    */
    public function __construct($domain, $apiuser, $apikey)
    {
        return parent::build(DeployHqClient::$dataType, 'https://' . $domain . '.deployhq.com/', $apiuser, $apikey);
    }

    /**
    Projects
    */

    /**
    * Get All Projects
    */
    function projects() {
        return $this->get('projects');
    }

    /**
    * 
    */
}
