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

class Query_Form_Plan extends Daiquiri_Form_Abstract {

    /**
     * The sql query plan.
     * @var string
     */
    protected $_query;

    /**
     * Whether or not the plan can be edited.
     * @var bool
     */
    protected $_editable;

    /**
     * Whether or not a mail link is displayed.
     * @var bool
     */
    protected $_mail;

    /**
     * Sets $_query.
     * @param string $query the sql query plan
     */
    public function setQuery($query) {
        $this->_query = $query;
    }

    /**
     * Sets $_editable.
     * @param bool $editable whether or not the plan can be edited
     */
    public function setEditable($editable) {
        $this->_editable = $editable;
    }

    /**
     * Sets $_mail.
     * @param bool $mail whether or not a mail link is displayed.
     */
    public function setMail($mail) {
        $this->_mail = $mail;
    }

    /**
     * Initializes the form.
     */
    public function init() {
        $this->addCsrfElement('plan_csrf');

        $this->addTextareaElement('plan_query', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'rows' => 12,
            'label' => 'Query:',
            'class' => 'span9 mono codemirror',
            'style' => "resize: none;"
        ));

        if ($this->_mail) {
            $this->addLinkButtonElement('plan_mail', 'Send this plan as a bug report to the Daiquiri developers (opens a new window/tab).');
        }

        if (!$this->_editable) {
            $this->getElement('plan_query')->setAttrib('readonly', 'readonly');
        }

        $this->addSubmitButtonElement('plan_submit', 'Submit this plan');
        $this->addButtonElement('plan_cancel', 'Cancel');

        $this->addSimpleGroup(array('plan_query'), 'input-group');
        $this->addInlineGroup(array('plan_submit', 'plan_cancel'), 'button-group');
        if ($this->_mail) {
            $this->addInlineGroup(array('plan_mail'), 'mail-group');
        }
        if (isset($this->_query)) {
            $this->setDefault('plan_query', $this->_query);
        }
    }

}
