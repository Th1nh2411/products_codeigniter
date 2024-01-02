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
            $user_data = [
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'name' => $user['name'],
                'role' => $user['role'],
                'token' => $token,
            ];
            $cookie_data = array(
                'name'   => 'userInfo',
                'value'  => json_encode($user_data),
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
        $name = $this->input->post('name');
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
            'name' => $name,
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
        } else if ($user_id === -1) {
            $this->response(['status' => false, 'message' => 'This email is already registered.'], 400);
        } else {
            $this->response(['status' => false, 'message' => 'Registration failed.'], 500);
        }
    }
    public function getUsers_get()
    {
        // Nhận từ khóa tìm kiếm từ tham số URL
        $resultValidate = $this->authorization_token->validateToken('admin');
        $keyword = $this->input->get('q');

        // Nhận trang và giới hạn từ tham số URL (mặc định là trang 1 và giới hạn 5 sản phẩm mỗi trang)
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 5;
        // Handle sort query
        $sort = $this->input->get('sort') ?: 'name';
        $sortBy = $sort[0] == '-' ?  substr($sort, 1) : $sort;
        $sortOrder = $sort[0] == '-' ? 'DESC' : 'ASC';

        $userModel = new UserModel;
        $data = $userModel->getUsers($keyword, $page, $limit, $sortBy, $sortOrder);
        $this->response($data, 200);
    }
}
