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

class Auth_SessionsController extends Daiquiri_Controller_Abstract {

    protected $_model;

    public function init() {
        $this->_model = Daiquiri_Proxy::factory('Auth_Model_Sessions');
    }

    public function indexAction() {
        if (Daiquiri_Auth::getInstance()->checkAcl('Auth_Model_Sessions', 'rows')) {
            $this->view->status = 'ok';
        } else {
            throw new Daiquiri_Exception_Forbidden();
        }
    }

    public function colsAction() {
        $this->getControllerHelper('pagination')->cols();
    }

    public function rowsAction() {
        $this->getControllerHelper('pagination')->rows();
    }

    public function destroyAction() {
        $session = $this->_getParam('session');
        $this->getControllerHelper('form')->destroy($session);
    }

}
