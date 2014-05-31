<?php


class ModelCatalogSmartsearch extends Model {


	public function getProductsByTag($tag, $category_id = 0, $sort = 'p.sort_order', $order = 'ASC', $start = 0, $limit = 20) {
		if ($tag) {
		
			$sql = "SELECT *, pd.name AS name, p.image, m.name AS manufacturer, ss.name AS stock, (SELECT AVG(r.rating) FROM " . DB_PREFIX . "review r WHERE p.product_id = r.product_id GROUP BY r.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_tags pt ON (p.product_id = pt.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (p.stock_status_id = ss.stock_status_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "' AND (LCASE(pt.tag) = '" . $this->db->escape(strtolower($tag)) . "'";

			$keywords = explode(" ", $tag);
						
			foreach ($keywords as $keyword) {
				$sql .= " OR LCASE(pt.tag) = '" . $this->db->escape(strtolower($keyword)) . "'";
			}
			
			$sql .= ")";
			
			if ($category_id) {
				$data = array();
				
				$this->load->model('catalog/category');
				
				$string = rtrim($this->getPath($category_id), ',');
				
				foreach (explode(',', $string) as $category_id) {
					$data[] = "category_id = '" . (int)$category_id . "'";
				}
				
				$sql .= " AND p.product_id IN (SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE " . implode(" OR ", $data) . ")";
			}
		
			$sql .= " AND p.status = '1' AND p.date_available <= NOW() GROUP BY p.product_id";
		
			$sort_data = array(
				'pd.name',
				'p.sort_order',
				'special',
				'rating',
				'p.price',
				'p.model'
			);
				
			if (in_array($sort, $sort_data)) {
				if ($sort == 'pd.name' || $sort == 'p.model') {
					$sql .= " ORDER BY LCASE(" . $sort . ")";
				} else {
					$sql .= " ORDER BY " . $sort;
				}
			} else {
				$sql .= " ORDER BY p.sort_order";	
			}
			
			if ($order == 'DESC') {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if ($start < 0) {
				$start = 0;
			}
		
			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
			
			$query = $this->db->query($sql);
			
			$products = array();
			
			foreach ($query->rows as $key => $value) {
				$products[$value['product_id']] = $this->getProduct($value['product_id']);
			}
			
			return $products;
		}
	}
	
	public function getProductsByKeyword($keyword, $category_id = 0, $description = FALSE, $model = FALSE, $sort = 'p.sort_order', $order = 'ASC', $start = 0, $limit = 20) {
		if ($keyword) {
						
			$sql = "SELECT *, pd.name AS name, p.image, m.name AS manufacturer, ss.name AS stock, (SELECT AVG(r.rating) FROM " . DB_PREFIX . "review r WHERE p.product_id = r.product_id GROUP BY r.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (p.stock_status_id = ss.stock_status_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			if (!$description) {
				$sql .= " AND (LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%'";
			} else {
				$sql .= " AND (LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%'";
			}
			
			if (!$model) {
				$sql .= ")";
			} else {
				$sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%')";
			}
			
			if ($category_id) {
				$data = array();
				
				$this->load->model('catalog/category');
				
				$string = rtrim($this->getPath($category_id), ',');
				
				foreach (explode(',', $string) as $category_id) {
					$data[] = "category_id = '" . (int)$category_id . "'";
				}
				
				$sql .= " AND p.product_id IN (SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE " . implode(" OR ", $data) . ")";
			}
		
			$sql .= " AND p.status = '1' AND p.date_available <= NOW() GROUP BY p.product_id";
		
			$sort_data = array(
				'pd.name',
				'p.sort_order',
				'special',
				'rating',
				'p.price',
				'p.model'
			);
				
			if (in_array($sort, $sort_data)) {
				if ($sort == 'pd.name' || $sort == 'p.model') {
					$sql .= " ORDER BY LCASE(" . $sort . ")";
				} else {
					$sql .= " ORDER BY " . $sort;
				}
			} else {
				$sql .= " ORDER BY p.sort_order";	
			}
			
			if ($order == 'DESC') {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if ($start < 0) {
				$start = 0;
			}
		
			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;

			$query = $this->db->query($sql);
						
			return $query->rows;
		}
		return 0;	
	}
	
	public function getTotalProductsByKeyword($keyword, $category_id = 0, $description = FALSE, $model = FALSE) {
		if ($keyword) {
			$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			
			if (!$description) {
				$sql .= " AND (LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%'";
			} else {
				$sql .= " AND (LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%'";
			}

			if (!$model) {
				$sql .= ")";
			} else {
				$sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%')";
			}

			if ($category_id) {
				$data = array();
				
				$this->load->model('catalog/category');
				
				$string = rtrim($this->getPath($category_id), ',');
				
				foreach (explode(',', $string) as $category_id) {
					$data[] = "category_id = '" . (int)$category_id . "'";
				}
				
				$sql .= " AND p.product_id IN (SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE " . implode(" OR ", $data) . ")";
			}
			
			$sql .= " AND p.status = '1' AND p.date_available <= NOW()";
			
			$query = $this->db->query($sql);
		
			if ($query->num_rows) {
				return $query->row['total'];
			} else {
				return 0;
			}
		}
		return 0;
	}
	
	public function getTotalProductsByTag($tag, $category_id = 0) {
		if ($tag) {
		
			$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_tags pt ON (p.product_id = pt.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (p.stock_status_id = ss.stock_status_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "' AND (LCASE(pt.tag) = '" . $this->db->escape(strtolower($tag)) . "'";

			$keywords = explode(" ", $tag);
						
			foreach ($keywords as $keyword) {
				$sql .= " OR LCASE(pt.tag) = '" . $this->db->escape(strtolower($keyword)) . "'";
			}
			
			$sql .= ")";
			
			if ($category_id) {
				$data = array();
				
				$this->load->model('catalog/category');
				
				$string = rtrim($this->getPath($category_id), ',');
				
				foreach (explode(',', $string) as $category_id) {
					$data[] = "category_id = '" . (int)$category_id . "'";
				}
				
				$sql .= " AND p.product_id IN (SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE " . implode(" OR ", $data) . ")";
			}
		
			$sql .= " AND p.status = '1' AND p.date_available <= NOW()";
					
			$query = $this->db->query($sql);
			
			if ($query->num_rows) {
				return $query->row['total'];
			} else {
				return 0;
			}
		}
		return 0;
	}

   	public function getAverageRating($product_id) {
		$query = $this->db->query("SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review WHERE status = '1' AND product_id = '" . (int)$product_id . "' GROUP BY product_id");
		
		if (isset($query->row['total'])) {
			return round($query->row['total']);
		} else {
			return 0;
		}
	}	
//    
//    public function getProductRelated($product_id) {
//		$product_data = array();
//
//		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
//		
//		foreach ($query->rows as $result) { 
//			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
//		}
//		
//		return $product_data;
//	}
//
//    public function getProductRelatedByTag($product_id) {
//        $product_data = array();
//        $where_tags = array();
//
//        $product_tags = $this->getProductTags($product_id);
//
//        if (!empty($product_tags)) {
//            foreach ($product_tags as $product_tag) {
//                $where_tags[] = $product_tag["tag"];
//            }
//            unset($product_tags);
//
//            $related_ids = $this->db->query("
//				SELECT product_id 
//				FROM " . DB_PREFIX . "product_tags 
//				WHERE tag IN ('" . implode("','", $where_tags) . "') 
//				      AND product_id NOT IN ('" . (int) $product_id . "') 
//				ORDER BY RAND() LIMIT 10");
//
//            foreach ($related_ids->rows as $related_id) {
//                $related_product_id = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, ss.name AS stock, (SELECT AVG(r.rating) FROM " . DB_PREFIX . "review r WHERE p.product_id = r.product_id GROUP BY r.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (p.stock_status_id = ss.stock_status_id) WHERE p.product_id = '" . (int) $related_id['product_id'] . "' AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "' AND ss.language_id = '" . (int) $this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");
//
//                if ($related_product_id->num_rows) {
//                    $product_data[$related_id['product_id']] = $related_product_id->row;
//                }
//            }
//        }
//
//        return $product_data;
//    }
//    
//    	public function getProductTags($product_id) {
//		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tag WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
//
//		return $query->rows;
//	}
//        
        

}
