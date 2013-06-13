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

class Query_Form_RangeQuery extends Query_Form_AbstractFormQuery {

    public function getQuery() {
        if (!isset($this->_formOptions['table'])) {
            throw new Exception('no table was specified');
        }

        $sql = "SELECT * FROM {$this->_formOptions['table']}";
        $sql .= $this->_quoteInto(" WHERE `x` >= ?", $this->getValue('range_xmin'));
        $sql .= $this->_quoteInto(" AND `x` <= ?", $this->getValue('range_xmax'));
        $sql .= $this->_quoteInto(" AND `y` >= ?", $this->getValue('range_ymin'));
        $sql .= $this->_quoteInto(" AND `y` <= ?", $this->getValue('range_ymax'));
        $sql .= $this->_quoteInto(" AND `z` >= ?", $this->getValue('range_zmin'));
        $sql .= $this->_quoteInto(" AND `z` <= ?", $this->getValue('range_zmax'));

        return $sql;
    }

    public function getTablename() {
        return $this->getValue('range_tablename');
    }

    public function getQueue() {
        return $this->getValue('range_queue');
    }

    public function init() {

        $this->setAttrib('id', 'daiquiri-form-range-query');

        $this->addElement('text', 'range_xmin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('validator' => 'float')
            ),
            'label' => 'x<sub>min</sub>',
            'class' => 'span2'
        ));
        $this->addElement('text', 'range_xmax', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('validator' => 'float')
            ),
            'label' => 'x<sub>max</sub>',
            'class' => 'span2'
        ));
        $this->addElement('text', 'range_ymin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('validator' => 'float')
            ),
            'label' => 'y<sub>min</sub>',
            'class' => 'span2'
        ));
        $this->addElement('text', 'range_ymax', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('validator' => 'float')
            ),
            'label' => 'y<sub>max</sub>',
            'class' => 'span2'
        ));
        $this->addElement('text', 'range_zmin', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('validator' => 'float')
            ),
            'label' => 'z<sub>min</sub>',
            'class' => 'span2'
        ));
        $this->addElement('text', 'range_zmax', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('validator' => 'float')
            ),
            'label' => 'z<sub>max</sub>',
            'class' => 'span2'
        ));
        $this->addElement('text', 'range_tablename', array(
            'filters' => array(
                'StringTrim',
                array('PregReplace', array('match' => '/ /', 'replace' => '_'))
            ),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 128)),
                array('validator' => 'Regex', 'options' => array('pattern' => '/^[^;@%*?()!"`\'&]+$/'))
            ),
            'label' => 'Name of the new table',
            'class' => 'span9'
        ));

        // add fields
        $this->addPrimaryButtonElement('range_submit', 'Submit new Range Query');

        // add groups
        $this->addViewScriptGroup(array('range_xmin', 'range_xmax', 'range_ymin', 'range_ymax', 'range_zmin', 'range_zmax'), '_forms/range.phtml');
        $this->addParagraphGroup(array('range_tablename'), 'table-group', false, true);
        $this->addInlineGroup(array('range_submit'), 'button-group');

        if (isset($this->_tablename)) {
            $this->setDefault('range_tablename', $this->_tablename);
        }
    }

}
