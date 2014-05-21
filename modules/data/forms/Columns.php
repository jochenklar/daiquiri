<?php

/*
 *  Copyright (c) 2012-2014 Jochen S. Klar <jklar@aip.de>,
 *                           Adrian M. Partl <apartl@aip.de>, 
 *                           AIP E-Science (www.aip.de)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Data_Form_Columns extends Daiquiri_Form_Abstract {

    protected $_tables = array();
    protected $_table_id;
    protected $_roles = array();
    protected $_ucds = array();
    protected $_entry = array();
    protected $_submit;
    protected $_csrfActive = true;

    public function setTables($tables) {
        $this->_tables = $tables;
    }

    public function setTableId($table_id) {
        $this->_table_id = $table_id;
    }

    public function setRoles($roles) {
        $this->_roles = $roles;
    }
    
    public function setUcds($ucds) {
        $this->_ucds = $ucds;
    }

    public function setEntry($entry) {
        $this->_entry = $entry;
    }

    public function setSubmit($submit) {
        $this->_submit = $submit;
    }

    public function setCsrfActive($csrfActive) {
        $this->_csrfActive = $csrfActive;
    }

    public function init() {
        $this->setFormDecorators();

        if($this->_csrfActive === true) {
            $this->addCsrfElement();
        }

        // add elements
        $this->addElement('select', 'table_id', array(
            'label' => 'Table:',
            'required' => true,
            'multiOptions' => $this->_tables
        ));
        $this->addElement('text', 'name', array(
            'label' => 'Column name',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Volatile()),
            )
        ));
        $this->addElement('text', 'order', array(
            'label' => 'Order of column',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'int'),
            )
        ));
        $this->addElement('text', 'type', array(
            'label' => 'Column type',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Volatile()),
            )
        ));
        $this->addElement('text', 'unit', array(
            'label' => 'Column unit',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Volatile()),
            )
        ));
        $this->addElement('text', 'ucd', array(
            'label' => 'Column UCD',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Volatile()),
            )
        ));
        $this->addElement('select', 'publication_role_id', array(
            'label' => 'Published for: ',
            'multiOptions' => $this->_roles,
        ));
        $this->addElement('checkbox', 'publication_select', array(
            'label' => 'Allow SELECT',
            'value' => '1',
            'class' => 'checkbox'
        ));
        $this->addElement('checkbox', 'publication_update', array(
            'label' => 'Allow UPDATE',
            'class' => 'checkbox'
        ));
        $this->addElement('checkbox', 'publication_insert', array(
            'label' => 'Allow INSERT',
            'class' => 'checkbox'
        ));

        // obtain UCD data and provide a usable form (but only if called from a non scriptable context)
        $ucdStrings = array();
        if($this->_csrfActive === true) {
            foreach ($this->_ucds as $ucd) {
                $ucdStrings[$ucd['word']] = $ucd['word'] . " | " . $ucd['type'] . " | " . $ucd['description'];
            }
        }
        $this->addElement('select', 'ucd_list', array(
            'label' => 'List of UCDs: ',
            'required' => false,
            'multiOptions' => $ucdStrings,
        ));
        $this->addElement('textarea', 'description', array(
            'label' => 'Column description',
            'rows' => '4',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Volatile()),
            )
        ));

        $this->addPrimaryButtonElement('submit', $this->_submit);
        $this->addButtonElement('cancel', 'Cancel');

        // add groups
        $this->addHorizontalGroup(array('table_id', 'name', 'order', 'type', 'unit', 'ucd', 'ucd_list', 'description', 'publication_role_id', 'publication_select', 'publication_update', 'publication_insert'));
        $this->addActionGroup(array('submit', 'cancel'));

        // set fields
        foreach (array('table_id', 'order', 'name', 'type', 'unit', 'ucd', 'description', 'publication_role_id', 'publication_select',
    'publication_update', 'publication_insert') as $element) {
            if (isset($this->_entry[$element])) {
                $this->setDefault($element, $this->_entry[$element]);
            }
        }
        if (isset($this->_table_id)) {
            $this->setDefault('table_id', $this->_table_id);
        }
    }

}