<?php


defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . "libraries/RestController.php";
require APPPATH . "libraries/Format.php";

use chriskacerguis\RestServer\RestController;

class UserController extends RestController
{
    public function __construct()
    {

        parent::__construct();
        $this->load->model("UserModel");
        // $this->load->library('AuthMiddleware');
    }
    public function login_post()
    {
        $userModel = new UserModel;
        // $authMiddleware = new AuthMiddleware;
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // Gọi hàm đăng nhập từ model
        $user = $userModel->login($email, $password);

        // Kiểm tra xem người dùng đã đăng nhập thành công hay không
        if ($user) {
            // $user_id = $user['id']; // Lấy user_id từ đăng nhập
            // $expires_at = 3600; // Tính toán thời gian hết hạn của token

            // $token = $authMiddleware->generate_token($user_id, $expires_at);

            // // Lưu thông tin token vào cache
            // $authMiddleware->cache_token($user_id, $token);

            $this->response([
                'result' => true,
                // 'token' => $token, 
                'user' => $user,
                'message' => 'Login successful.'
            ], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Login failed. Invalid email or password.'], 500);
        }
    }
    public function register_post()
    {
        $userModel = new UserModel;
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $password = $this->input->post('password');
        $role = $this->input->post('role') ?: 'user';

        // Kiểm tra email đã tồn tại hay chưa
        if (!$email || !$password) {
            $this->response(['status' => false, 'message' => 'Registration failed. Missing email or password'], 500);
        }
        if ($userModel->checkEmailExist($email)) {
            $this->response(['status' => false, 'message' => 'Email already exists.'], 500);
            return;
        }

        // Hash mật khẩu trước khi lưu vào cơ sở dữ liệu
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Dữ liệu người dùng
        $user_data = [
            'email' => $email,
            'phone' => $phone,
            'password' => $hashed_password,
            'role' => $role
        ];

        // Gọi hàm đăng ký từ model
        $user_id = $userModel->createUser($user_data);

        // Kiểm tra xem người dùng đã đăng ký thành công hay không
        if ($user_id) {
            $this->response([
                'result' => true, 'user_id' => $user_id,
                'message' => 'Registration successful.'
            ], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Registration failed.'], 500);
        }
    }
}
