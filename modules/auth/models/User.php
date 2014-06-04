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

class Auth_Model_User extends Daiquiri_Model_Table {

    /**
     * Possible options for each user.
     * @var array $_options
     */
    private $_options = array(
        'Show' => array(
            'url' => '/auth/user/show',
            'class' => 'daiquiri-user-show',
            'permission' => 'show',
            'resource' => 'Auth_Model_User'
        ),
        'Update' => array(
            'url' => '/auth/user/update',
            'class' => 'daiquiri-user-update',
            'permission' => 'update',
            'resource' => 'Auth_Model_User'
        ),
        'Delete' => array(
            'url' => '/auth/user/delete',
            'class' => 'daiquiri-user-delete',
            'permission' => 'delete',
            'resource' => 'Auth_Model_User'
        ),
        'Confirm' => array(
            'url' => '/auth/registration/confirm',
            'class' => 'daiquiri-user-confirm',
            'permission' => 'confirm',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('registered')
        ),
        'Reject' => array(
            'url' => '/auth/registration/reject',
            'class' => 'daiquiri-user-reject',
            'permission' => 'reject',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('registered')
        ),
        'Activate' => array(
            'url' => '/auth/registration/activate',
            'class' => 'daiquiri-user-activate',
            'permission' => 'activate',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('registered', 'confirmed')
        ),
        'Disable' => array(
            'url' => '/auth/registration/disable',
            'class' => 'daiquiri-user-disable',
            'permission' => 'disable',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('active')
        ),
        'Reenable' => array(
            'url' => '/auth/registration/reenable',
            'class' => 'daiquiri-user-reenable',
            'permission' => 'reenable',
            'resource' => 'Auth_Model_Registration',
            'prerequisites' => array('disabled')
        ),
        'Password' => array(
            'url' => '/auth/password/set',
            'class' => 'daiquiri-user-password',
            'permission' => 'set',
            'resource' => 'Auth_Model_Password'
         )
    );

    /**
     * Constructor. Sets resource object and cols.
     */
    public function __construct() {
        $this->setResource('Auth_Model_Resource_User');
        $this->_cols = array('id', 'username', 'email', 'role', 'status');
    }

    /**
     * Returns the columns of the user table specified by some parameters. 
     * @param array $params get params of the request
     * @return array $response
     */
    public function cols(array $params = array()) {
        $cols = array();
        foreach ($this->_cols as $colname) {
            $col = array(
                'name' => $colname,
                'sortable' => 'true'
            );
            if ($colname === 'id') {
                $col['width'] = '10px';
            } else if ($colname === 'email') {
                $col['width'] = '120px';
            } else if ($colname === 'role') {
                $col['width'] = '40px';
            } else if ($colname === 'status') {
                $col['width'] = '40px';
            } else {
                $col['width'] = '100px';
            }
            $cols[] = $col;
        }
        $cols[] = array(
            'name' => 'options',
            'width' => '200px',
            'sortable' => 'false'
        );
        
        return array('cols' => $cols, 'status' => 'ok');
    }

    /**
     * Returns the rows of the user table specified by some parameters. 
     * @param array $params get params of the request
     * @return array $response
     */
    public function rows(array $params = array()) {
        // parse params
        $sqloptions = $this->getModelHelper('pagination')->sqloptions($params);

        // get the data from the database
        $dbRows = $this->getResource()->fetchRows($sqloptions);

        // loop through the table and add an options to destroy the session
        $rows = array();
        foreach ($dbRows as $dbRow) {
            $row = array();
            foreach ($this->_cols as $col) {
                $row[] = $dbRow[$col];
            }

            $status = $dbRow['status'];
        
            $options = array();
            foreach ($this->_options as $key => $value) {
                if ($status !== null &&
                    isset($value['prerequisites']) &&
                    !in_array($status, $value['prerequisites'])) {
                    // pass
                } else {
                    $option = $this->internalLink(array(
                        'text' => $key,
                        'href' => $value['url'] . '/id/' . $dbRow['id'],
                        'resource' => $value['resource'],
                        'permission' => $value['permission'],
                        'class' => $value['class']));
                    if (!empty($option)) {
                       $options[] = $option;
                    }
                }
            }
            $row[] = implode('&nbsp;',$options);

            $rows[] = $row;
        }

        return $this->getModelHelper('pagination')->response($rows, $sqloptions);
    }

