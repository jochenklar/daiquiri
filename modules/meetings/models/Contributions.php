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

class Meetings_Model_Contributions extends Daiquiri_Model_CRUD {

    public function __construct() {
        $this->setResource('Meetings_Model_Resource_Contributions');

        $this->_options = array(
            'create' => array(
                'form' => 'Meetings_Form_Contribution',
                'submit' => 'Create contribution'
            ),
            'update' => array(
                'form' => 'Meetings_Form_Contribution',
                'submit' => 'Update contribution'
            ),
            'delete' => array(
                'form' => 'Meetings_Form_Delete',
                'submit' => 'Delete contribution'
            ),
        );
    }

public function create($meetingId, array $formParams = array()) {
        // get models
        $meetingsModel = new Meetings_Model_Meetings();
        $participantsModel = new Meetings_Model_Participants();

        // create the form object
        $form = new Meetings_Form_Contribution(array(
            'submit'=> 'Create contribution',
            'meeting' => $meetingsModel->getResource()->fetchRow($meetingId),
            'participants' => $participantsModel->getResource()->fetchValues('email', $meetingId)
        ));

        // valiadate the form if POST
        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {
                // get the form values
                $values = $form->getValues();

                // store the values in the database
                $this->getResource()->insertRow($values);

                return array('status' => 'ok');
            } else {
                return array(
                    'status' => 'error',
                    'errors' => $form->getMessages(),
                    'form' => $form
                );
            }
        }

        return array('form' => $form, 'status' => 'form');
    }

    public function update($id, array $formParams = array()) {
        // get participant from the database
        $entry = $this->getResource()->fetchRow($id);
        if (empty($entry)) {
            throw new Exception('$id ' . $id . ' not found.');
        }

        // get the meeting
        $meetingsModel = new Meetings_Model_Meetings();
        $participantsModel = new Meetings_Model_Participants();

        // create the form object
        $form = new Meetings_Form_Contribution(array(
            'submit'=> 'Update participant',
            'meeting' => $meetingsModel->getResource()->fetchRow($entry['meeting_id']),
            'participants' => $participantsModel->getResource()->fetchValues('email', $entry['meeting_id']),
            'entry' => $entry
        ));

        // valiadate the form if POST
        if (!empty($formParams)) {
            if ($form->isValid($formParams)) {
                // get the form values
                $values = $form->getValues();
                
                // store the values in the database
                $this->getResource()->updateRow($id, $values);

                return array('status' => 'ok');
            } else {
                return array(
                    'status' => 'error',
                    'errors' => $form->getMessages(),
                    'form' => $form
                );
            }
        }

        return array('form' => $form, 'status' => 'form');
    }
    
}