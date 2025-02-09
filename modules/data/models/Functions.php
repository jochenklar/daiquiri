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

class Data_Model_Functions extends Daiquiri_Model_Table {

    /**
     * Constructor. Sets resource object.
     */
    public function __construct() {
        $this->setResource('Data_Model_Resource_Functions');
    }

    /**
     * Returns all function entries.
     * @return array $response
     */
    public function index() {
        $functions = array();

        foreach($this->getResource()->fetchRows() as $row) {
            $function = $this->getResource()->fetchRow($row['id']);
            $function['publication_role'] = Daiquiri_Auth::getInstance()->getRole($function['publication_role_id']);
            $function['value'] = '/data/functions/show/id/' . $function['id'];
            $functions[] = $function;
        }
        return array('functions' => $functions, 'status' => 'ok');
    }

    /**
     * Creates function entry.
     * @param array $formParams
     * @return array $response
     */
    public function create(array $formParams = array()) {
        // get roles
        $roles = array_merge(array(0 => 'not published'), Daiquiri_Auth::getInstance()->getRoles());

        // create the form object
        $form = new Data_Form_Functions(array(
            'roles' => $roles,
            'submit' => 'Create function entry'
        ));

        // valiadate the form if POST
        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {
                // get the form values
                $values = $form->getValues();

                // check if entry is already there
                if ($this->getResource()->fetchRowByName($values['name']) !== false) {
                    return $this->getModelHelper('CRUD')->validationErrorResponse($form,'Function entry already exists.');
                }

                // check if the order needs to be set to NULL
                if ($values['order'] === '') {
                    $values['order'] = NULL;
                }

                // store the values in the database
                $this->getResource()->insertRow($values);
                return array('status' => 'ok');
            } else {
                return $this->getModelHelper('CRUD')->validationErrorResponse($form);
            }
        }

        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Returns a function entry.
     * @param mixed $input int id or array with "function" key
     * @return array $response
     */
    public function show($input) {
        if (is_int($input)) {
            $row = $this->getResource()->fetchRow($input);
        } elseif (is_array($input)) {
            if (empty($input['function'])) {
                throw new Exception('Either int id or array with "function" key must be provided as $input');
            }
            $row = $this->getResource()->fetchRowByName($input['function']);
        } else {
            throw new Exception('$input has wrong type.');
        }
        
        if (empty($row)) {
            throw new Daiquiri_Exception_NotFound();
        }

        $row['publication_role'] = Daiquiri_Auth::getInstance()->getRole($row['publication_role_id']);

        return array('status' => 'ok','row' => $row);
    }

    /**
     * Updates a function entry.
     * @param mixed $input int id or array with "db","table" and "column" keys
     * @param array $formParams
     * @return array $response
     */
    public function update($input, array $formParams = array()) {
        if (is_int($input)) {
            $entry = $this->getResource()->fetchRow($input);
        } elseif (is_array($input)) {
            if (empty($input['function'])) {
                throw new Exception('Either int id or array with "function" key must be provided as $input');
            }
            $entry = $this->getResource()->fetchRowByName($input['function']);
        } else {
            throw new Exception('$input has wrong type.');
        }

        if (empty($entry)) {
            throw new Daiquiri_Exception_NotFound();
        }

        // get roles
        $roles = $roles = array_merge(array(0 => 'not published'), Daiquiri_Auth::getInstance()->getRoles());

        // create the form object
        $form = new Data_Form_Functions(array(
            'entry' => $entry,
            'roles' => $roles,
            'submit' => 'Update table entry'
        ));

        // valiadate the form if POST
        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {
                // get the form values
                $values = $form->getValues();

                // check if the order needs to be set to NULL
                if ($values['order'] === '') {
                    $values['order'] = NULL;
                }

                $this->getResource()->updateRow($entry['id'], $values);
                return array('status' => 'ok');
            } else {
                return $this->getModelHelper('CRUD')->validationErrorResponse($form);
            }
        }

        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Deletes a function entry.
     * @param mixed $input int id or array with "db","table" and "column" keys
     * @param array $formParams
     * @return array $response
     */
    public function delete($input, array $formParams = array()) {
        if (is_int($input)) {
            $row = $this->getResource()->fetchRow($input);
        } elseif (is_array($input)) {
            if (empty($input['function'])) {
                throw new Exception('Either int id or array with "function" key must be provided as $input');
            }
            $entry = $this->getResource()->fetchRowByName($input['function']);
        } else {
            throw new Exception('$input has wrong type.');
        }

        if (empty($row)) {
            throw new Daiquiri_Exception_NotFound();
        }

        return $this->getModelHelper('CRUD')->delete($row['id'], $formParams);
    }

    /**
     * Returns all functions for export.
     * @return array $response
     */
    public function export() {
        $rows = array();
        foreach($this->getResource()->fetchRows() as $dbRow) {
            $rows[] = array(
                'name' => $dbRow['name'],
                'order' => $dbRow['order'],
                'description' => $dbRow['description'],
                'publication_role' => Daiquiri_Auth::getInstance()->getRole($dbRow['publication_role_id'])
            );
        }

        return array(
            'data' => array('functions' => $rows),
            'status' => 'ok'
        );
    }

}
