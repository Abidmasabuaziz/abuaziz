<?php

class Model_master_data extends CI_Model
{
    public function custom_query($query) 
    {
        $sql = $this->db->query($query);
        return $sql;
    }

    public function get($table, $field)
    {
        $this->db->select($field);
        $sql = $this->db->get($table);
        return $sql->result();
    }

    public function get_where($table, $field, $condition)
    {
        $this->db->select($field);
        $this->db->where($condition);
        $sql = $this->db->get($table);
        if($sql){
            return $sql->result();   
        }
        else{
            return false;
        }
    }
    
    public function insert($table, $data) 
    {
        $q = $this->db->insert($table, $data);
        return $q;
    }
    
    public function update($table, $data, $condition) {
        $this->db->where($condition);
        return $this->db->update($table, $data);
		
    }
    
    public function delete($table, $condition) 
    {
        $this->db->where($condition);
        return $this->db->delete($table);
    }
}