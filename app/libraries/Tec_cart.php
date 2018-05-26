<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tec_cart
{

    public $product_id_rules = '\.a-z0-9_-';
    public $product_name_rules = '\w \-\.\:';
    public $product_name_safe = TRUE;
    public $cart_id = FALSE;
    protected $_cart_contents = array();

    public function __construct($params = array()) {
        $this->load->helper('cookie');
        if ($cart_id = get_cookie('cart_id', TRUE)) {
            $this->cart_id = $cart_id;
            $result = $this->db->get_where('cart', array('id' => $this->cart_id))->row();
            $this->_cart_contents = $result ? json_decode($result->data, true) : NULL;
        } else {
            $this->_setup();
        }
        if (empty($this->_cart_contents)) {
            $this->_empty();
        }
    }

    public function __get($var) {
        return get_instance()->$var;
    }

    private function _setup() {
        $this->load->helper('string');
        $val = md5(random_string('alnum', 16));
        set_cookie('cart_id', $val, 2592000);
        return $this->cart_id = $val;
    }

    private function _empty() {
        $this->_cart_contents = array('cart_total' => 0, 'total_item_tax' => 0, 'total_items' => 0, 'total_unique_items' => 0);
    }

    public function cart_id() {
        return $this->cart_id;
    }

    public function insert($items = array()) {

        if (!is_array($items) OR count($items) === 0) {
            return FALSE;
        }

        $save_cart = FALSE;
        if (isset($items['id'])) {
            if (($rowid = $this->_insert($items))) {
                $save_cart = TRUE;
            }
        } else {
            foreach ($items as $val) {
                if (is_array($val) && isset($val['id'])) {
                    if ($this->_insert($val)) {
                        $save_cart = TRUE;
                    }
                }
            }
        }

        if ($save_cart === TRUE) {
            $this->_save_cart();
            return isset($rowid) ? $rowid : TRUE;
        }

        return FALSE;
    }

    protected function _insert($items = array()) {
        if (!is_array($items) OR count($items) === 0) {
            return FALSE;
        }

        if (!isset($items['id'], $items['qty'], $items['name'])) {
            return FALSE;
        }

        $items['qty'] = (float) $items['qty'];

        if ($items['qty'] == 0) {
            return FALSE;
        }

        if (!preg_match('/^[' . $this->product_id_rules . ']+$/i', $items['id'])) {
            return FALSE;
        }

        if ($this->product_name_safe && !preg_match('/^[' . $this->product_name_rules . ']+$/i' . (UTF8_ENABLED ? 'u' : ''), $items['name'])) {
            return FALSE;
        }

        $items['price'] = (float) $items['price'];

        if (isset($items['options']) && count($items['options']) > 0) {
            $rowid = md5($items['id'] . serialize((array) $items['options']));
        } else {
            $rowid = md5($items['id']);
        }

        $old_quantity = isset($this->_cart_contents[$rowid]['qty']) ? (int) $this->_cart_contents[$rowid]['qty'] : 0;

        $items['rowid'] = $rowid;
        $items['qty'] += $old_quantity;
        $this->_cart_contents[$rowid] = $items;

        return $rowid;
    }

    public function update($items = array()) {
        if (!is_array($items) OR count($items) === 0) {
            return FALSE;
        }

        $save_cart = FALSE;
        if (isset($items['rowid'])) {
            if ($this->_update($items) === TRUE) {
                $save_cart = TRUE;
            }
        } else {
            foreach ($items as $val) {
                if (is_array($val) && isset($val['rowid'])) {
                    if ($this->_update($val) === TRUE) {
                        $save_cart = TRUE;
                    }
                }
            }
        }

        if ($save_cart === TRUE) {
            $this->_save_cart();
            return TRUE;
        }

        return FALSE;
    }

    protected function _update($items = array()) {
        if (!isset($items['rowid'], $this->_cart_contents[$items['rowid']])) {
            return FALSE;
        }

        if (isset($items['qty'])) {
            $items['qty'] = (float) $items['qty'];
            if ($items['qty'] == 0) {
                unset($this->_cart_contents[$items['rowid']]);
                return TRUE;
            }
        }

        $keys = array_intersect(array_keys($this->_cart_contents[$items['rowid']]), array_keys($items));
        if (isset($items['price'])) {
            $items['price'] = (float) $items['price'];
        }

        foreach (array_diff($keys, array('id', 'name')) as $key) {
            $this->_cart_contents[$items['rowid']][$key] = $items[$key];
        }

        return TRUE;
    }

    protected function _save_cart() {
        $this->_cart_contents['cart_total'] = 0;
        $this->_cart_contents['total_items'] = 0;
        $this->_cart_contents['total_item_tax'] = 0;
        $this->_cart_contents['total_unique_items'] = 0;
        foreach ($this->_cart_contents as $key => $val) {
            if (!is_array($val) OR !isset($val['price'], $val['qty'])) {
                continue;
            }

            $this->_cart_contents['total_unique_items'] += 1;
            $this->_cart_contents['total_items'] += $val['qty'];
            $this->_cart_contents['cart_total'] += $this->sma->formatDecimal(($val['price'] * $val['qty']), 4);
            $this->_cart_contents['total_item_tax'] += $this->sma->formatDecimal(($val['tax'] * $val['qty']), 4);
            $this->_cart_contents[$key]['row_tax'] = $this->sma->formatDecimal(($this->_cart_contents[$key]['tax'] * $this->_cart_contents[$key]['qty']), 4);
            $this->_cart_contents[$key]['subtotal'] = $this->sma->formatDecimal(($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']), 4);
        }

        if (count($this->_cart_contents) <= 4) {
            $this->db->delete('cart', array('id' => $this->cart_id));
            return FALSE;
        }

        if ($this->db->get_where('cart', array('id' => $this->cart_id))->num_rows() > 0) {
            return $this->db->update('cart', array('time' => time(), 'user_id' => $this->session->userdata('user_id'), 'data' => json_encode($this->_cart_contents)), array('id' => $this->cart_id));
        } else {
            return $this->db->insert('cart', array('id' => $this->cart_id, 'time' => time(), 'user_id' => $this->session->userdata('user_id'), 'data' => json_encode($this->_cart_contents)));
        }

    }

    public function total() {
        return $this->sma->formatDecimal($this->_cart_contents['cart_total'], 4);
    }

    public function shipping() {
        return $this->sma->formatDecimal($this->shop_settings->shipping, 4);
    }

    public function order_tax() {
        if (!empty($this->Settings->tax2)) {
            if ($order_tax_details = $this->site->getTaxRateByID($this->Settings->default_tax_rate2)) {
                if ($order_tax_details->type == 2 || $order_tax_details->rate == 0) {
                    $order_tax = $this->sma->formatDecimal($order_tax_details->rate, 4);
                } elseif ($order_tax_details->type == 1) {
                    $order_tax = $this->sma->formatDecimal(((($this->total()) * $order_tax_details->rate) / 100), 4);
                }
                return $order_tax;
            }
        }
        return 0;
    }

    public function total_item_tax() {
        return $this->sma->formatDecimal($this->_cart_contents['total_item_tax'], 4);
    }

    public function remove($rowid) {
        unset($this->_cart_contents[$rowid]);
        $this->_save_cart();
        return TRUE;
    }

    public function total_items($unique = FALSE) {
        return $unique ? $this->_cart_contents['total_unique_items'] : $this->_cart_contents['total_items'];
    }

    public function contents($newest_first = FALSE) {
        $cart = ($newest_first) ? array_reverse($this->_cart_contents) : $this->_cart_contents;
        unset($cart['total_items']);
        unset($cart['total_item_tax']);
        unset($cart['total_unique_items']);
        unset($cart['cart_total']);
        return $cart;
    }

    public function get_item($row_id) {
        return (in_array($row_id, array('total_items', 'cart_total'), TRUE) OR !isset($this->_cart_contents[$row_id]))
        ? FALSE
        : $this->_cart_contents[$row_id];
    }

    public function has_options($row_id = '') {
        return (isset($this->_cart_contents[$row_id]['options']) && count($this->_cart_contents[$row_id]['options']) !== 0);
    }

    public function product_options($row_id = '') {
        return isset($this->_cart_contents[$row_id]['options']) ? $this->_cart_contents[$row_id]['options'] : array();
    }

    public function destroy() {
        $this->_empty();
        return $this->db->delete('cart', array('id' => $this->cart_id));
    }

    // Get cart with currency conversion
    function cart_data($re = false) {
        $citems = $this->contents();
        foreach($citems as &$value) {
            $value['price'] = $this->sma->convertMoney($value['price']);
            $value['subtotal'] = $this->sma->convertMoney($value['subtotal']);
            if ($this->has_options($value['rowid'])) {
                $value['options'] = $this->product_options($value['rowid']);
                foreach($value['options'] as &$opt_value) {
                    $opt_value['price'] = $this->sma->convertMoney($opt_value['price']);
                }
            }
        }
        $total = $this->sma->convertMoney($this->total(), FALSE, FALSE);
        $shipping = $this->sma->convertMoney($this->shipping(), FALSE, FALSE);
        $order_tax = $this->sma->convertMoney($this->order_tax(), FALSE, FALSE);
        $cart = array(
            'total_items' => $this->total_items(),
            'total_unique_items' => $this->total_items(TRUE),
            'contents' => $citems,
            'total_item_tax' => $this->sma->convertMoney($this->total_item_tax()),
            'subtotal' => $this->sma->convertMoney($this->total()-$this->total_item_tax()),
            'total' => $this->sma->formatMoney($total, $this->selected_currency->symbol),
            'shipping' => $this->sma->formatMoney($shipping, $this->selected_currency->symbol),
            'order_tax' => $this->sma->formatMoney($order_tax, $this->selected_currency->symbol),
            'grand_total' => $this->sma->formatMoney(($this->sma->formatDecimal($total)+$this->sma->formatDecimal($order_tax)+$this->sma->formatDecimal($shipping)), $this->selected_currency->symbol),
            );

        if ($re) {
            return $cart;
        }

        $this->sma->send_json($cart);
    }

}
