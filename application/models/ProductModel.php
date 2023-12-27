<?php


defined('BASEPATH') or exit('No direct script access allowed');
class ProductModel extends CI_Model
{
    public function getProducts($keyword, $page = 1, $limit = 5, $categoryId = null, $sortBy = 'title', $sortOrder = 'asc')
    {
        $this->db->select('products.*, (price - (price * discountPercentage)/100) as calculatedPrice');
        $this->db->from('products');
        $this->db->join('product_images', 'products.id = product_images.product_id', 'left');
        $this->db->join('categories', 'products.category_id = categories.id', 'left');

        // Thêm điều kiện lọc theo categoryId
        if ($categoryId) {
            $this->db->where('products.category_id', $categoryId);
        }

        // Thêm điều kiện tìm kiếm
        if ($keyword) {
            $this->db->group_start(); // Bắt đầu một nhóm điều kiện OR
            $this->db->like('products.title', $keyword);
            $this->db->or_like('products.description', $keyword);
            $this->db->group_end(); // Kết thúc nhóm điều kiện OR
        }
        $this->db->group_by('products.id');
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


    public function getProductById($productId)
    {
        // Lấy chi tiết sản phẩm từ bảng products
        $productQuery = $this->db->get_where('products', ['id' => $productId]);
        $product = $productQuery->row();

        // Nếu sản phẩm không tồn tại, trả về NULL
        if (!$product) {
            return null;
        }

        // Lấy tất cả hình ảnh liên quan đến sản phẩm từ bảng product_images


        // Lấy tên category từ id
        $categoryQuery = $this->db->get_where('categories', ['id' => $product->category_id]);
        $category = $categoryQuery->row();

        // Giai đoạn này, bạn có $product là chi tiết sản phẩm và $images là danh sách hình ảnh

        // Tạo một mảng kết quả để trả về
        $result = [
            'id' => $product->id,
            'title' => $product->title,
            'description' => $product->description,
            'price' => $product->price,
            'discountPercentage' => $product->discountPercentage,
            'rating' => $product->rating,
            'stock' => $product->stock,
            'brand' => $product->brand,
            'category' => $category->name,
            'thumbnail' => $product->thumbnail,
            // Lấy chỉ định cột 'image_url' từ mảng hình ảnh và thêm thumbnail
        ];

        // Trả về dữ liệu kết quả
        return $result;
    }
    public function get_categories()
    {
        $this->db->select('id,name');

        // Thêm điều kiện tìm kiếm

        $query = $this->db->get('categories');
        return $query->result();
    }
    public function getProductsByCategory($categoryId)
    {
        $this->db->select('products.*, GROUP_CONCAT(product_images.image_url) AS image_urls, categories.name AS category');
        $this->db->from('products');
        $this->db->join('product_images', 'products.id = product_images.product_id', 'left');
        $this->db->join('categories', 'products.category_id = categories.id', 'left');
        $this->db->where('products.category_id', $categoryId); // Lọc theo category_id
        $this->db->group_by('products.id');

        $query = $this->db->get();
        return $query->result();
    }

    public function createProduct($data)
    {
        $this->db->trans_start();
        try {
            $productData = array(
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'discountPercentage' => isset($data['discountPercentage']) ? $data['discountPercentage'] :  100,
                'rating' => isset($data['rating']) ? $data['rating'] : 5,
                'stock' => isset($data['stock']) ? $data['stock'] : 0,
                'brand' => isset($data['brand']) ? $data['brand'] : 'Hanbiro',
                'category_id' => $data['category_id'],
                'thumbnail' => isset($data['thumbnail']) ? $data['thumbnail'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Placeholder_view_vector.svg/310px-Placeholder_view_vector.svg.png'
                // Các trường dữ liệu khác của sản phẩm
            );

            // Thực hiện chèn dữ liệu vào bảng 'products'
            $result = $this->db->insert('products', $productData);
            $insertedId = $this->db->insert_id();
            $imageData = array(
                'image_url' => isset($data['thumbnail']) ? $data['thumbnail'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Placeholder_view_vector.svg/310px-Placeholder_view_vector.svg.png',
                'product_id' => $insertedId,
            );
            // Thực hiện chèn dữ liệu vào bảng 'product_image'
            $this->db->where('product_id', $insertedId);
            $this->db->insert('product_images', $imageData);
            if ($result === false) {
                // Nếu có lỗi, throw exception để kích hoạt rollback
                throw new Exception('Error updating product');
            }
            // Kiểm tra xem có lỗi không
            if ($this->db->affected_rows() > 0) {
                $result = array('status' => true, 'message' => 'Product created successfully');
            } else {
                $result = array('status' => false, 'message' => 'Failed to create product');
            }
            $this->db->trans_complete();

            return $result;
        } catch (Exception $e) {
            // Xử lý exception và thực hiện rollback
            $this->db->trans_rollback();
            return false;
        }
    }
    public function updateProduct($id, $data)
    {
        $this->db->trans_start();
        $productData = array(
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'discountPercentage' => $data['discountPercentage'],
            'brand' => $data['brand'],
            'category_id' => $data['category_id'],
            'thumbnail' => isset($data['thumbnail']) ? $data['thumbnail'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Placeholder_view_vector.svg/310px-Placeholder_view_vector.svg.png'
            // Các trường dữ liệu khác của sản phẩm
        );
        try {
            // Thực hiện cập nhật
            $this->db->where('id', $id);
            $result = $this->db->update('products', $productData);

            // Kiểm tra kết quả của cập nhật
            if ($result === false) {
                // Nếu có lỗi, throw exception để kích hoạt rollback
                throw new Exception('Error updating product');
            }

            // Nếu không có lỗi, commit transaction
            $this->db->trans_complete();


            return $result;
        } catch (Exception $e) {
            // Xử lý exception và thực hiện rollback
            $this->db->trans_rollback();
            return false;
        }
    }
    public function deleteProduct($id)
    {
        $this->db->where('product_id', $id);
        $this->db->delete('product_images');

        $this->db->where('id', $id);
        $this->db->delete('products');

        // Trả về số lượng hàng ảnh hưởng (số lượng sản phẩm đã bị xóa)
        return $this->db->affected_rows();
    }
    // -------------------------------------------------------------PRODUCT IMAGE HANDLE-----------------------------------------------------------------
    public function getProductImage($id)
    {
        $imagesQuery = $this->db->get_where('product_images', ['product_id' => $id]);
        $images = $imagesQuery->result();


        // Trả về số lượng hàng ảnh hưởng (số lượng sản phẩm đã bị xóa)
        return $images;
    }
    public function createProductImage($data)
    {
        $this->db->trans_start();
        try {

            $imageData = array(
                'image_url' => isset($data['image_url']) ? $data['image_url'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Placeholder_view_vector.svg/310px-Placeholder_view_vector.svg.png',
                'product_id' => $data['product_id'],
            );
            // Thực hiện chèn dữ liệu vào bảng 'products'
            $result = $this->db->insert('product_images', $imageData);


            if ($result === false) {
                // Nếu có lỗi, throw exception để kích hoạt rollback
                throw new Exception('Error updating product');
            }
            // Kiểm tra xem có lỗi không
            if ($this->db->affected_rows() > 0) {
                $result = array('status' => true, 'message' => 'Product image created successfully', 'data' => $data);
            } else {
                $result = array('status' => false, 'message' => 'Failed to create product');
            }
            $this->db->trans_complete();

            return $result;
        } catch (Exception $e) {
            // Xử lý exception và thực hiện rollback
            $this->db->trans_rollback();
            return false;
        }
    }
    public function updateProductImage(
        $id,
        $data
    ) {
        $this->db->trans_start();

        try {
            // Thực hiện cập nhật
            $this->db->where('id', $id);
            $result = $this->db->update('product_images', $data);

            // Kiểm tra kết quả của cập nhật
            if ($result === false) {
                // Nếu có lỗi, throw exception để kích hoạt rollback
                throw new Exception('Error updating product images');
            }

            // Nếu không có lỗi, commit transaction
            $this->db->trans_complete();


            return $result;
        } catch (Exception $e) {
            // Xử lý exception và thực hiện rollback
            $this->db->trans_rollback();
            return false;
        }
    }
    public function deleteProductImage($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('product_images');

        // Trả về số lượng hàng ảnh hưởng (số lượng sản phẩm đã bị xóa)
        return $this->db->affected_rows();
    }
}