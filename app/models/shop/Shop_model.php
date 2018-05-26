<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shop_model extends CI_Model
{

    public function __construct() {
        parent::__construct();
    }

    public function getSettings() {
        return $this->db->get('settings')->row();
    }

    public function getShopSettings() {
        return $this->db->get('shop_settings')->row();
    }

    public function getCustomerGroup($id) {
        return $this->db->get('customer_groups', ['id' => $id])->row();
    }

    public function getPriceGroup($id) {
        return $this->db->get('price_groups', ['id' => $id])->row();
    }

    public function getDateFormat($id) {
        return $this->db->get_where('date_format', ['id' => $id], 1)->row();
    }

    public function addCustomer($data) {
        if ($this->db->insert('companies', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function addWishlist($product_id) {
        $user_id = $this->session->userdata('user_id');
        if (!$this->getWishlistItem($product_id, $user_id)) {
            return $this->db->insert('wishlist', ['product_id' => $product_id, 'user_id' => $user_id]);
        }
        return FALSE;
    }

    public function removeWishlist($product_id) {
        $user_id = $this->session->userdata('user_id');
        return $this->db->delete('wishlist', ['product_id' => $product_id, 'user_id' => $user_id]);
    }

    public function getWishlistItem($product_id, $user_id) {
        return $this->db->get_where('wishlist', ['product_id' => $product_id, 'user_id' => $user_id])->row();
    }

    public function getAllCurrencies() {
        return $this->db->get('currencies')->result();
    }

    public function getNotifications() {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where("from_date <=", $date)
        ->where("till_date >=", $date)->where('scope !=', 2);
        return $this->db->get("notifications")->result();
    }

    public function getAddresses() {
        return $this->db->get_where("addresses", ['company_id' => $this->session->userdata('company_id')])->result();
    }

    public function getCurrencyByCode($code) {
        return $this->db->get_where('currencies', ['code' => $code], 1)->row();
    }

    public function getAllCategories() {
        $this->db->where('parent_id', NULL)->or_where('parent_id', 0)->order_by('name');
        return $this->db->get("categories")->result();
    }

    public function getSubCategories($parent_id) {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        return $this->db->get("categories")->result();
    }

    public function getCategoryBySlug($slug) {
        return $this->db->get_where('categories', ['slug' => $slug], 1)->row();
    }

    public function getAllBrands() {
        return $this->db->get("brands")->result();
    }

    public function getBrandBySlug($slug) {
        return $this->db->get_where('brands', ['slug' => $slug], 1)->row();
    }

    public function getUserByEmail($email) {
        return $this->db->get_where('users', ['email' => $email], 1)->row();
    }

    public function getAllPages() {
        $this->db->select('name, slug')->order_by('order_no asc');
        return $this->db->get_where("pages", ['active' => 1])->result();
    }

    public function getPageBySlug($slug) {
        return $this->db->get_where('pages', ['slug' => $slug], 1)->row();
    }

    public function getFeaturedProducts($limit = 16, $promo = TRUE) {

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.slug as slug, price, quantity, type, promotion, promo_price, b.name as brand_name, b.slug as brand_slug, c.name as category_name, c.slug as category_slug")
        ->join('brands b', 'products.brand=b.id', 'left')
        ->join('categories c', 'products.category_id=c.id', 'left')
        ->where('products.featured', 1)
        ->limit($limit);
        if ($promo) {
            $this->db->order_by('promotion desc');
        }
        $this->db->order_by('RAND()');
        return $this->db->get("products")->result();
    }

    public function getProducts($filters = []) {

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.slug as slug, price, quantity, type, promotion, promo_price, product_details as details")
        ->limit($filters['limit'], $filters['offset']);
        if (!empty($filters)) {
            if (!empty($filters['query'])) {
                $this->db->like('name', $filters['query'], 'both');
            }
            if (!empty($filters['category'])) {
                $this->db->where('category_id', $filters['category']['id']);
            }
            if (!empty($filters['brand'])) {
                $this->db->where('brand', $filters['brand']['id']);
            }
            if (!empty($filters['min_price'])) {
                $this->db->where('price >=', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $this->db->where('price <=', $filters['max_price']);
            }
            if (!empty($filters['in_stock'])) {
                $this->db->group_start()->where('quantity >=', 1)->or_where('type !=', 'standard')->group_end();
            }
            if (!empty($filters['sorting'])) {
                $this->db->order_by(str_replace('-', ' ', $filters['sorting']));
            } else {
                $this->db->order_by('name asc');
            }
        } else {
            $this->db->order_by('name asc');
        }
        return $this->db->get("products")->result_array();
    }

    public function getProductsCount($filters = []) {

        if (!empty($filters)) {
            if (!empty($filters['query'])) {
                $this->db->like('name', $filters['query'], 'both');
            }
            if (!empty($filters['category'])) {
                $this->db->where('category_id', $filters['category']['id']);
            }
            if (!empty($filters['brand'])) {
                $this->db->where('brand', $filters['brand']['id']);
            }
            if (!empty($filters['min_price'])) {
                $this->db->where('price >=', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $this->db->where('price <=', $filters['max_price']);
            }
            if (!empty($filters['in_stock'])) {
                $this->db->where('quantity >=', 1);
            }
        }

        return $this->db->count_all_results("products");
    }

    public function getWishlist($no = NULL) {
        $this->db->where('user_id', $this->session->userdata('user_id'));
        return $no ? $this->db->count_all_results('wishlist') : $this->db->get('wishlist')->result();
    }

    public function getProductBySlug($slug) {
        return $this->db->get_where('products', ['slug' => $slug], 1)->row();
    }

    public function getProductByID($id) {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.slug as slug, price, quantity, type, promotion, promo_price, product_details as details");
        return $this->db->get_where('products', ['id' => $id], 1)->row();
    }

    public function getProductVariants($product_id, $warehouse_id = NULL, $all = NULL) {
        if (!$warehouse_id) { $warehouse_id = $this->shop_settings->warehouse; }
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', FALSE)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (! $this->Settings->overselling && ! $all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        return $this->db->get('product_variants')->result_array();
    }

    public function getProductVariantByID($id) {
        return $this->db->get_where('product_variants',['id' => $id])->row();
    }

    public function getProductVariantWarehouseQty($option_id, $warehouse_id) {
        return $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1)->row();
    }

    public function getAddressByID($id) {
        return $this->db->get_where('addresses', ['id' => $id], 1)->row();
    }

    public function addSale($data = array(), $items = array())
    {

        $cost = $this->site->costing($items);
        // $this->sma->print_arrays($cost);

        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            $this->site->updateReference('so');

            foreach ($items as $item) {

                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($data['sale_status'] == 'completed') {

                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                            $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $sale_id;
                                $ic['date'] = date('Y-m-d', strtotime($data['date']));
                                if(! isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            // $this->site->syncQuantity($sale_id);
            // $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            return $sale_id;

        }

        return false;
    }

    public function getOrder($clause) {
        if ($this->loggedIn) {
            $this->db->order_by('id desc');
            $sale = $this->db->get_where('sales', ['id' => $clause['id']], 1)->row();
            return ($sale->customer_id == $this->session->userdata('company_id')) ? $sale : FALSE;
        } elseif(!empty($clause['hash'])) {
            return $this->db->get_where('sales', $clause, 1)->row();
        }
        return FALSE;
    }

    public function getOrders($limit, $offset) {
        if ($this->loggedIn) {
            $this->db->select("sales.*, deliveries.status as delivery_status")
            ->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->limit($limit, $offset);
            return $this->db->get_where('sales', ['customer_id' => $this->session->userdata('company_id')])->result();
        } 
        return FALSE;
    }

    public function getOrdersCount() {
        $this->db->where('customer_id', $this->session->userdata('company_id'));
        return $this->db->count_all_results("sales");
    }

    public function getOrderItems($sale_id) {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');

        return $this->db->get_where('sale_items', ['sale_id' => $sale_id])->result();
    }

    public function getQuote($clause) {
        if ($this->loggedIn) {
            $this->db->order_by('id desc');
            $sale = $this->db->get_where('quotes', ['id' => $clause['id']], 1)->row();
            return ($sale->customer_id == $this->session->userdata('company_id')) ? $sale : FALSE;
        } elseif(!empty($clause['hash'])) {
            return $this->db->get_where('quotes', $clause, 1)->row();
        }
        return FALSE;
    }

    public function getQuotes($limit, $offset) {
        if ($this->loggedIn) {
            $this->db->limit($limit, $offset);
            return $this->db->get_where('quotes', ['customer_id' => $this->session->userdata('company_id')])->result();
        } 
        return FALSE;
    }

    public function getQuotesCount() {
        $this->db->where('customer_id', $this->session->userdata('company_id'));
        return $this->db->count_all_results("quotes");
    }

    public function getQuoteItems($quote_id) {
        $this->db->select('quote_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=quote_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
            ->group_by('quote_items.id')
            ->order_by('id', 'asc');
        return $this->db->get_where('quote_items', ['quote_id' => $quote_id])->result();
    }

    public function getProductComboItems($pid) {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function getProductPhotos($id) {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllWarehouseWithPQ($product_id, $warehouse_id = NULL) {
        if (!$warehouse_id) { $warehouse_id = $this->shop_settings->warehouse; }
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('warehouses.id');
        return $this->db->get('warehouses')->row();
    }

    public function getProductOptionsWithWH($product_id, $warehouse_id = NULL) {
        if (!$warehouse_id) { $warehouse_id = $this->shop_settings->warehouse; }
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(['' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'])
            ->order_by('product_variants.id');
        return $this->db->get_where('product_variants', ['product_variants.product_id' => $product_id, 'warehouses.id' => $warehouse_id, 'warehouses_products_variants.quantity !=' => NULL])->result();
    }

    public function getProductOptions($product_id) {
        return $this->db->get_where('product_variants', array('product_id' => $product_id))->result();
    }

    public function getSaleByID($id) {
        return $this->db->get_where('sales', ['id' => $id])->row();
    }

    public function getCompanyByID($id) {
        return $this->db->get_where('companies', ['id' => $id])->row();
    }

    public function getPaypalSettings() {
        return $this->db->get_where('paypal', ['id' => 1])->row();
    }

    public function getSkrillSettings() {
        return $this->db->get_where('skrill', ['id' => 1])->row();
    }

    public function updateCompany($id, $data = array()) {
        return $this->db->update('companies', $data, ['id' => $id]);
    }

}
