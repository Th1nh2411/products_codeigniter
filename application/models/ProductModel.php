<?php


defined('BASEPATH') or exit('No direct script access allowed');
class ProductModel extends CI_Model
{
    public function get_products($keyword)
    {
        $this->db->select('products.*, GROUP_CONCAT(product_images.image_url) AS image_urls');
        $this->db->from('products');
        $this->db->join('product_images', 'products.id = product_images.product_id', 'left');
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
            'category' => $product->category,
            'thumbnail' => $product->thumbnail,
            'images' => array_column($images, 'image_url'), // Lấy chỉ định cột 'image_url' từ mảng hình ảnh
        ];

        // Trả về dữ liệu kết quả
        return $result;
    }
}