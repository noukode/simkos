<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penghuni_model extends CI_Model
{
    private $_table = 'penghuni';

    public function tambah($data)
    {
        return $this->db->insert($this->_table, $data);
    }
    public function update($user_id, $data)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->update($this->_table, $data);
    }
}
