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

class Query_Form_SqlQuery extends Query_Form_AbstractFormQuery {

    /**
     * The default value for the query field.
     * @var string
     */
    protected $_query;

    /**
     * Sets $_query.
     * @param string $query the default value for the query field
     */
    public function setQuery($query) {
        $this->_query = $query;
    }

    /**
     * Gets the SQL query contructed from the form fields.
     * @return string $sql
     */
    public function getQuery() {
        return $this->getValue('sql_query');
    }
    
    /**
     * Gets the content of the tablename field.
     * @return string $tablename
     */
    public function getTablename() {
        return $this->getValue('sql_tablename');
    }

    /**
     * Gets the selected queue.
     * @return string $queue
     */
    public function getQueue() {
        $value = str_replace('sql_queue_', '', $this->getValue('sql_queue_value'));
        return $value;
    }

    /**
     * Initializes the form.
     */
    public function init() {

        // add form elements
        $this->addCsrfElement('sql_csrf');
        $this->addElement('textarea', 'sql_query', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Query:',
            'class' => 'span9',
            'style' => "resize: none;",
            'rows' => 8
        ));
        $this->addTablenameElement('sql_tablename');
        $this->addPrimaryButtonElement('sql_submit', 'Submit new SQL Query');
        $this->addDumbButtonElement('sql_clear', 'Clear input window');
        $this->addQueuesElement('sql_queue');

        // add display groups
        $this->addDivGroup(array('sql_query'), 'sql-input-group');
        $this->addDivGroup(array('sql_tablename'), 'sql-table-group', false, true);
        $this->addInlineGroup(array('sql_submit','sql_clear','sql_queue'), 'sql-button-group');

        // add angular directives
        $this->setAttrib('name',"sql");
        $this->setAttrib('ng-submit',"submitQuery('sql')");
        $this->setDecorators(array(
            'FormElements',
            array('Callback', array(
                'callback' => function($content, $element, $options) {
                    $ngErrorModel = 'errors.sql.form';

                    return '<ul class="unstyled text-error angular-hidden" ng-show="' . $ngErrorModel . '"><li ng-repeat="error in ' . $ngErrorModel . '">Error: {{error}}</li></ul>';
                },
                'placement' => 'append'
            )),
            'Form'
        ));
        foreach (array('sql_csrf','sql_query','sql_tablename') as $name) {
            $element = $this->getElement($name);
            $element->setAttrib('ng-model','values.sql.' . $name);
            $element->addDecorators(array(array('Callback', array(
                'callback' => function($content, $element, $options) {
                    $ngErrorModel = 'errors.sql.' . $element->getName();

                    return '<ul class="unstyled text-error angular-hidden" ng-show="' . $ngErrorModel . '"><li ng-repeat="error in ' . $ngErrorModel . '">Error: {{error}}</li></ul>';
                },
                'placement' => 'append'
            ))));
        }
    }

}
