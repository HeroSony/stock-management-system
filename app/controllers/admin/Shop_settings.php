<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shop_settings extends MY_Controller
{

    function __construct() {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('admin');
        }
        $this->lang->admin_load('front_end', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('shop_admin_model');
        $this->upload_path = 'assets/uploads/';
        $this->image_types = 'gif|jpg|jpeg|png';
        $this->allowed_file_size = '1024';
    }

    function index() {

        $this->form_validation->set_rules('shop_name', lang('shop_name'), 'trim|required');
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'trim|required');
        $this->form_validation->set_rules('biller', lang('biller'), 'trim|required');

        if ($this->form_validation->run() == true) {

            $data = array('shop_name' => DEMO ? 'SMA Shop' : $this->input->post('shop_name'),
                'description' => DEMO ? 'Stock Manager Advance - SMA Shop - Demo Ecommerce Shop that would help you to sell your products from your site.' : $this->input->post('description'),
                'warehouse' => $this->input->post('warehouse'),
                'biller' => $this->input->post('biller'),
                'about_link' => $this->input->post('about_link'),
                'terms_link' => $this->input->post('terms_link'),
                'privacy_link' => $this->input->post('privacy_link'),
                'contact_link' => $this->input->post('contact_link'),
                'payment_text' => $this->input->post('payment_text'),
                'follow_text' => $this->input->post('follow_text'),
                'facebook' => $this->input->post('facebook'),
                'twitter' => $this->input->post('twitter'),
                'google_plus' => $this->input->post('google_plus'),
                'instagram' => $this->input->post('instagram'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'cookie_message' => DEMO ? 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.' : $this->input->post('cookie_message'),
                'cookie_link' => $this->input->post('cookie_link'),
                'shipping' => $this->input->post('shipping'),
            );
        }

        if ($this->form_validation->run() == true && $this->shop_admin_model->updateShopSettings($data)) {

            $this->session->set_flashdata('message', lang('settings_updated'));
            admin_redirect("shop_settings");

        } else {

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['pages'] = $this->shop_admin_model->getAllPages();
            $this->data['shop_settings'] = $this->shop_admin_model->getShopSettings();
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('shop_settings')));
            $meta = array('page_title' => lang('shop_settings'), 'bc' => $bc);
            $this->page_construct('shop/index', $meta, $this->data);
        }
    }

    function slider() {

        // $this->form_validation->set_rules('image1', lang('image1'), 'trim|required');
        $this->form_validation->set_rules('caption1', lang('caption').' 1', 'trim|max_length[160]');
        // $this->form_validation->set_rules('image2', lang('image2'), 'trim|required');
        // $this->form_validation->set_rules('caption2', lang('caption2'), 'trim|max_length[160]');

        if ($this->form_validation->run() == true) {

            $uploaded = ['image1' => '', 'image2' => '', 'image3' => '', 'image4' => '', 'image5' => ''];
            if (!DEMO) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);

                $images = ['image1', 'image2', 'image3', 'image4', 'image5'];
                foreach ($images as $image) {
                    if ($_FILES[$image]['size'] > 0) {
                        if (!$this->upload->do_upload($image)) {
                            $error = $this->upload->display_errors();
                            $this->session->set_flashdata('error', $error);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $uploaded[$image] = $this->upload->file_name;
                    }
                }
            }

            $data = [
                [
                'image' => DEMO ? 's1.jpg' : (!empty($uploaded['image1']) ? $uploaded['image1'] : ''),
                'link' => DEMO ? shop_url('products') : $this->input->post('link1'),
                'caption' => DEMO ? '' : $this->input->post('caption1')
                ],
                [
                'image' => DEMO ? 's2.jpg' : (!empty($uploaded['image2']) ? $uploaded['image2'] : ''),
                'link' => DEMO ? '' : $this->input->post('link2'),
                'caption' => DEMO ? '' : $this->input->post('caption2')
                ],
                [
                'image' => DEMO ? 's3.jpg' : (!empty($uploaded['image3']) ? $uploaded['image3'] : ''),
                'link' => DEMO ? '' : $this->input->post('link3'),
                'caption' => DEMO ? '' : $this->input->post('caption3')
                ],
                [
                'image' => DEMO ? '' : (!empty($uploaded['image4']) ? $uploaded['image4'] : ''),
                'link' => DEMO ? '' : $this->input->post('link4'),
                'caption' => DEMO ? '' : $this->input->post('caption4')
                ],
                [
                'image' => DEMO ? '' : (!empty($uploaded['image5']) ? $uploaded['image5'] : ''),
                'link' => DEMO ? '' : $this->input->post('link5'),
                'caption' => DEMO ? '' : $this->input->post('caption5')
                ]
            ];
            foreach($data as &$img) {
                if (empty($img['image'])) {
                    unset($img['image']);
                }
            }
        }

        if ($this->form_validation->run() == true && $this->shop_admin_model->updateSlider($data)) {

            $this->session->set_flashdata('message', lang('silder_updated'));
            admin_redirect("shop_settings/slider");

        } else {

            $shop_settings = $this->shop_admin_model->getShopSettings();
            $this->data['slider_settings'] = json_decode($shop_settings->slider);
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('shop_settings'), 'page' => lang('shop_settings')), array('link' => '#', 'page' => lang('slider_settings')));
            $meta = array('page_title' => lang('slider_settings'), 'bc' => $bc);
            $this->page_construct('shop/slider', $meta, $this->data);
        }
    }

    function pages() {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('shop_settings'), 'page' => lang('shop_settings')), array('link' => '#', 'page' => lang('pages')));
        $meta = array('page_title' => lang('pages'), 'bc' => $bc);
        $this->page_construct('shop/pages', $meta, $this->data);
    }

    function getPages() {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, name, slug, active, order_no, title")
            ->from("pages")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . admin_url('shop_settings/edit_page/$1') . "' class='tip' title='" . lang("edit_page") . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_page") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('shop_settings/delete_page/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    function add_page() {

        $this->form_validation->set_rules('name', lang('name'), 'required|alpha_numeric_spaces|max_length[15]');
        $this->form_validation->set_rules('title', lang('title'), 'required|alpha_numeric_spaces|max_length[60]');
        $this->form_validation->set_rules('description', lang('description'), 'required');
        $this->form_validation->set_rules('body', lang('body'), 'required');
        $this->form_validation->set_rules('order_no', lang('order_no'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('slug', lang('slug'), 'trim|required|is_unique[pages.slug]|alpha_dash');
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'body' => $this->input->post('body', TRUE),
                'slug' => $this->input->post('slug'),
                'order_no' => $this->input->post('order_no'),
                'active' => $this->input->post('active') ? $this->input->post('active') : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            );
        }

        if ($this->form_validation->run() == true && $this->shop_admin_model->addPage($data)) {

            $this->session->set_flashdata('message', lang('page_added'));
            admin_redirect("shop_settings/pages");

        } else {
            
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('add_page')));
            $meta = array('page_title' => lang('add_page'), 'bc' => $bc);
            $this->page_construct('shop/add_page', $meta, $this->data);

        }
    }

    function edit_page($id = NULL) {

        $page = $this->shop_admin_model->getPageByID($id);
        $this->form_validation->set_rules('name', lang('name'), 'required|alpha_numeric_spaces|max_length[15]');
        $this->form_validation->set_rules('title', lang('title'), 'required|alpha_numeric_spaces|max_length[60]');
        $this->form_validation->set_rules('description', lang('description'), 'required');
        $this->form_validation->set_rules('body', lang('body'), 'required');
        $this->form_validation->set_rules('order_no', lang('order_no'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('slug', lang('slug'), 'trim|required|alpha_dash');
        if($page->slug != $this->input->post('slug')) {
            $this->form_validation->set_rules('slug', lang('slug'), 'is_unique[pages.slug]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'body' => $this->input->post('body', TRUE),
                'slug' => $this->input->post('slug'),
                'order_no' => $this->input->post('order_no'),
                'active' => $this->input->post('active') ? $this->input->post('active') : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            );
        }

        if ($this->form_validation->run() == true && $this->shop_admin_model->updatePage($id, $data)) {

            $this->session->set_flashdata('message', lang('page_updated'));
            admin_redirect("shop_settings/pages");

        } else {

            $this->data['page'] = $page;
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('edit_page')));
            $meta = array('page_title' => lang('edit_page'), 'bc' => $bc);
            $this->page_construct('shop/edit_page', $meta, $this->data);

        }
    }

    function delete_page($id = NULL) {

        if ($this->shop_admin_model->deletePage($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("page_deleted")));
        }
    }

    function page_actions() {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->shop_admin_model->deletePage($id);
                    }
                    $this->session->set_flashdata('message', lang("pages_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function change_logo()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            $this->sma->md();
        }
        $this->load->helper('security');
        $this->form_validation->set_rules('site_logo', lang("site_logo"), 'xss_clean');
        $this->form_validation->set_rules('login_logo', lang("login_logo"), 'xss_clean');
        $this->form_validation->set_rules('biller_logo', lang("biller_logo"), 'xss_clean');
        if ($this->form_validation->run() == true) {

            if ($_FILES['site_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = 300;
                $config['max_height'] = 80;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('site_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;
                $this->db->update('settings', array('logo' => $site_logo), array('setting_id' => 1));
            }

            if ($_FILES['login_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = 300;
                $config['max_height'] = 80;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('login_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $login_logo = $this->upload->file_name;
                $this->db->update('settings', array('logo2' => $login_logo), array('setting_id' => 1));
            }

            if ($_FILES['biller_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = 300;
                $config['max_height'] = 80;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('biller_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
            }

            $this->session->set_flashdata('message', lang('logo_uploaded'));
            redirect($_SERVER["HTTP_REFERER"]);

        } elseif ($this->input->post('upload_logo')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/change_logo', $this->data);
        }
    }

    function email_templates($template = "credentials")
    {

        $this->form_validation->set_rules('mail_body', lang('mail_message'), 'trim|required');
        $this->load->helper('file');
        $temp_path = is_dir('./themes/' . $this->theme . 'email_templates/');
        $theme = $temp_path ? $this->theme : 'default';
        if ($this->form_validation->run() == true) {
            $data = $_POST["mail_body"];
            if (write_file('./themes/' . $this->theme . 'email_templates/' . $template . '.html', $data)) {
                $this->session->set_flashdata('message', lang('message_successfully_saved'));
                admin_redirect('shop_settings/email_templates#' . $template);
            } else {
                $this->session->set_flashdata('error', lang('failed_to_save_message'));
                admin_redirect('shop_settings/email_templates#' . $template);
            }
        } else {

            $this->data['credentials'] = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
            $this->data['sale'] = file_get_contents('./themes/' . $this->theme . 'email_templates/sale.html');
            $this->data['quote'] = file_get_contents('./themes/' . $this->theme . 'email_templates/quote.html');
            $this->data['purchase'] = file_get_contents('./themes/' . $this->theme . 'email_templates/purchase.html');
            $this->data['transfer'] = file_get_contents('./themes/' . $this->theme . 'email_templates/transfer.html');
            $this->data['payment'] = file_get_contents('./themes/' . $this->theme . 'email_templates/payment.html');
            $this->data['forgot_password'] = file_get_contents('./themes/' . $this->theme . 'email_templates/forgot_password.html');
            $this->data['activate_email'] = file_get_contents('./themes/' . $this->theme . 'email_templates/activate_email.html');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('shop_settings'), 'page' => lang('shop_settings')), array('link' => '#', 'page' => lang('email_templates')));
            $meta = array('page_title' => lang('email_templates'), 'bc' => $bc);
            $this->page_construct('settings/email_templates', $meta, $this->data);
        }
    }

}
