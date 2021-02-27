<?php

class MageMigrator_Migrule_Helper_Sales extends MageMigrator_Migrule_Helper_Abstract {
	
	protected function _import15($listSalesRule){
		return false;
	}
	
	protected function _export15(){
		
		$listSalesRule = array();
		
		// pega as regras
		$fetchSales = $this->_read->fetchAll("SELECT * FROM salesrule");
		
		foreach($fetchSales as $salesrule){

			// instancia o obj Commun
			$commun = Mage::getModel('migrule/commun');
			
			$commun->addData(array(
				'rule_id' 				=> $salesrule['rule_id'],	
				'name' 					=> $salesrule['name'],	
				'description'			=> $salesrule['description'],	
				'from_date' 			=> $salesrule['from_date'],	
				'to_date' 				=> $salesrule['to_date'],	
				'coupons' 				=> array(
											array( 'code' => $salesrule['coupon_code'])
										),	
				'uses_per_coupon' 		=> $salesrule['uses_per_coupon'],	
				'uses_per_customer'		=> $salesrule['uses_per_customer'],	
				'customer_group_ids' 	=> $salesrule['customer_group_ids'],	
				'is_active' 			=> $salesrule['is_active'],	
				'conditions_serialized' => $salesrule['conditions_serialized'],	
				'actions_serialized'	=> $salesrule['actions_serialized'],	
				'stop_rules_processing' => $salesrule['stop_rules_processing'],	
				'is_advanced' 			=> $salesrule['is_advanced'],	
				'product_ids' 			=> $salesrule['product_ids'],	
				'sort_order' 			=> $salesrule['sort_order'],	
				'simple_action' 		=> $salesrule['simple_action'],	
				'discount_amount' 		=> $salesrule['discount_amount'],	
				'discount_qty' 			=> $salesrule['discount_qty'],	
				'discount_step' 		=> $salesrule['discount_step'],	
				'simple_free_shipping' 	=> $salesrule['simple_free_shipping'],	
				'apply_to_shipping' 	=> $salesrule['apply_to_shipping'],	
				'times_used' 			=> $salesrule['times_used'],	
				'is_rss' 				=> $salesrule['is_rss'],	
				'website_ids' 			=> $salesrule['website_ids'],	
				'shipping_fair_type' 	=> $salesrule['shipping_fair_type'],	
				'shipping_fair' 		=> $salesrule['shipping_fair'],	
				'website_ids' 			=> $salesrule['website_ids'],	
			));
			
			// pega as regras de cliente na regra corrente
			$fetchSalesCustomer = $this->_read->fetchAll("SELECT * FROM salesrule_customer WHERE rule_id = '{$salesrule['rule_id']}'");
			
			$listSalesCustomers = array();
			
			foreach($fetchSalesCustomer as $customer){
				$listSalesCustomers[] = array(
					'rule_customer_id'	=> $customer['rule_customer_id'],
					'rule_id'			=> $customer['rule_id'],
					'customer_id' 		=> $customer['customer_id'],
					'times_used' 		=> $customer['times_used'],
				);	
			}
			
			$commun->setData('customers', $listSalesCustomers);
			
			$listSalesRule[] = $commun->getData();
		}
		
		return $listSalesRule;
	}
	
	protected function _import17($listSalesRule){
		
		$sql = '';
		
		foreach($listSalesRule as $salesrule){
			
			$data = $salesrule;
			
			$fetch = $this->_read->fetchAll("SELECT rule_id FROM salesrule WHERE rule_id = '{$data['rule_id']}'");
			if(count($fetch)){
				continue;
			}
			
			$name = addslashes($data['name']);
			$description = addslashes($data['description']);
			$couponType = ($data['coupons'][0]['code'] != '')? 2: 1;
			$fromDate = (empty($data['from_date']))? '': "from_date = '{$data['from_date']}',";
			$toDate = (empty($data['to_date']))? '': "to_date = '{$data['to_date']}',";
			
			$sql .= "
				INSERT INTO salesrule SET
					rule_id = '{$data['rule_id']}',
					name = '{$name}',
					description = '{$description}',
					$fromDate
					$toDate
					uses_per_customer = '{$data['uses_per_customer']}',
					is_active = '{$data['is_active']}',
					conditions_serialized = '{$data['conditions_serialized']}',
					actions_serialized = '{$data['actions_serialized']}',
					stop_rules_processing = '{$data['stop_rules_processing']}',
					is_advanced = '{$data['is_advanced']}',
					product_ids = '{$data['product_ids']}',
					sort_order = '{$data['sort_order']}',
					simple_action = '{$data['simple_action']}',
					discount_amount = '{$data['discount_amount']}',
					discount_qty = '{$data['discount_qty']}',
					discount_step = '{$data['discount_step']}',
					simple_free_shipping = '{$data['simple_free_shipping']}',
					apply_to_shipping = '{$data['apply_to_shipping']}',
					times_used = '{$data['times_used']}',
					is_rss = '{$data['is_rss']}',
					coupon_type = '{$couponType}',
					use_auto_generation = '0',
					uses_per_coupon = '{$data['uses_per_coupon']}',
					shipping_fair_type = '{$data['shipping_fair_type']}',
					shipping_fair = '{$data['shipping_fair']}';
			";
			
			
			foreach($data['coupons'] as $coupon){
				if($coupon['code']){
					$sql .= "
						INSERT INTO salesrule_coupon SET
							rule_id = '{$data['rule_id']}',		
							code = '{$coupon['code']}',		
							usage_limit = '{$data['uses_per_coupon']}',		
							usage_per_customer = '{$data['uses_per_customer']}',		
							times_used = '{$data['times_used']}',		
							expiration_date = '{$data['to_date']}',
							is_primary = 1,
							created_at = NOW();
					";
				}
			}
			
			if(!empty($data['customers'])){
				foreach($data['customers'] as $customer){
					$sql .= "
						INSERT INTO salesrule_customer SET
							rule_id = '{$data['rule_id']}',		
							customer_id = '{$customer['customer_id']}',		
							times_used = '{$customer['times_used']}';
					";
				} 
			}
			
			if(!empty($data['website_ids'])){
				$websites = explode(',',$data['website_ids']);
				foreach($websites as $websiteId){
					$sql .= "
						INSERT INTO salesrule_website SET
							rule_id = '{$data['rule_id']}',		
							website_id = '{$websiteId}';
					";
				}
			}
			
			if(!empty($data['customer_group_ids'])){
				$customerGroups = explode(',',$data['customer_group_ids']);
				foreach($customerGroups as $groupId){
					$sql .= "
						INSERT INTO salesrule_customer_group SET
							rule_id = '{$data['rule_id']}',		
							customer_group_id = '{$groupId}';
					";
				}
			}
			
		}
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.sql';
		
		file_put_contents(Mage::getRoot() . '/../migrator/export/rules/salesrulesql/' . $filename, $sql);
		
		$process = Mage::getModel('migrator/process');
		$process->import('migrule',$filename, 'executeImportSalesRuleSql');
		$process->getManager()->work();
		
	}
	
	protected function _export17(){
		return false;
	}
	
}