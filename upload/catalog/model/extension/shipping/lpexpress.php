<?php
/*
* @package		lpexpress
* @copyright	2017 e-tools.lt
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @license		GNU/GPL based on AceShop www.joomace.net
*
*checkout.twig #collapse-shipping-method select, 
*/

 
class ModelExtensionShippingLpexpress extends Model {    

    public $terminals_url = 'https://www.lpexpress.lt/index.php?cl=terminals&fnc=getTerminals';

  	public function getQuote($address) {
		$this->language->load('extension/shipping/lpexpress');
		
		$quote_data = array();
		



	
		$products = $this->cart->getProducts();
		$this->load->model('catalog/product');
		//var_dump($products);
	/*	foreach ($products as $product)
		{
			$product_obj = $this->model_catalog_product->getProduct($product['product_id']);
			if ($this->config->get('shipping_lpexpress_disableifhtml'))
			{
				if (strpos($product_obj['description'],'no omiva_module') !== false)
				{
					return array();
				}
			}
			if ($this->config->get('shipping_lpexpress_maxweight')>0)
			{
				if ($product_obj['weight']>$this->config->get('shipping_lpexpress_maxweight'))
				{
					return array();
				}
			}
		}
		*/


  

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");
	
		foreach ($query->rows as $result) {
			if ($this->config->get('shipping_lpexpress_' . $result['geo_zone_id'] . '_status')) {
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
				$lpexpress_cartWeight = $this->cart->getWeight();
				
				if ($this->config->get('shipping_lpexpress_price'))
				{
					$cost = $this->config->get('shipping_lpexpress_price');
				}
				
				if ($this->config->get('shipping_lpexpress_' . $result['geo_zone_id'] . '_baseshippingprice'))
				{
					$cost = $this->config->get('shipping_lpexpress_' . $result['geo_zone_id'] . '_baseshippingprice');
				}
				
	
				
				$lpexpress_count_over_limit = ceil($lpexpress_cartWeight/10) - 1;
				if (($lpexpress_count_over_limit>0) && ($this->config->get('shipping_lpexpress_' . $result['geo_zone_id'] . '_priceperadditional')>0))
				{
					$cost += $lpexpress_count_over_limit * $this->config->get('shipping_lpexpress_' . $result['geo_zone_id'] . '_priceperadditional');
				}

		$terminals_json  = file_get_contents( $this->terminals_url );
		$terminals_json  = json_decode( $terminals_json );

	//	$filter_country  = $filter_country ? $filter_country : $this->get_shipping_country();
		$locations       = array();



        		foreach( $terminals_json as $key => $location ) {
			if( $location->nfqactive == 1 ) {
				$terminals[$location->city][] = (object) array(
					'place_id'   => $location->oxid,
					'zipcode'    => $location->zip,
					'name'       => $location->name,
					'city'       => $location->city,
					'address'    => $location->address,
					'comment'    => $location->comment
				);
			}
		}
				        $dropSelect = '';

						$dropSelect .= '<select name="terminal" class="form-control form-inline" style="width: 80%; display: inline;" >';
                       	foreach( $terminals as $group_name => $locations ) :
				            
                            $dropSelect .= '<optgroup label="'.$group_name.'">';
                            
                            foreach( $locations as $location ):
                                
                                 $dropSelect .=   "<option value='". $location->place_id.'|' .$location->name ."' >".$location->name .' '. $location->address ."</option>";

                            endforeach;

                            $dropSelect .= '</optgroup>';
                        endforeach;
			
						$dropSelect .= "</select>";
						





				
				if ((string)$cost != '') { 
					$quote_data['lpexpress_' . $result['geo_zone_id']] = array(
						'code'         => 'lpexpress.lpexpress_' . $result['geo_zone_id'],
						'title'        => $dropSelect,
						'cost'         => $cost,
						'tax_class_id' => $this->config->get('shipping_lpexpress_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_lpexpress_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
					);	
				}
			}
		}
		
		$method_data = array();
	
		if ($quote_data) {
      		$method_data = array(
        		'code'       => 'lpexpress',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_lpexpress_sort_order'),
        		'error'      => false
      		);
		}
	
		return $method_data;
  	}
}
