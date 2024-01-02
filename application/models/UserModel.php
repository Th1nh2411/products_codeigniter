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
        if ($this->checkEmailExist($data['email'])) {
            return -1;
        };
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
    public function getUsers($keyword, $page = 1, $limit = 5, $sortBy = 'name', $sortOrder = 'asc')
    {
        $this->db->select('users.*');
        $this->db->from('users');
        // Thêm điều kiện tìm kiếm
        if ($keyword) {
            $this->db->group_start(); // Bắt đầu một nhóm điều kiện OR
            $this->db->like('users.name', $keyword);
            $this->db->or_like(
                'users.phone',
                $keyword
            );
            $this->db->or_like('users.user_id', $keyword);
            $this->db->group_end(); // Kết thúc nhóm điều kiện OR
        }
        $this->db->order_by($sortBy, $sortOrder);
        // Đếm số lượng sản phẩm sau khi được lọc
        $total_rows = $this->db->count_all_results('', FALSE);

        // Tính toán vị trí bắt đầu của kết quả phân trang
        $start = ($page - 1) * $limit;

        // Áp dụng phân trang
        $this->db->limit($limit, $start);

        $query = $this->db->get();
        $result['data'] = $query->result();

        // Tính toán tổng số trang
        $total_pages = ceil($total_rows / $limit);

        // Thêm thông tin phân trang vào kết quả
        $result['current_page'] = $page;
        $result['total_pages'] = $total_pages;

        return $result;
    }
}
