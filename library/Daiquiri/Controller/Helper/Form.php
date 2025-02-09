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

class Daiquiri_Controller_Helper_Form extends Daiquiri_Controller_Helper_Abstract {

    private $_redirect;

    public function __construct($controller, array $options = array()) {
        parent::__construct($controller);

        // get the module and the controller from the request
        $request = $this->getRequest();
        $module  = $request->module;
        $controller = $request->controller;

        // construct default options
        if (isset($options['redirect'])) {
            $this->_redirect = $options['redirect'];
        } else {
            $this->_redirect = '/' . $module . '/' . $controller . '/';
        }
    }

    public function __call($methodname, array $arguments) {
        // get params
        $redirect = $this->getParam('redirect', $this->_redirect);

        // check if POST or GET
        if ($this->getRequest()->isPost()) {
            if ($this->getParam('cancel')) {
                // user clicked cancel
                $this->getController()->redirect($redirect);
            } else {
                // validate form and do stuff
                $response = call_user_func_array(
                    array($this->getModel(),$methodname),
                    array_merge($arguments, array($this->getRequest()->getPost()))
                );
            }
        } else {
            // just display the form
            $response = call_user_func_array(array($this->getModel(),$methodname),$arguments);
        }

        // assign to view
        $this->getView()->redirect = $redirect;
        $this->getView()->assign($response);
    }
}