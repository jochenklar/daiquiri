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
 * Model for the user management.
 */
class Auth_Model_User extends Daiquiri_Model_PaginatedTable {

    /**
     * Possible options for each user.
     * @var array 
     */
    private $_options = array(
        'Show' => array(
            'url' => '/auth/user/show',
            'permission' => 'show',
            'resource' => 'Auth_Model_User'
        ),
        'Update' => array(
            'url' => '/auth/user/update',
            'permission' => 'update',
            'resource' => 'Auth_Model_User'
        ),
        'Delete' => array(
            'url' => '/auth/user/delete',
            'permission' => 'delete',
            'resource' => 'Auth_Model_User'
        ),
        'Confirm' => array(
            'url' => '/auth/registration/confirm',
            'permission' => 'confirm',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('registered')
        ),
        'Reject' => array(
            'url' => '/auth/registration/reject',
            'permission' => 'reject',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('registered')
        ),
        'Activate' => array(
            'url' => '/auth/registration/activate',
            'permission' => 'activate',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('registered', 'confirmed')
        ),
        'Disable' => array(
            'url' => '/auth/registration/disable',
            'permission' => 'disable',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('active')
        ),
        'Reenable' => array(
            'url' => '/auth/registration/reenable',
            'permission' => 'reenable',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('disabled')
        ),
        'Password' => array(
            'url' => '/auth/password/set',
            'permission' => 'set',
            'resource' => 'Auth_Model_Password'
        )
    );

    /**
     * Default columns to be returned in cols/rows.
     * @var array 
     */
    private $_cols = array('id', 'username', 'email', 'role', 'status');

    /**
     * Construtor. Sets resource.
     */
    public function __construct() {
        $this->setResource('Auth_Model_Resource_User');
    }

    /**
     * Returns the main data of the user table.
     * @return array 
     */
    public function rows(array $params = array()) {
        // set default columns
        if (empty($params['cols'])) {
            $params['cols'] = $this->_cols;
        }

        // get the table from the resource
        $sqloptions = $this->_sqloptions($params);
        $rows = $this->getResource()->fetchRows($sqloptions);
        $response = $this->_response($rows, $sqloptions);

        // get the right columm for the status
        $statusCol = array_search('status', $params['cols']);

        // loop through the table and add options
        if (isset($params['options']) && $params['options'] === 'true') {
            for ($i = 0; $i < sizeof($response->rows); $i++) {
                $id = $response->rows[$i]['id'];
                $links = '';

                $status = null;
                if (!empty($statusCol)) {
                    $status = $response->rows[$i]["cell"][$statusCol];
                }

                foreach ($this->_options as $key => $value) {
                    if ($status !== null &&
                            isset($value['prerequisites']) &&
                            !in_array($status, $value['prerequisites'])) {
                        // pass
                    } else {
                        $links .= $this->internalLink(array(
                            'text' => $key,
                            'href' => $value['url'] . '/id/' . $id,
                            'resource' => $value['resource'],
                            'permission' => $value['permission'],
                            'append' => '&nbsp;'));
                    }
                }

                $response->rows[$i]["cell"][] = $links;
            }
        }

        return $response;
    }

    /**
     * Returns the columns for the index.
     * @return array 
     */
    public function cols(array $params = array()) {
        // set default columns
        if (empty($params['cols'])) {
            $params['cols'] = $this->_cols;
        }

        $cols = array();
        foreach ($params['cols'] as $name) {
            $col = array('name' => $name);
            if ($name === 'id') {
                $col['width'] = '20em';
                $col['align'] = 'center';
            } else if ($name === 'username') {
                $col['width'] = '80em';
            } else if ($name === 'email') {
                $col['width'] = '160em';
            } else if ($name === 'role') {
                $col['width'] = '60em';
            } else if ($name === 'status') {
                $col['width'] = '60em';
            } else {
                $col['width'] = '80em';
            }
            $cols[] = $col;
        }

        if (isset($params['options']) && $params['options'] === 'true') {
            $cols[] = array(
                'name' => 'options',
                'width' => '300em',
                'sortable' => 'false'
            );
        }

        return $cols;
    }

