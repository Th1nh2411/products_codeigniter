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
        $this->load->library('Authorization_Token');
    }
    public function login_post()
    {
        $userModel = new UserModel;

        $_POST = json_decode(file_get_contents(
            "php://input"
        ), true);
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        if (!$email) {
            $this->response(['status' => false, 'message' => 'Registration failed. Missing email'], 404);
        }
        if (!$password) {
            $this->response(['status' => false, 'message' => 'Registration failed. Missing password'], 404);
        }
        // Gọi hàm đăng nhập từ model
        $user = $userModel->login($email, $password);

        // Kiểm tra xem người dùng đã đăng nhập thành công hay không
        if ($user) {
            // HANDLE token
            $token_data = array('user_id' => $user['user_id'], 'email' => $user['email'], 'role' => $user['role']);
            $token = $this->authorization_token->generateToken($token_data);
            $cookie_data = array(
                'name'   => 'userInfo',
                'value'  => json_encode(array('user_id' => $user['user_id'], 'email' => $user['email'], 'token' => $token)),
                'expire' => time() + 360000,
                'samesite' => 'None',
                'secure' => true
            );
            $this->input->set_cookie($cookie_data);
            $this->response([
                'result' => true,
                'token' => $token,
                'message' => 'Login successful.'
            ], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Invalid email or password.'], 404);
        }
    }
    public function register_post()
    {
        $userModel = new UserModel;

        $_POST = json_decode(file_get_contents(
            "php://input"
        ), true);
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $password = $this->input->post('password');
        $role = $this->input->post('role') ?: 'user';

        if (!$email) {
            $this->response(['status' => false, 'message' => 'Registration failed. Missing email'], 404);
        }
        if (!$password) {
            $this->response(['status' => false, 'message' => 'Registration failed. Missing password'], 404);
        }
        // Kiểm tra email đã tồn tại hay chưa
        if ($userModel->checkEmailExist($email)) {
            $this->response(['status' => false, 'message' => 'Email already exists.'], 402);
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
