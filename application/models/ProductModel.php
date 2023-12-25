<?php


defined('BASEPATH') or exit('No direct script access allowed');
class ProductModel extends CI_Model
{
    public function get_products($keyword)
    {
        $this->db->select('products.*, GROUP_CONCAT(product_images.image_url) AS image_urls, categories.name AS category');
        $this->db->from('products');
        $this->db->join('product_images', 'products.id = product_images.product_id', 'left');
        $this->db->join('categories', 'products.category_id = categories.id', 'left');
        $this->db->group_by('products.id');

        // Thêm điều kiện tìm kiếm
        $this->db->like('products.title', $keyword);
        $this->db->or_like('products.description', $keyword);

        $query = $this->db->get();
        return $query->result();
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
        $imagesQuery = $this->db->get_where('product_images', ['product_id' => $productId]);
        $images = $imagesQuery->result();

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
            'images' => array_column($images, 'image_url'), // Lấy chỉ định cột 'image_url' từ mảng hình ảnh
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
                'thumbnail' => isset($data['brand']) ? $data['brand'] : 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Placeholder_view_vector.svg/310px-Placeholder_view_vector.svg.png'
                // Các trường dữ liệu khác của sản phẩm
            );
            // Thực hiện chèn dữ liệu vào bảng 'products'
            $result = $this->db->insert('products', $productData);
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

        try {
            // Thực hiện cập nhật
            $this->db->where('id', $id);
            $result = $this->db->update('products', $data);

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
}
