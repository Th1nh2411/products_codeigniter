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
        $this->load->library("Authorization_Token");
    }


    public function getProducts_get()
    {
        // Nhận từ khóa tìm kiếm từ tham số URL
        $keyword = $this->input->get('q');

        // Nhận trang và giới hạn từ tham số URL (mặc định là trang 1 và giới hạn 5 sản phẩm mỗi trang)
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 5;
        // Handle sort query
        $sort = $this->input->get('sort') ?: 'title';
        $sortBy = $sort[0] == '-' ?  substr($sort, 1) : $sort;
        $sortOrder = $sort[0] == '-' ? 'DESC' : 'ASC';
        // Handle filter query
        $discount = $this->input->get('discount');
        $new = $this->input->get('new');

        $productModel = new ProductModel;
        $data = $productModel->getProducts($keyword, $page, $limit, null, $sortBy, $sortOrder, $discount, $new);
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

    public function getProductsByCategory_get($categoryId)
    {
        // Nhận từ khóa tìm kiếm từ tham số URL
        $keyword = $this->input->get('q');

        // Nhận trang và giới hạn từ tham số URL (mặc định là trang 1 và giới hạn 5 sản phẩm mỗi trang)
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 5;
        // Handle sort query
        $sort = $this->input->get('sort') ?: 'title';
        $sortBy = $sort[0] == '-' ?  substr($sort, 1) : $sort;
        $sortOrder = $sort[0] == '-' ? 'DESC' : 'ASC';
        // Handle filter query
        $discount = $this->input->get('discount');
        $new = $this->input->get('new');
        $productModel = new ProductModel;
        $data = $productModel->getProducts($keyword, $page, $limit, $categoryId, $sortBy, $sortOrder, $discount, $new);
        $this->response($data, 200);
    }
    public function createProduct_post()
    {
        $resultValidate = $this->authorization_token->validateToken('admin');
        $productModel = new ProductModel;
        $_POST = json_decode(file_get_contents(
            "php://input"
        ), true);
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
        $resultValidate = $this->authorization_token->validateToken('admin');
        // if (!$resultValidate['status']) {
        //     $this->response($resultValidate, 401);
        // }


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
        $resultValidate = $this->authorization_token->validateToken('admin');
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
    public function getCategories_get()
    {
        // Nhận từ khóa tìm kiếm từ tham số URL


        $productModel = new ProductModel;
        $data = $productModel->get_categories();
        $this->response($data, 200);
    }
    // -------------------------------------------------------------PRODUCT IMAGE HANDLE-----------------------------------------------------------------
    public function getProductImages_get($id)
    {
        // Nhận từ khóa tìm kiếm từ tham số URL


        $productModel = new ProductModel;
        $data = $productModel->getProductImage($id);
        $this->response($data, 200);
    }
    public function createProductImage_post()
    {
        $resultValidate = $this->authorization_token->validateToken('admin');
        $productModel = new ProductModel;
        $_POST = json_decode(file_get_contents("php://input"), true);
        $data =
            $this->input->post();
        $result = $productModel->createProductImage($data);
        if (!$result) {
            $this->response(['status' => false, 'message' => 'Create failed'], 500);
        }

        $this->response($result, 200);
    }
    public function updateProductImage_put($id = null)
    {
        $resultValidate = $this->authorization_token->validateToken('admin');
        $productModel = new ProductModel;
        // Kiểm tra xem ID có tồn tại không
        if ($id === null) {
            $this->response(['message' => 'Invalid product ID'], 400);
        }

        // Lấy dữ liệu từ request body
        $data = $this->put();
        // Validate dữ liệu nếu cần

        // Gọi hàm update trong model để thực hiện cập nhật
        $result = $productModel->updateProductImage($id, $data);

        if ($result) {
            $this->response(['message' => 'Product updated successfully'], 200);
        } else {
            $this->response(['message' => 'Failed to update product'], 500);
        }
    }
    public function deleteProductImage_delete($id)
    {
        $resultValidate = $this->authorization_token->validateToken('admin');
        $productModel = new ProductModel;
        $result
            = $productModel->deleteProductImage($id);

        if ($result) {
            // Xóa thành công
            $this->response(['message' => 'Product deleted successfully'], 200);
        } else {
            // Xóa thất bại hoặc không tìm thấy sản phẩm
            $this->response(['message' => 'Failed to delete product'], 500);
        }
    }
}