    /**
     * Returns the credentials of a given user from the database.
     * @return array 
     */
    public function show($id) {
        return $this->getResource()->fetchRow($id);
    }

    /**
     * Creates a new user.
     * @param array $formParams
     * @return Object
     */
    public function create(array $formParams = array()) {
        // get the status and the roles model
        $statusModel = new Auth_Model_Status();
        $roleModel = new Auth_Model_Roles();

        // create the form object
        $form = new Auth_Form_Create(array(
                    'details' => Daiquiri_Config::getInstance()->auth->details->toArray(),
                    'status' => $statusModel->getValues(),
                    'roles' => $roleModel->getValues()
                ));

        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {

                // get the form values
                $values = $form->getValues();

                // unset some elements
                unset($values['confirmPassword']);

                // create the user
                $id = $this->getResource()->storeUser($values);

                // log the event
                $detailsResource = new Auth_Model_Resource_Details();
                $detailsResource->logEvent($id, 'create');

                return array('status' => 'ok');
            } else {
                return array('form' => $form, 'status' => 'validation failed');
            }
        }
        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Updates an existing user.
     * @param int $id
     * @param array $formParams
     * @return Object
     */
    public function update($id, array $formParams = array()) {
        // get the status and the roles model
        $statusModel = new Auth_Model_Status();
        $roleModel = new Auth_Model_Roles();

        // create the form object
        $form = new Auth_Form_Update(array(
                    'user' => $this->getResource()->fetchRow($id),
                    'details' => Daiquiri_Config::getInstance()->auth->details->toArray(),
                    'status' => $statusModel->getValues(),
                    'roles' => $roleModel->getValues(),
                    'changeUsername' => Daiquiri_Config::getInstance()->auth->changeUsername,
                    'changeEmail' => Daiquiri_Config::getInstance()->auth->changeEmail,
                ));

        if (!empty($formParams) && $form->isValid($formParams)) {
            // get the form values
            $values = $form->getValues();

            // update the user and redirect
            $this->getResource()->updateUser($id, $values);

            // log the event
            $detailsResource = new Auth_Model_Resource_Details();
            $detailsResource->logEvent($id, 'update');

            return array('status' => 'ok');
        }

        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Edits the credentials of the currently logged in user.
     * @param array $formParams
     * @return array
     */
    public function edit(array $formParams = array()) {
        // get id
        $id = Daiquiri_Auth::getInstance()->getCurrentId();

        // create the form object
        $form = new Auth_Form_Edit(array(
                    'user' => $this->getResource()->fetchRow($id),
                    'details' => Daiquiri_Config::getInstance()->auth->details->toArray(),
                    'changeUsername' => Daiquiri_Config::getInstance()->auth->changeUsername,
                    'changeEmail' => Daiquiri_Config::getInstance()->auth->changeEmail,
                ));

        if (!empty($formParams) && $form->isValid($formParams)) {
            // get the form values
            $values = $form->getValues();

            // update the user and redirect
            $this->getResource()->updateUser($id, $values);

            // log the event
            $detailsResource = new Auth_Model_Resource_Details();
            $detailsResource->logEvent($id, 'edit');

            return array('status' => 'ok');
        }

        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Deletes an existing user.
     * @param int $id
     * @param array $formParams
     * @return array 
     */
    public function delete($id, array $formParams = array()) {
        // create the form object
        $form = new Auth_Form_Delete();

        // valiadate the form if POST
        if (!empty($formParams) && $form->isValid($formParams)) {

            // get the form values
            $values = $form->getValues();

            // update the user and redirect
            if ($values['submit']) {
                $this->getResource()->deleteUser($id);

                // invalidate the session of the user
                $resource = new Auth_Model_Resource_Sessions();
                foreach ($resource->fetchAuthSessionsByUserId($id) as $session) {
                    $resource->deleteRow($session);
                };
            }

            return array('status' => 'ok');
        }

        return array('form' => $form, 'status' => 'form');
    }

}
