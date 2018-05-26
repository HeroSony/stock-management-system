<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products_api extends CI_Model
{

    public function __construct() {
        parent::__construct();
    }

    public function countProducts($filters = []) {
        if ($filters['category']) {
            $category = $this->getCategoryByCode($filters['category']);
            $this->db->where('category_id', $category->id);
        }
        if ($filters['brand']) {
            $brand = $this->getBrandByCode($filters['brand']);
            $this->db->where('brand', $brand->id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function getProducts($filters = []) {

        $uploads_url = base_url('assets/uploads/');
        $this->db->select("id, code, name, type, slug, price, CONCAT('{$uploads_url}', image) as image_url, tax_method, tax_rate, unit");

        if (!empty($filters['include'])) {
            foreach ($filters['include'] as $include) {
                if ($include == 'brand') {
                    $this->db->select('brand');
                } elseif ($include == 'category') {
                    $this->db->select('category_id as category');
                }
            }
        }
        if ($filters['category']) {
            $category = $this->getCategoryByCode($filters['category']);
            $this->db->where('category_id', $category->id);
        }
        if ($filters['brand']) {
            $brand = $this->getBrandByCode($filters['brand']);
            $this->db->where('brand', $brand->id);
        }
        if ($filters['code']) {
            $this->db->where('code', $filters['code']);
        } else {
            $this->db->order_by($filters['order_by'][0], $filters['order_by'][1] ? $filters['order_by'][1] : 'asc');
            $this->db->limit($filters['limit'], ($filters['start']-1));
        }

        return $this->db->get("products")->result();
    }

    public function getProduct($filters) {
        if (!empty($products = $this->getProducts($filters))) {
            return array_values($products)[0];
        }
        return FALSE;
    }

    public function getCategoryByID($id) {
        return $this->db->get_where('categories', ['id' => $id], 1)->row();
    }

    public function getBrandByID($id) {
        return $this->db->get_where('brands', ['id' => $id], 1)->row();
    }

    public function getCategoryByCode($code) {
        return $this->db->get_where('categories', ['code' => $code], 1)->row();
    }

    public function getBrandByCode($code) {
        return $this->db->get_where('brands', ['code' => $code], 1)->row();
    }

    public function getTaxRateByID($id) {
        return $this->db->get_where('tax_rates', ['id' => $id], 1)->row();
    }

    public function getProductUnit($id) {
        return $this->db->get_where('units', ['id' => $id], 1)->row();
    }

    public function getSubUnits($base_unit) {
        return $this->db->get_where('units', ['base_unit' => $base_unit])->result();
    }

    public function getProductPhotos($product_id) {
        $uploads_url = base_url('assets/uploads/');
        $this->db->select("CONCAT('{$uploads_url}', photo) as photo_url");
        return $this->db->get_where('product_photos', ['product_id' => $product_id])->result();
    }

}
