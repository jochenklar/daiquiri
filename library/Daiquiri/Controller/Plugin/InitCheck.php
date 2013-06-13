<?php

/*
 *  Copyright (c) 2012, 2013 Jochen S. Klar <jklar@aip.de>,
 *                           Adrian M. Partl <apartl@aip.de>, 
 *                           AIP E-Science (www.aip.de)
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  See the NOTICE file distributed with this work for additional
 *  information regarding copyright ownership. You may obtain a copy
 *  of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * @class   Daiquiri_Controller_Plugin_InitCheck InitCheck.php
 * @brief   Daiquiri InitCheck front controller plugin.
 * 
 * Class for the daiquiri front controller plugin handling errors.
 * 
 * Checks whether the Daiquiri configuration environment has been properly set.
 * 
 */
class Daiquiri_Controller_Plugin_InitCheck extends Zend_Controller_Plugin_Abstract {

    /**
     * @brief   preDispatch method - called by Front Controller after dispatch
     * @param   Zend_Controller_Request_Abstract $request: request object
     * 
     * Checks whether the Daiquiri configuration environment has been properly set. If
     * not, raise error.
     * 
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        parent::preDispatch($request);

        if (Daiquiri_Config::getInstance()->isEmpty()) {
            $request->setModuleName('config');
            $request->setControllerName('error');
            $request->setActionName('init');
        }
    }

}

