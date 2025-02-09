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

class Meetings_Model_ParticipantStatus extends Daiquiri_Model_Table {

    /**
     * Constructor. Sets resource and tablename.
     */
    public function __construct() {
        $this->setResource('Daiquiri_Model_Resource_Table');
        $this->getResource()->setTablename('Meetings_ParticipantStatus');
    }

    /**
     * Returns all participants status
     * @return array $response
     */
    public function index() {
        return $this->getModelHelper('CRUD')->index();
    }

    /**
     * Returns one specific participant status.
     * @param int $id id of the participant status
     * @return array $response
     */
    public function show($id) {
        return $this->getModelHelper('CRUD')->show($id);
    }

    /**
     * Creates a new particpant status.
     * @param array $formParams
     * @return array $response
     */
    public function create(array $formParams = array()) {
        return $this->getModelHelper('CRUD')->create($formParams, 'Create participant status');
    }

    /**
     * Updates an participant status.
     * @param int $id id of the participant status
     * @param array $formParams
     * @return array $response
     */
    public function update($id, array $formParams = array()) {
        return $this->getModelHelper('CRUD')->update($id, $formParams, 'Update participant status');
    }

    /**
     * Deletes a participant status.
     * @param int $id id of the participant status
     * @param array $formParams
     * @return array $response
     */
    public function delete($id, array $formParams = array()) {
        return $this->getModelHelper('CRUD')->delete($id, $formParams, 'Delete participant status');
    }
}