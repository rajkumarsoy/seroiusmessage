<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
        

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array
        */
        public function __construct()
        {
                parent::__construct();
        }

        public function check_entity($table,$column,$value)
        {                
                $query = $this->db->from($table);
                $query = $this->db->where($column, $value);
                $query = $this->db->get();
                return $query->result();
        }

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array
        */
        public function insert_one_row($table, $data)
        {
                $query = $this->db->insert($table, $data);
                return $this->db->insert_id();
        }

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array    
        */
        public function delete_record_by_id($table, $where)
        {
                return $this->db->delete($table, $where);
        }

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array
        */
        public function get_record_by_id($table, $data)
        {
                $query = $this->db->get_where($table, $data);
                return $query->row();
        }

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array
        */
        public function update_record_by_id($table, $data, $where)
        {
                $query = $this->db->update($table, $data, $where);
                return $this->db->affected_rows();
        }

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array
        */
        public function get_nearby_users($latitude, $longitude, $miles, $user_id)
        {
                $SQL = "SELECT id, (3959 * acos (cos ( radians(".$latitude.") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(".$longitude.") ) + sin ( radians(".$latitude.") ) * sin( radians( latitude ) ) ) ) AS distance FROM users HAVING distance < ".$miles." AND id != $user_id ORDER BY distance";
                $query = $this->db->query($SQL);
                return $query->result_array();
        }

        /**
        * Get data from passed table name with given where condition
        *
        * @param string $table
        * @param array  $where
        *
        * @return array
        */
        public function get_contacts($contacts)
        {
                $query = $this->db->from('users');
                $query = $this->db->where_in('mobile', $contacts);
                $query = $this->db->get();
                
                return $query->result_array();
        }

        

}