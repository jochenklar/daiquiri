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
 * Class for the form which is used log in. 
 */
class Auth_Form_Login extends Auth_Form_Abstract {

    /**
     * Initializes the form.
     */
    public function init() {
        $this->setFormDecorators();
        $this->addCsrfElement();

        // add elements 
        $this->addUsernameElement(true);
        $this->addPasswordElement(true);

        $this->addPrimaryButtonElement('submit', 'Login');
        $this->addButtonElement('cancel', 'Cancel');

        // set decorators
        $this->addHorizontalGroup(array('username', 'password'));
        $this->addActionGroup(array('submit', 'cancel'));
    }

}
