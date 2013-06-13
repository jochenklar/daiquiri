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
 * Abstract base class for all DbTable objects.
 */
abstract class Daiquiri_Model_DbTable_Abstract extends Zend_Db_Table_Abstract {

    /**
     * Set the database adapter from a string or from Zend_Db_Adapter_Abstract.
     * @param type $adapter
     */
    public function setAdapter($adapter) {
        if (is_string($adapter)) {
            $front = Zend_Controller_Front::getInstance();
            $bootstrap = $front->getParam("bootstrap");
            $adapter = $bootstrap->getPluginResource('multidb')->getDb($adapter);
        }
        $this->_setAdapter($adapter);
    }

    /**
     * Get the name of the current database.
     * @return string
     */
    public function getDb() {
        return $this->_schema;
    }

    /**
     * Set the database.
     */
    public function setDb($db) {
        $this->_schema = $db;
    }

    /**
     * Get the name of the current database table.
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Set the database table.
     */
    public function setName($name) {
        $this->_name = $name;
    }

    /**
     * Get the primary key.
     * @return string
     */
    public function getPrimary() {
        return $this->_primary;
    }

    /**
     * Set the primary key.
     */
    public function setPrimary($primary = null) {
        if (empty($primary)) {
            $db = $this->getDb();
            if (empty($db)) {
                $desc = $this->getAdapter()->describeTable($this->getName());
            } else {
                $desc = $this->getAdapter()->describeTable($this->getDb() . "." . $this->getName());
            }

            foreach ($desc as $colname => $col) {
                if ($col['PRIMARY'] === true) {
                    $primary = $colname;
                    break;
                }
            }
        }

        // set the primary key
        if ($primary) {
            $this->setOptions(array('primary' => $primary));
        } else {
            // hack for query result tables without index
            if (array_key_exists('row_id', $desc)) {
                $this->setOptions(array('primary' => 'row_id'));
            } else {
                throw new Exception('no primary key found in ' . $this->getName());
            }
        }
    }

    /**
     * Get the columns of the table.
     */
    public function getCols() {
        $info = $this->info();
        return $info['cols'];
    }

    /**
     * Get the columns of the table.
     */
    public function getColsDirect() {
        $db = $this->getDb();
        if (empty($db)) {
            $desc = $this->getAdapter()->describeTable($this->getName());
        } else {
            $desc = $this->getAdapter()->describeTable($this->getDb() . "." . $this->getName());
        }

        return array_keys($desc);
    }

    /**
     * Constructs a Zend select object from a given array with sql options,
     * using the first (or a specified) database table.
     * @param Array $sqloptions array of sqloptions (start,limit,order,where,from)
     * @return Zend_Db_Select
     */
    public function getSelect($sqloptions = array()) {

        $select = $this->select();

        // set from
        $cols = $this->getCols();

        if (empty($sqloptions['from'])) {
            $select->from($this, $cols);
        } else {
            $from = array_intersect($sqloptions['from'], $cols);
            $select->from($this, $from);
        }

        // set limit
        if (!empty($sqloptions['limit'])) {
            if (!empty($sqloptions['start'])) {
                $start = $sqloptions['start'];
            } else {
                $start = 0;
            }
            $select->limit($sqloptions['limit'], $start);
        }
        // set order
        if (!empty($sqloptions['order'])) {
            $select = $select->order($sqloptions['order']);
        }
        // set where statement
        if (!empty($sqloptions['where'])) {
            foreach ($sqloptions['where'] as $w) {
                $select = $select->where($w);
            }
        }

        return $select;
    }

}
