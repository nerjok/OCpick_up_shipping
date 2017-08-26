<?php
/*
* @package		dpdshops
* @copyright	2017 e-tools, e-tools.lt
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @license		GNU/GPL based on AceShop www.joomace.net
*/


class ControllerExtensionShippingDpdshops extends Controller { 
	private $error = array();
	
	public function index() {  
		$this->language->load('extension/shipping/dpdshops');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				 
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_dpdshops', $this->request->post);	

			$this->session->data['success'] = $this->language->get('text_success');
									
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=shipping', 'SSL'));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_none'] = $this->language->get('text_none');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_baseshippingprice'] = $this->language->get('entry_baseshippingprice');
		$data['entry_priceperadditional'] = $this->language->get('entry_priceperadditional');
		$data['entry_freeshippingfrom'] = $this->language->get('entry_freeshippingfrom');
		

		$data['entry_disableifhtml'] = $this->language->get('entry_disableifhtml');
		$data['entry_maxweight'] = $this->language->get('entry_maxweight');
		$data['entry_handlingaction'] = $this->language->get('entry_handlingaction');
		$data['shipping_dpdshops_handlingactions'] = array($this->language->get('Per order'), $this->language->get('Per package'));
		$data['entry_enablefreeshipping'] = $this->language->get('entry_enablefreeshipping');
		$data['entry_freeshippingsubtotal'] = $this->language->get('entry_freeshippingsubtotal');
		$data['entry_enablecod'] = $this->language->get('entry_enablecod');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_shipping'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=shipping', 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/shipping/dpdshops', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$data['action'] = $this->url->link('extension/shipping/dpdshops', 'user_token=' . $this->session->data['user_token'], 'SSL');
		
		$data['cancel'] = $this->url->link('extension/shipping', 'user_token=' . $this->session->data['user_token'], 'SSL'); 

		$this->load->model('localisation/geo_zone');
		
		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();
		
		
		foreach ($geo_zones as $geo_zone) {

			$data['states'][ $geo_zone['geo_zone_id']]['geo_zone'] =  $geo_zone['geo_zone_id'];
			if (isset($this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_baseshippingprice'])) {
		
			$data['states'][ $geo_zone['geo_zone_id']]['baseshippingprice']=$this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_baseshippingprice'];
			} else {
			
			$data['states'][ $geo_zone['geo_zone_id']]['baseshippingprice'] = $this->config->get('shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_baseshippingprice');
			}


			if (isset($this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_priceperadditional'])) {
			
			$data['states'][ $geo_zone['geo_zone_id']]['priceperadditional'] = $this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_priceperadditional'];
			} else {
				
				$data['states'][ $geo_zone['geo_zone_id']]['priceperadditional'] = $this->config->get('shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_priceperadditional');
			}
			

			if (isset($this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_freeshippingfrom'])) {
			
			$data['states'][ $geo_zone['geo_zone_id']]['freeshippingfrom'] =  $this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_freeshippingfrom'];
			} else {
			
			$data['states'][ $geo_zone['geo_zone_id']]['freeshippingfrom'] = $this->config->get('shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_freeshippingfrom');
			}
			
			if (isset($this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_status'])) {
			
			$data['states'][ $geo_zone['geo_zone_id']]['status'] =$this->request->post['shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_status'];
			
			} else {
			$data['states'][ $geo_zone['geo_zone_id']]['status'] = $this->config->get('shipping_dpdshops_' . $geo_zone['geo_zone_id'] . '_status');
			}		
		}


		var_dump($data['states']);
		
		$data['geo_zones'] = $geo_zones;

		if (isset($this->request->post['shipping_dpdshops_tax_class_id'])) {
			$data['shipping_dpdshops_tax_class_id'] = $this->request->post['shipping_dpdshops_tax_class_id'];
		} else {
			$data['shipping_dpdshops_tax_class_id'] = $this->config->get('shipping_dpdshops_tax_class_id');
		}
		
	
		if (isset($this->request->post['shipping_dpdshops_disableifhtml'])) {
			$data['shipping_dpdshops_disableifhtml'] = $this->request->post['shipping_dpdshops_disableifhtml'];
		} else {
			$data['shipping_dpdshops_disableifhtml'] = $this->config->get('shipping_dpdshops_disableifhtml');
		}
		if (isset($this->request->post['shipping_dpdshops_maxweight'])) {
			$data['shipping_dpdshops_maxweight'] = $this->request->post['shipping_dpdshops_maxweight'];
		} else {
			$data['shipping_dpdshops_maxweight'] = $this->config->get('shipping_dpdshops_maxweight');
		}
	
	
		
		$this->load->model('localisation/tax_class');
				
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		if (isset($this->request->post['shipping_dpdshops_status'])) {
			$data['shipping_dpdshops_status'] = $this->request->post['shipping_dpdshops_status'];
		} else {
			$data['shipping_dpdshops_status'] = $this->config->get('shipping_dpdshops_status');
		}
		
		if (isset($this->request->post['shipping_dpdshops_sort_order'])) {
			$data['shipping_dpdshops_sort_order'] = $this->request->post['shipping_dpdshops_sort_order'];
		} else {
			$data['shipping_dpdshops_sort_order'] = $this->config->get('shipping_dpdshops_sort_order');
		}	


				
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
 
    $this->response->setOutput($this->load->view('extension/shipping/dpdshops', $data));
	}
		
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/dpdshops')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}