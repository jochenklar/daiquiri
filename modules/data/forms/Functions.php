<?php
/*
 *  Copyright (c) 2012-2015  Jochen S. Klar <jklar@aip.de>,
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

class Data_Form_Functions extends Data_Form_Abstract {

    /**
     * Initializes the form.
     */
    public function init() {
        $this->addCsrfElement();

        // add elements
        $this->addTextElement('name', array(
            'label' => 'Function name',
            'required' => true,
            'class' => 'span6 mono',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Text()),
            )
        ));
        $this->addTextareaElement('description', array(
            'label' => 'Function description',
            'class' => 'span6 mono',
            'rows' => '4',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => new Daiquiri_Form_Validator_Textarea()),
            )
        ));
        $this->addTextElement('order', array(
            'label' => 'Order in list',
            'class' => 'span1 mono',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'int'),
            )
        ));
        $this->addSelectElement('publication_role_id', array(
            'label' => 'Published for',
            'required' => true,
            'multiOptions' => $this->_roles,
        ));
        $this->setDefault('publication_role_id', 0);
        $this->addSubmitButtonElement('submit', $this->_submit);
        $this->addCancelButtonElement('cancel', 'Cancel');

        // add groups
        $this->addHorizontalGroup(array('name','description','order','publication_role_id'));
        $this->addActionGroup(array('submit', 'cancel'));

        // set fields
        foreach (array('order', 'name', 'description', 'publication_role_id') as $element) {
            if (isset($this->_entry[$element])) {
                $this->setDefault($element, $this->_entry[$element]);
            }
        }
    }

}
