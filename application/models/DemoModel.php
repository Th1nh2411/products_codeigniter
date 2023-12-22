<?php


defined('BASEPATH') or exit('No direct script access allowed');
class DemoModel extends CI_Model
{
    public function get_students()
    {
        $query = $this->db->get('students');
        return $query->result();
    }
    public function checkDatabaseConnection()
    {
        if ($this->db->conn_id) {
            echo "Đã kết nối thành công đến cơ sở dữ liệu.";
        } else {
            echo "Không thể kết nối đến cơ sở dữ liệu.";
        }
    }
}
