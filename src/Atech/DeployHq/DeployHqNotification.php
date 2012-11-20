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

namespace Atech\DeployHq;

use Atech\Common\Notification\AbstractNotification;

/**
 * Client to interact with CodeBase
 *
 */
class DeployHqNotification extends AbstractNotification
{
    /**
    * grab data
    * validate the signature
    *
    * @return mixed packet on ok false not ok
    */
    function __construct()
    {
        $this->_keyFile = __DIR__ . 'public.key';

        parent::__construct();

        if ($this->validateSignature()) {
            return json_decode($this->payload);
        }
        return false;
    }
}
