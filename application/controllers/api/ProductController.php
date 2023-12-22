<?php


defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . "libraries/RestController.php";
require APPPATH . "libraries/Format.php";

use chriskacerguis\RestServer\RestController;

class ProductController extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("ProductModel");
    }
    public function getAll_get()
    {
        // Nhận từ khóa tìm kiếm từ tham số URL
        $keyword = $this->input->get('q');


        $productModel = new ProductModel;
        $data = $productModel->get_products($keyword);
        $this->response($data, 200);
    }
    public function getProduct_get($productId)
    {
        $productModel = new ProductModel;
        $product = $productModel->getProductById($productId);

        // Kiểm tra nếu sản phẩm không tồn tại, trả về lỗi 404
        if (!$product) {
            $this->response(['status' => false, 'message' => 'Product not found'], 404);
        }

        // Trả về dữ liệu JSON
        $this->response($product, 200);
    }
}