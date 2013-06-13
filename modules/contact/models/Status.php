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
 * Model for the contact form Status.
 */
class Contact_Model_Status extends Daiquiri_Model_SimpleTable {

    /**
     * Constructor. Sets resource object and primary field.
     */
    public function __construct() {
        $this->setResource('Daiquiri_Model_Resource_Table');
        $this->getResource()->setTable('Contact_Model_DbTable_Status');
        $this->setValueField('status');
    }

    public function create(array $formParams = array()) {
        // create the form object
        $form = new Contact_Form_Status();

        // valiadate the form if POST
        if (!empty($formParams) && $form->isValid($formParams)) {
            // get the form values
            $values = $form->getValues();

            $this->addValue($values['status']);

            return array('status' => 'ok');
        }

        return array('form' => $form, 'status' => 'form');
    }

}
