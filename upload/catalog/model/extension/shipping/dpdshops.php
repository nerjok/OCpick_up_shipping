<?php
/*
* @package		dpdshops
* @copyright	2017 e-tools.lt
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @license		GNU/GPL based on AceShop www.joomace.net
*
*checkout.twig #collapse-shipping-method select, 
*/

 
class ModelExtensionShippingDpdshops extends Model {    

    public $terminals_url = 'ftp://ftp.dpd.ee/parcelshop/psexport_latest.csv';

  	public function getQuote($address) {
		$this->language->load('extension/shipping/dpdshops');
		
		$quote_data = array();
		



	
		$products = $this->cart->getProducts();
		$this->load->model('catalog/product');
		//var_dump($products);
	/*	foreach ($products as $product)
		{
			$product_obj = $this->model_catalog_product->getProduct($product['product_id']);
			if ($this->config->get('shipping_dpdshops_disableifhtml'))
			{
				if (strpos($product_obj['description'],'no omiva_module') !== false)
				{
					return array();
				}
			}
			if ($this->config->get('shipping_dpdshops_maxweight')>0)
			{
				if ($product_obj['weight']>$this->config->get('shipping_dpdshops_maxweight'))
				{
					return array();
				}
			}
		}
		*/


  

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");
	
		foreach ($query->rows as $result) {
			if ($this->config->get('shipping_dpdshops_' . $result['geo_zone_id'] . '_status')) {
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
				$dpdshops_cartWeight = $this->cart->getWeight();
				
				if ($this->config->get('shipping_dpdshops_price'))
				{
					$cost = $this->config->get('shipping_dpdshops_price');
				}
				
				if ($this->config->get('shipping_dpdshops_' . $result['geo_zone_id'] . '_baseshippingprice'))
				{
					$cost = $this->config->get('shipping_dpdshops_' . $result['geo_zone_id'] . '_baseshippingprice');
				}
				
	
				
				$dpdshops_count_over_limit = ceil($dpdshops_cartWeight/10) - 1;
				if (($dpdshops_count_over_limit>0) && ($this->config->get('shipping_dpdshops_' . $result['geo_zone_id'] . '_priceperadditional')>0))
				{
					$cost += $dpdshops_count_over_limit * $this->config->get('shipping_dpdshops_' . $result['geo_zone_id'] . '_priceperadditional');
				}

		if( ( $handle = fopen( $this->terminals_url, "r" ) ) !== FALSE ) {
			while( ( $data = fgetcsv( $handle, 1000, "|" ) ) !== FALSE ) {
				$shop_location_id = $data[22];
				$shop_country     = substr( $shop_location_id, 0, 2 );


                if ($shop_country =='LT') {
                    $terminals[$data[5]][]      = (object) array(
                        'place_id'   => $shop_location_id,
                        'zipcode'    => $data[4],
                        'name'       => utf8_encode( $data[2] ),
                        'address'    => utf8_encode( $data[3] ),
                        'city'       => utf8_encode( $data[5] )
                    
                    );
                }
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
					$quote_data['dpdshops_' . $result['geo_zone_id']] = array(
						'code'         => 'dpdshops.dpdshops_' . $result['geo_zone_id'],
						'title'        => $dropSelect,
						'cost'         => $cost,
						'tax_class_id' => $this->config->get('shipping_dpdshops_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_dpdshops_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
					);	
				}
			}
		}
		
		$method_data = array();
	
		if ($quote_data) {
      		$method_data = array(
        		'code'       => 'dpdshops',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_dpdshops_sort_order'),
        		'error'      => false
      		);
		}
	
		return $method_data;
  	}
}
