<?php 
class ControllerProductSearchJson extends Controller { 	
	public function index() { 
    	$this->language->load('product/search_json');
				
		$url = '';
		
		if (isset($this->request->get['keyword'])) {
			$url .= '&keyword=' . $this->request->get['keyword'];
		}
		
		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}
		
		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}
		
		if (isset($this->request->get['model'])) {
			$url .= '&model=' . $this->request->get['model'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}	
 
   
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['keyword'])) {
			$this->data['keyword'] = $this->request->get['keyword'];
		} else {
			$this->data['keyword'] = '';
		}

		if (isset($this->request->get['category_id'])) {
			$this->data['category_id'] = $this->request->get['category_id'];
		} else {
			$this->data['category_id'] = '';
		}

		$this->load->model('catalog/category');
		
		$this->data['categories'] = $this->getCategories(0);
		
		if (isset($this->request->get['description'])) {
			$this->data['description'] = $this->request->get['description'];
		} else {
			$this->data['description'] = '';
		}
		
		if (isset($this->request->get['model'])) {
			$this->data['model'] = $this->request->get['model'];
		} else {
			$this->data['model'] = '';
		}
		
		
        $json = array();
        
		$this->data['products'] = array();
        
		if (isset($this->request->get['keyword'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/smartsearch');
			
			$product_total = $this->model_catalog_smartsearch->getTotalProductsByKeyword($this->request->get['keyword'], isset($this->request->get['category_id']) ? $this->request->get['category_id'] : '', isset($this->request->get['description']) ? $this->request->get['description'] : '', isset($this->request->get['model']) ? $this->request->get['model'] : '');
			$product_tag_total = 0;
			//$product_tag_total = $this->model_catalog_smartsearch->getTotalProductsByTag($this->request->get['keyword'], isset($this->request->get['category_id']) ? $this->request->get['category_id'] : '');
			
						

			$product_total = max($product_total, $product_tag_total);
                  
			$json['total_products'] = $product_total;
			if ($product_total) {
				$url = '';

				if (isset($this->request->get['category_id'])) {
					$url .= '&category_id=' . $this->request->get['category_id'];
				}
		
				if (isset($this->request->get['description'])) {
					$url .= '&description=' . $this->request->get['description'];
				}
				
				if (isset($this->request->get['model'])) {
					$url .= '&model=' . $this->request->get['model'];
				}
				     
				$this->load->model('catalog/review');
				//$this->load->model('tool/seo_url'); 
				$this->load->model('tool/image');
				
				$this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');
				
        		
        		
				$results = $this->model_catalog_smartsearch->getProductsByKeyword($this->request->get['keyword'], isset($this->request->get['category_id']) ? $this->request->get['category_id'] : '', isset($this->request->get['description']) ? $this->request->get['description'] : '', isset($this->request->get['model']) ? $this->request->get['model'] : '', $sort, $order, 0, 5);
                                
        		//$tag_results = $this->model_catalog_smartsearch->getProductsByTag($this->request->get['keyword'], isset($this->request->get['category_id']) ? $this->request->get['category_id'] : '', $sort, $order, 0, 5);
                        
        		         $tag_results=array();
				foreach ($results as $key => $value) {
					$tag_results[$value['product_id']] = $results[$key];
				}
				
				//$product_total = count($tag_results);
                            
				foreach ($tag_results as $result) {
					if ($this->config->get('config_review')) {
						$rating = $this->model_catalog_smartsearch->getAverageRating($result['product_id']);	
					} else {
						$rating = false;
					}
					
//					$special = FALSE;
//					
//					//$discount = $this->model_catalog_product->getProductDiscount($result['product_id']);
//					//$discount = $this->model_catalog_smartsearch->getProductDiscount($result['product_id']);
// 					
////					if ($discount) {
////						$price = $this->currency->format($this->tax->calculate($discount, $result['tax_class_id'], $this->config->get('config_tax')));
////					} else {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
////					
////						//$special = $this->model_catalog_product->getProductSpecial($result['product_id']);
////						//$special = $this->model_catalog_smartsearch->getProductSpecial($result['product_id']);
////					
//////						if ($special) {
//////							$special = $this->currency->format($this->tax->calculate($special, $result['tax_class_id'], $this->config->get('config_tax')));
//////						}					
////					}
//					
					$this->data['products'][] = array(
            			'name'    => $result['name'],
						'model'   => $result['model'],
            			'price'   => $price,
						//'href'    => $this->model_tool_seo_url->rewrite(HTTP_SERVER . 'index.php?route=product/product&keyword=' . $this->request->get['keyword'] . $url . '&product_id=' . $result['product_id']),
						'href'    => HTTP_SERVER . 'index.php?route=product/product&keyword=' . $this->request->get['keyword'] . $url . '&product_id=' . $result['product_id'],
          			);
                        }
				
        		$url = '';
				
				if (isset($this->request->get['keyword'])) {
					$url .= '&keyword=' . $this->request->get['keyword'];
				}
				
				if (isset($this->request->get['category_id'])) {
					$url .= '&category_id=' . $this->request->get['category_id'];
				}
				
				if (isset($this->request->get['description'])) {
					$url .= '&description=' . $this->request->get['description'];
				}
				
				if (isset($this->request->get['model'])) {
					$url .= '&model=' . $this->request->get['model'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}	
				
				$this->data['sorts'] = array();
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_default'),
					'value' => 'p.sort_order-ASC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=p.sort_order&order=ASC'
				);
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_name_asc'),
					'value' => 'pd.name-ASC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=pd.name&order=ASC'
				); 

				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_name_desc'),
					'value' => 'pd.name-DESC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=pd.name&order=DESC'
				);

				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_price_asc'),
					'value' => 'p.price-ASC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=p.price&order=ASC'
				); 

				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_price_desc'),
					'value' => 'p.price-DESC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=p.price&order=DESC'
				); 
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=rating&order=DESC'
				); 
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=rating&order=ASC'
				);
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_model_asc'),
					'value' => 'p.model-ASC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=p.model&order=ASC'
				); 

				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_model_desc'),
					'value' => 'p.model-DESC',
					'href'  => HTTP_SERVER . 'index.php?route=product/search' . $url . '&sort=p.model&order=DESC'
				);
				
				$url = '';

				if (isset($this->request->get['keyword'])) {
					$url .= '&keyword=' . $this->request->get['keyword'];
				}
				
				if (isset($this->request->get['category_id'])) {
					$url .= '&category_id=' . $this->request->get['category_id'];
				}
				
				if (isset($this->request->get['description'])) {
					$url .= '&description=' . $this->request->get['description'];
				}
				
				if (isset($this->request->get['model'])) {
					$url .= '&model=' . $this->request->get['model'];
				}
				
				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}	

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}
				
				$this->data['sort'] = $sort;
				$this->data['order'] = $order;
			}
		}
		
		if(empty($this->data['products']))
		{
			$this->data['products'][] = array(
				'name'    => $this->language->get('text_no_result'),
				'model'   => '',
            	'price'   => '',
				'href'    => ''
			);
		}
		elseif($json['total_products'] > count($this->data['products']))
		{
			$remainder_cnt = $json['total_products'] - count($this->data['products']);
			if($remainder_cnt > 0)
			{
				$this->data['products'][] = array(
					'name'    => $remainder_cnt. ' more results',
					'model'   => '',
	            	'price'   => '',
					'href'    => HTTP_SERVER . 'index.php?route=product/search&keyword='.$this->request->get['keyword'].'&category_id='.$this->request->get['category_id']
				);
			}
		}
		
		$json['result'] = $this->data['products'];
		
  		$this->load->helper('json');

		//$this->response->setOutput($this->request->get['callback'].'('. json_encode($json).')');
		$jsonenc = json_encode($json);
		
		echo $jsonenc;
                
  	}
	
	private function getCategories($parent_id, $level = 0) {
		$level++;
		
		$data = array();
		
		$results = $this->model_catalog_category->getCategories($parent_id);
		
		foreach ($results as $result) {
			$data[] = array(
				'category_id' => $result['category_id'],
				'name'        => str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $result['name']
			);
			
			$children = $this->getCategories($result['category_id'], $level);
			
			if ($children) {
			  $data = array_merge($data, $children);
			}
		}
		
		return $data;
	}	
}
