<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Social_auth extends MY_Shop_Controller {

	function __construct() {
		parent::__construct();
	}

	public function index() {
		redirect('/');
	}

	public function login($provider) {

		try
		{
			$this->load->library('HybridAuthLib');

			if ($this->hybridauthlib->providerEnabled($provider)) {

				$service = $this->hybridauthlib->authenticate($provider);

				if ($service->isUserConnected()) {

					$this->lang->admin_load('auth');
					$this->load->admin_model('auth_model');
					$this->load->library('ion_auth');
					$user_profile = $service->getUserProfile();

					if ($user = $this->shop_model->getUserByEmail($user_profile->email)) {

						if ($user->active < 1) {
							$this->session->set_flashdata('error', lang('login_unsuccessful_not_active'));
							redirect('login');
						}
						$this->session->set_flashdata('message', lang('login_successful'));
						$this->auth_model->update_last_login($user->id);
						$this->auth_model->update_last_login_ip($user->id);
						$this->auth_model->set_session($user);

					} else {

						$this->load->helper('string');

						$email = $username = $user_profile->email;
						$password = random_string('alnum', 8);

						$customer_group = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
						$price_group = $this->shop_model->getPriceGroup($this->Settings->price_group);

						$company_data = [
						'company' => '-',
						'name' => $user_profile->firstName.' '.$user_profile->lastName,
						'email' => $user_profile->email,
						'group_id' => 3,
						'group_name' => 'customer',
						'customer_group_id' =>$customer_group ? $customer_group->id : NULL,
						'customer_group_name' => $customer_group ? $customer_group->name : NULL,
						'price_group_id' =>$price_group ? $price_group->id : NULL,
						'price_group_name' => $price_group ? $price_group->name : NULL,
						];

						$company_id = $this->shop_model->addCustomer($company_data);

						$additional_data = [
						'first_name' => $user_profile->firstName,
						'last_name' => $user_profile->lastName,
						'gender' => $user_profile->gender,
						'company_id' => $company_id,
						'group_id' => 3
						];


						if ($this->ion_auth->register($username, $password, $email, $additional_data, TRUE, TRUE)) {

							if ($this->ion_auth->login($email, $password)) {
								if ($this->Settings->mmode) {
									if ( ! $this->sma->in_group('owner')) {
										$this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
										redirect('logout');
									}
								}

								$this->session->set_flashdata('message', $this->ion_auth->messages());
								$referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : '/';
								redirect($referrer);
							} else {
								$this->session->set_flashdata('error', lang('user_login_failed').' '.$this->ion_auth->errors());
								redirect('login');
							}

						} else {
							$this->session->set_flashdata('error', lang('user_registeration_failed').' '.$this->ion_auth->errors());
							redirect('login');
						}
					}

					// $this->sma->print_arrays($additional_data);
					// $this->sma->print_arrays($user_profile);
					// $this->session->set_flashdata('message', $this->ion_auth->messages());
					redirect('/');

				} else {
					$this->session->set_flashdata('error', lang('cant_authenticate_user'));
					redirect('login');
				}

			} else {
				log_message('error', 'controllers.HAuth.login: This provider is not enabled ('.$provider.')');
				show_404($_SERVER['REQUEST_URI']);
			}

		} catch(Exception $e) {

			$error = 'Unexpected error';
			switch($e->getCode())
			{
				case 0 : $error = 'Unspecified error.'; break;
				case 1 : $error = 'Hybriauth configuration error.'; break;
				case 2 : $error = 'Provider not properly configured.'; break;
				case 3 : $error = 'Unknown or disabled provider.'; break;
				case 4 : $error = 'Missing provider application credentials.'; break;
				case 5 :
				if (isset($service)) {
					$service->logout();
				}
				$this->session->set_flashdata('error', lang('user_cancelled_auth'));
				redirect('login');
				break;
				case 6 : $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
				break;
				case 7 : $error = 'User not connected to the provider.';
				break;
			}

			if (isset($service)) {
				$service->logout();
			}

			log_message('error', 'controllers.HAuth.login: '.$error);
			$this->session->set_flashdata('error', lang('error_authenticating_user'));
			redirect('login');
		}
	}

	public function endpoint() {

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$_GET = $_REQUEST;
		}
		require_once APPPATH.'third_party/hybridauth/index.php';

	}
}
