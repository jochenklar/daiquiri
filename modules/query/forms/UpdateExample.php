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

class Query_Form_UpdateExample extends Daiquiri_Form_Abstract {

    protected $_name = null;
    protected $_query = null;

    public function setName($name) {
        $this->_name = $name;
    }

    public function setQuery($query) {
        $this->_query = $query;
    }

    public function init() {
        $this->setFormDecorators();
        $this->addCsrfElement();

        // add elements
        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'class' => 'input-xxlarge',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Text()),
            )
        ));
        $this->addElement('textarea', 'query', array(
            'label' => 'Query',
            'class' => 'input-xxlarge',
            'rows' => '12',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Volatile()),
            )
        ));
        $this->addPrimaryButtonElement('submit', 'Update Example');
        $this->addButtonElement('cancel', 'Cancel');

        // add groups
        $this->addHorizontalGroup(array('name', 'query'));
        $this->addActionGroup(array('submit', 'cancel'));

        // set fields
        if (isset($this->_name)) {
            $this->setDefault('name', $this->_name);
        }
        if (isset($this->_query)) {
            $this->setDefault('query', $this->_query);
        }
    }

}