    /**
     * Returns the credentials of a given user from the database.
     * @param int $id id of the user
     * @return array $response
     */
    public function show($id) {
        return $this->getModelHelper('CRUD')->show($id);
    }

    /**
     * Creates a new user.
     * @param array $formParams
     * @return array $response
     */
    public function create(array $formParams = array()) {
        // get the status model, the roles model and the roles
        $status = Daiquiri_Auth::getInstance()->getStatus();
        $roles = Daiquiri_Auth::getInstance()->getRoles();
        unset($roles[1]); // unset the guest user

        // create the form object
        $form = new Auth_Form_CreateUser(array(
            'details' => Daiquiri_Config::getInstance()->auth->details->toArray(),
            'status' => $status,
            'roles' => $roles
        ));

        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {

                // get the form values
                $values = $form->getValues();

                // unset some elements
                unset($values['confirmPassword']);

                // create the user
                $id = $this->getResource()->insertRow($values);

                // log the event
                $detailsResource = new Auth_Model_Resource_Details();
                $detailsResource->logEvent($id, 'create');

                return array('status' => 'ok');
            } else {
                return $this->getModelHelper('CRUD')->validationErrorResponse($form);
            }
        }
        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Updates an existing user.
     * @param int $id id of the user
     * @param array $formParams
     * @return array $response
     */
    public function update($id, array $formParams = array()) {
        // get the status model, the roles model and the roles
        $status = Daiquiri_Auth::getInstance()->getStatus();
        $roles = Daiquiri_Auth::getInstance()->getRoles();
        unset($roles[1]); // unset the guest user

        // get user
        $user = $this->getResource()->fetchRow($id);
        if (empty($user)) {
            throw new Daiquiri_Exception_NotFound();
        }

        // create the form object
        $form = new Auth_Form_UpdateUser(array(
            'details' => Daiquiri_Config::getInstance()->auth->details->toArray(),
            'status' => $status,
            'roles' => $roles,
            'changeUsername' => Daiquiri_Config::getInstance()->auth->changeUsername,
            'changeEmail' => Daiquiri_Config::getInstance()->auth->changeEmail,
            'user' => $user
        ));

        // check if request is POST
        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {
                // get the form values
                $values = $form->getValues();

                // update the user and redirect
                $this->getResource()->updateRow($id, $values);

                // log the event
                $detailsResource = new Auth_Model_Resource_Details();
                $detailsResource->logEvent($id, 'update');

                return array('status' => 'ok');
            } else {
                return $this->getModelHelper('CRUD')->validationErrorResponse($form);
            }
        }

        return array('form' => $form, 'status' => 'form');
    }

    /**
     * Deletes an existing user.
     * @param int $id id of the user
     * @param array $formParams
     * @return array $response
     */
    public function delete($id, array $formParams = array()) {
        // create the form object
        $form = new Daiquiri_Form_Danger(array(
            'submit' => 'Delete user'
        ));

        // valiadate the form if POST
        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {
                // get the form values
                $values = $form->getValues();

                // delete the user and redirect
                $this->getResource()->deleteRow($id);

                // invalidate the session of the user
                $resource = new Auth_Model_Resource_Sessions();
                foreach ($resource->fetchAuthSessionsByUserId($id) as $session) {
                    $resource->deleteRow($session);
                };
                
                return array('status' => 'ok');
            } else {
                return $this->getModelHelper('CRUD')->validationErrorResponse($form);
            }
        }

        return array('form' => $form, 'status' => 'form');
    }

}
