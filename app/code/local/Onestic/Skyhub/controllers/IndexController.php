<?php
class Onestic_Skyhub_IndexController extends Mage_Core_Controller_Front_Action {

	public function productsAction() {
	    Mage::getModel('onestic_skyhub/updater')->products();
	}
	
	public function categoriesAction() {
	    $api = Mage::getModel('onestic_skyhub/api_categories');
	    $apiCategories = $api->categories();
	    $allowedCats = explode(',', Mage::helper('onestic_skyhub')->getConfig('categories'));
	    var_dump($apiCategories);
	    var_dump($allowedCats);
	    foreach ($allowedCats as $cat) {
	        $category = Mage::getModel('catalog/category')->load($cat);
	        if (!in_array($cat,$apiCategories)) {
	            $retorno = $api->create(array(
	                'category' => array(
	                    'code' => $category->getId(),
	                    'name' => $category->getName()
	                )
	            ));
	            var_dump($retorno);
	        }
	    }
	}
	
	public function ordersAction() {
	    Mage::getModel('onestic_skyhub/updater')->orders();
	}
	
	public function deleteCategoriesAction() {
	    $categories = Mage::getModel('onestic_skyhub/api_categories')->categories();
	    foreach ($categories['body'] as $category) {
	        try {
	           Mage::getModel('onestic_skyhub/api_categories')->remove($category->code);
	           $msg = "<span style='color: green'>REMOVIDO</span>";
	        } catch(Exception $e) {
	            $msg = "<span style='color: red'>" . $e->getMessage() . "</span>";
	        }
	        echo $category->code . " = " . $category->name . " - " . $msg . "<br />";
	    }
	    die();
	}
	
    public function orderAction() {
        $orderID = $this->getRequest()->getParam('id');
        Mage::getModel('onestic_skyhub/updater')->orderFix($orderID);
    }
    
    public function statusAction() {
    	echo "INIT: " . date('H:i:s');
    	//Mage::getModel('onestic_skyhub/updater')->orders();
    	//Mage::getModel('onestic_skyhub/products_updater')->products();
    	
    	/*$resource = Mage::getSingleton('core/resource');
    	$readConnection = $resource->getConnection('core_read');
    	$query = "SELECT o.entity_id FROM `sales_flat_order` as o left join sales_flat_order_grid as g on g.entity_id = o.entity_id where o.created_at between '2017-06-30' AND '2017-07-08' and g.status is null AND o.entity_id is not null";
    	$results = $readConnection->fetchAll($query);
    	
    	foreach ($results as $result) {
    		echo "ID: " . $result['entity_id'] . "<br />";
    		$model = Mage::getModel('sales/order');
    		$model->getResource()->updateGridRecords($result['entity_id']);
    	}*/
    	
    	$collection = Mage::getModel('onestic_skyhub/orders')->getCollection()
    		->addFieldToFilter('status_sync',array('like' => 'NOT_SYNCED'))
    		->addFieldToFilter('order_id',array('null' => true))
			->setPageSize(100)
			->setCurPage(2)
			->setOrder('id');
			
    	$api = Mage::getModel('onestic_skyhub/api_orders');
    	foreach($collection as $order) {
    		/*$result = $api->exported($order->getCode());
    		$message = $api->checkResponseErrors($result['httpCode']);
    		if ($message) {
    			Mage::log('EXPORTED ERROR '.$order->getCode().': ' . $message,null,'onestic_skyhub.log');
    		} else {
    			Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'status_sync','SYNCED');
    			Mage::log('PEDIDO '.$order->getCode().' EXPORTADO COM SUCESSO',null,'onestic_skyhub.log');
    		}*/
    	
			$apiOrder = $api->getOrder($order->getCode());
			if ($apiOrder['httpCode'] == 200) {
				Mage::getModel('onestic_skyhub/order')->create($apiOrder['body']);
				$mOrder = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->getCode());
				if ($mOrder->getId()) {
					$order->setData('order_id',$mOrder->getId());
					$order->setData('increment_id',$mOrder->getIncrementId());
					$order->save();
					$result = Mage::getModel('onestic_skyhub/checker')->checkOrder($mOrder->getId());
					if ($result) {
						echo "Pedido " . $order->getIncrementId() . " sincronizado com Skyhub!<br/>";
					} else {
						echo "Pedido " . $order->getIncrementId() . " não sincronizado com Skyhub!<br/>";
					}
				} else {
					echo "Não foi possível criar o pedido ".$order->getCode()." no Magento, verifique o log de erros para mais informações!<br/>";
				}
			} else {
				echo "Não foi possível recuperar as informações do pedido " . $order->getCode() . " na Skyhub!<br />";
			}
		}
    	
    	echo "<BR />END: " . date('H:i:s');
    }
    
    public function cronAction() {
    	$collection = Mage::getModel('cron/schedule')->getCollection()
    		->addFieldToFilter('job_code',array('like' => 'onestic_skyhub%'))
    		->setOrder('created_at','DESC');
    	echo "<table border='1' cellpadding='5'>
    			<thead>
    				<tr>
    					<th>#</th>
    					<th>Code</th>
    					<th>Status</th>
    					<th>Messages</th>
    					<th>Created</th>
    					<th>Scheduled</th>
    					<th>Executed</th>
    					<th>Finished</th>
    					<th>Reported</th>
    				</tr>
    			</thead>
    			<tbody>
    			";
    	foreach($collection as $cron) {
    		echo "<tr>
    				<td>" . $cron->getScheduleId() . "</td>
    				<td>" . $cron->getJobCode() . "</td>
    				<td>" . $cron->getStatus() . "</td>
    				<td>" . $cron->getMessages() . "</td>
    				<td>" . date('d/m/Y H:i',strtotime($cron->getCreatedAt())) . "</td>
    				<td>" . date('d/m/Y H:i',strtotime($cron->getScheduledAt())) . "</td>
    				<td>" . date('d/m/Y H:i',strtotime($cron->getExecutedAt())) . "</td>
    				<td>" . date('d/m/Y H:i',strtotime($cron->getFinishedAt())) . "</td>
    				<td>" . date('d/m/Y H:i',strtotime($cron->getReportedAt())) . "</td>
    			</tr>";
    	}
    	echo "</tbody></table>";
    	/*if ($handle = opendir('/var/log/php-fpm')) {
    		while (false !== ($entry = readdir($handle))) {
    			if ($entry != "." && $entry != "..") {
    				echo $entry . "<br />";
    			}
    		}
    	
    		closedir($handle);
    	}
    	echo file_get_contents('/var/log/php-fpm/www-error.log');*/
    }

}