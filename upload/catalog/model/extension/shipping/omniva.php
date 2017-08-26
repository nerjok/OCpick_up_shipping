<?php
/*
* @package		Omniva
* @copyright	2017 e-tools.lt
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @license		GNU/GPL based on AceShop www.joomace.net
*
*checkout.twig #collapse-shipping-method select, 
*/

 
class ModelExtensionShippingOmniva extends Model {    

    public $terminals_url = 'https://www.omniva.lt/locations.json';

  	public function getQuote($address) {
		$this->language->load('extension/shipping/omniva');
		
		$quote_data = array();
		



	
		$products = $this->cart->getProducts();
		$this->load->model('catalog/product');
		//var_dump($products);
		foreach ($products as $product)
		{
			$product_obj = $this->model_catalog_product->getProduct($product['product_id']);
			if ($this->config->get('shipping_omniva_disableifhtml'))
			{
				if (strpos($product_obj['description'],'no omiva_module') !== false)
				{
					return array();
				}
			}
			if ($this->config->get('shipping_omniva_maxweight')>0)
			{
				if ($product_obj['weight']>$this->config->get('shipping_omniva_maxweight'))
				{
					return array();
				}
			}
		}
		


  

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");
	
		foreach ($query->rows as $result) {
			if ($this->config->get('shipping_omniva_' . $result['geo_zone_id'] . '_status')) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			
				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}
		
			if ($status) {
				$cost = '';
				$omniva_cartWeight = $this->cart->getWeight();
				
				if ($this->config->get('shipping_omniva_price'))
				{
					$cost = $this->config->get('shipping_omniva_price');
				}
				
				if ($this->config->get('shipping_omniva_' . $result['geo_zone_id'] . '_baseshippingprice'))
				{
					$cost = $this->config->get('shipping_omniva_' . $result['geo_zone_id'] . '_baseshippingprice');
				}
				
	
				
				$omniva_count_over_limit = ceil($omniva_cartWeight/10) - 1;
				if (($omniva_count_over_limit>0) && ($this->config->get('shipping_omniva_' . $result['geo_zone_id'] . '_priceperadditional')>0))
				{
					$cost += $omniva_count_over_limit * $this->config->get('shipping_omniva_' . $result['geo_zone_id'] . '_priceperadditional');
				}

		$terminals_json  = file_get_contents( $this->terminals_url );
		$terminals_json  = json_decode( $terminals_json );

	//	$filter_country  = $filter_country ? $filter_country : $this->get_shipping_country();
		$locations       = array();

		foreach( $terminals_json as $key => $location ) {
			if( $location->A0_NAME == 'LT'){ //&& $location->TYPE == $filter_type ) {
				$terminals[$location->A1_NAME][] = (object) array(
					'place_id'   => $location->ZIP,
					'zipcode'    => $location->ZIP,
					'name'       => $location->NAME,
					'address'    => $location->A1_NAME,
					'city'       => $location->A2_NAME,
				);
			}
		}
				        $dropSelect = '';


						$dropSelect .= '<select name="terminal" class="form-control form-inline" style="width: 80%; display: inline;" >';
  	                    foreach( $terminals as $group_name => $locations ) :
				            
                            $dropSelect .= '<optgroup label="'.$group_name.'">';                       
                            foreach( $locations as $location ):
                                
                                 $dropSelect .=   "<option value='". $location->place_id.'|' .$location->name ."' >".$location->name ."</option>";

                            endforeach;
			        $dropSelect .= '</optgroup>';

                        endforeach;

						$dropSelect .= "</select>";
						





				
				if ((string)$cost != '') { 
					$quote_data['omniva_' . $result['geo_zone_id']] = array(
						'code'         => 'omniva.omniva_' . $result['geo_zone_id'],
						'title'        => $dropSelect,
						'cost'         => $cost,
						'tax_class_id' => $this->config->get('shipping_omniva_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_omniva_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
					);	
				}
			}
		}
		
		$method_data = array();
	
		if ($quote_data) {
      		$method_data = array(
        		'code'       => 'omniva',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_omniva_sort_order'),
        		'error'      => false
      		);
		}
	
		return $method_data;
  	}
}
