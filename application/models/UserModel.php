<?php


defined('BASEPATH') or exit('No direct script access allowed');
class UserModel extends CI_Model
{
    public function login($email, $password)
    {
        $this->db->where('email', $email);
        $query = $this->db->get('users');

        if ($query->num_rows() === 1) {
            $user = $query->row_array();

            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                return $user; // Trả về thông tin người dùng nếu đăng nhập thành công
            }
        }

        return false;
    }
    public function createUser($data)
    { // Insert thông tin người dùng mới vào bảng 'users'
        $this->db->insert('users', $data);
        return $this->db->insert_id(); // Trả về ID của người dùng mới
    }

    public function checkEmailExist($email)
    {
        // Kiểm tra xem email đã tồn tại trong bảng 'users' hay chưa
        $this->db->where('email', $email);
        $query = $this->db->get('users');
        return $query->num_rows() > 0;
    }
}