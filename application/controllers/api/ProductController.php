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
    public function getProducts_get()
    {
        // Nhận từ khóa tìm kiếm từ tham số URL
        $keyword = $this->input->get('q');


        $productModel = new ProductModel;
        $data = $productModel->get_products($keyword);
        $this->response($data, 200);
    }
    public function getProductById_get($productId)
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
    public function getCategories_get()
    {
        // Nhận từ khóa tìm kiếm từ tham số URL


        $productModel = new ProductModel;
        $data = $productModel->get_categories();
        $this->response($data, 200);
    }
    public function getProductsByCategory_get($categoryId)
    {
        $productModel = new ProductModel;
        $product = $productModel->getProductsByCategory($categoryId);

        // Kiểm tra nếu sản phẩm không tồn tại, trả về lỗi 404
        if (!$product) {
            $this->response(['status' => false, 'message' => 'Product not found'], 404);
        }

        // Trả về dữ liệu JSON
        $this->response($product, 200);
    }
    public function createProduct_post()
    {
        $productModel = new ProductModel;
        $data =
            $this->input->post();
        $result = $productModel->createProduct($data);

        if (!$result) {
            $this->response(['status' => false, 'message' => 'Create failed'], 500);
        }

        $this->response($result, 200);
    }
    public function updateProduct_put($id = null)
    {
        $productModel = new ProductModel;
        // Kiểm tra xem ID có tồn tại không
        if ($id === null) {
            $this->response(['message' => 'Invalid product ID'], 400);
        }

        // Lấy dữ liệu từ request body
        $data = $this->put();
        // Validate dữ liệu nếu cần

        // Gọi hàm update trong model để thực hiện cập nhật
        $result = $productModel->updateProduct($id, $data);

        if ($result) {
            $this->response(['message' => 'Product updated successfully'], 200);
        } else {
            $this->response(['message' => 'Failed to update product'], 500);
        }
    }
    public function deleteProduct_delete($id)
    {
        $productModel = new ProductModel;
        $result
            = $productModel->deleteProduct($id);

        if ($result) {
            // Xóa thành công
            $this->response(['message' => 'Product deleted successfully'], 200);
        } else {
            // Xóa thất bại hoặc không tìm thấy sản phẩm
            $this->response(['message' => 'Failed to delete product'], 500);
        }
    }
}