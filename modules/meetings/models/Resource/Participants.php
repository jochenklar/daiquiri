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

class Meetings_Model_Resource_Participants extends Daiquiri_Model_Resource_Simple {

    public function fetchRows() {
        $select = $this->getAdapter()->select();
        $select->from('Meetings_Participants', array('id', 'email'));
        $select->join('Meetings_Meetings', 'Meetings_Meetings.id = Meetings_Participants.meeting_id', array('title'));
        
        return $this->getAdapter()->fetchAll($select);
    }

    public function fetchRow($id) {
        if (empty($id)) {
            throw new Exception('$id not provided in ' . get_class($this) . '::fetchRow()');
        }

        $select = $this->getAdapter()->select();
        $select->from('Meetings_Participants', array('id', 'email', 'meeting_id'));
        $select->where('Meetings_Participants.id = ?', $id);

        $row = $this->getAdapter()->fetchRow($select);

        if (empty($row)) {
            throw new Exception($id . ' not found in ' . get_class($this) . '::fetchRow()');
        }

        return array_merge(
            $row,
            $this->_fetchParticipantDetails($id),
            array('contributions' => $this->_fetchContributions($id))
        );
    }

    public function fetchValues($fieldname, $meetingId) {
        if (empty($fieldname) || empty($meetingId)) {
            throw new Exception('$fieldname or $meetingId not provided in ' . get_class($this) . '::insertRow()');
        }

        // get select object
        $select = $this->getAdapter()->select();
        $select->from('Meetings_Participants', array('id', $fieldname));
        $select->where('meeting_id = ?', $meetingId);

        // query database, construct array, and return
        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            $data[$row['id']] = $row[$fieldname];
        }
        return $data;
    }

    public function insertRow(array $data) {
        if (empty($data)) {
            throw new Exception('$data not provided in ' . get_class($this) . '::insertRow()');
        }

        $contributions = $data['contributions'];
        unset($data['contributions']);

        $details = $data['details'];
        unset($data['details']);

        // insert values
        $this->getAdapter()->insert('Meetings_Participants', $data);

        // get id
        $id = $this->getAdapter()->lastInsertId();

        // create new details for this participant
        $this->_insertParticipantDetails($id, $details);

        // create new contributions for this participant
        $this->_insertContributions($id, $contributions);

        return $id;
    }

    public function updateRow($id, array $data) {
        if (empty($id) || empty($data)) {
            throw new Exception('$id or $data not provided in ' . get_class($this) . '::insertRow()');
        }

        $details = $data['details'];
        unset($data['details']);

        $contributions = $data['contributions'];
        unset($data['contributions']);

        // update the row in the database
        $this->getAdapter()->update('Meetings_Participants', $data, array('id = ?' => $id));

        // delete old and create new details for this participant
        $this->_deleteParticipantDetails($id);
        $this->_insertParticipantDetails($id, $details);

        // delete old and create new contributions for this participant
        $this->_deleteContributions($id);
        $this->_insertContributions($id, $contributions);

        return $id;
    }

    public function deleteRow($id) {
        if (empty($id)) {
            throw new Exception('$id not provided in ' . get_class($this) . '::deleteRow()');
        }

        // delete the row
        $this->getAdapter()->delete('Meetings_Participants', array('id = ?' => $id));

        // delete all details and contributions for this participant
        $this->_deleteParticipantDetails($id);
        $this->_deleteContributions($id);
    }

    private function _fetchParticipantDetails($id) {
        $select = $this->getAdapter()->select();
        $select->from('Meetings_ParticipantDetails', array('value'));
        $select->where('Meetings_ParticipantDetails.participant_id = ?', $id);
        $select->join('Meetings_ParticipantDetailKeys', 'Meetings_ParticipantDetailKeys.id = Meetings_ParticipantDetails.key_id', array('key'));

        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            $data[$row['key']] = $row['value'];
        }
        return $data;
    }

    private function _insertParticipantDetails($id, $details) {
        if (!empty($details)) {
            foreach($details as $key_id => $value) {
                $this->getAdapter()->insert('Meetings_ParticipantDetails', array(
                    'participant_id' => $id,
                    'key_id' =>  $key_id,
                    'value' => $value
                ));
            }
        }
    }

    private function _deleteParticipantDetails($id) {
        $this->getAdapter()->delete('Meetings_ParticipantDetails', array('participant_id = ?' => $id));
    }

    private function _fetchContributions($id) {
        $select = $this->getAdapter()->select();
        $select->from('Meetings_Contributions', array('title','abstract','contribution_type_id'));
        $select->where('Meetings_Contributions.participant_id = ?', $id);
        $select->join('Meetings_ContributionTypes', 'Meetings_ContributionTypes.id = Meetings_Contributions.contribution_type_id', array('contribution_type'));

        $data = array();
        foreach($this->getAdapter()->fetchAll($select) as $row) {
            $data[$row['contribution_type_id']] = array(
                'contribution_type' => $row['contribution_type'],
                'title' => $row['title'],
                'abstract' => $row['abstract']
            );
        }
        return $data;
    }

    private function _insertContributions($id, $contributions) {
        if (!empty($contributions)) {
            foreach($contributions as $contribution_type_id => $contribution) {
                $this->getAdapter()->insert('Meetings_Contributions', array(
                    'participant_id' => $id,
                    'contribution_type_id' =>  $contribution_type_id,
                    'title' => $contribution['title'],
                    'abstract' => $contribution['abstract']
                ));
            }
        }
    }

    private function _deleteContributions($id) {
        $this->getAdapter()->delete('Meetings_Contributions', array('participant_id = ?' => $id));
    }
}