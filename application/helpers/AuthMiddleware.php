<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthMiddleware
{

    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->driver('cache');
    }

    public function validate_token()
    {
        $headers = $this->CI->input->get_request_header('Authorization');

        if ($headers && strpos($headers, 'Bearer ') === 0) {
            $token = trim(substr($headers, 7));

            $user_id = 1; // Lấy user_id từ token hoặc cookie

            $cached_token = $this->CI->cache->get('user_token_' . $user_id);

            if ($cached_token && $cached_token === $token) {
                return true;
            }
        }

        return false;
    }

    public function cache_token($user_id, $token)
    {
        // Lưu thông tin token vào cache với thời gian hết hạn (ví dụ: 1 giờ)
        $this->CI->cache->save('user_token_' . $user_id, $token, 3600);
    }
}
