<?php
/**
 * Onestic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Integrator
 * @package    Onestic_Overcoupom
 * @copyright  Copyright (c) 2018 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Onestic_Overcoupom_Model_Observer
{
        
        
        function updateOrder(Varien_Event_Observer $observer)
        {
            
            $incrementId = $observer->getOrder()->getIncrementId();
            $orderId = $observer->getOrder()->getId();

            $orderfull = Mage::getModel('sales/order')->load($orderId);
            $couponCode = strtolower($orderfull->getCouponCode());

            $couponcodes = array();

	        for ($i=1;$i<=100;$i++) {
	            $couponcodes[] = Mage::helper('onestic_overcoupom')->getConfig('cupom'.$i);
	        }

	        array_change_key_case($couponcodes,CASE_LOWER);

	        if ((in_array($couponCode, $couponcodes))&&($couponCode != "")) {
            
	            $saveDirectory =  "/var/export/nestle";
	            $filename = "nestle.txt";

	            $baseDirectory = Mage::getBaseDir()."/";

	            $filename = $baseDirectory.$saveDirectory."/".$filename;


	            $saveDirectory = trim($saveDirectory, '/');
	            $newDirectory = "";
	            foreach(explode('/',$saveDirectory) as $val) {
	                if(!is_dir($baseDirectory.$newDirectory.$val)){
	                    mkdir($baseDirectory.$newDirectory.$val, 0755);
	                    chmod($baseDirectory.$newDirectory.$val, 0755);
	                }
	                $newDirectory .= $val."/";
	            }
	            $myfile = fopen($filename, "a") or die("erro ao abrir / criar arquivo!");
	            $txt = date('Y-m-d H:i:s'). ";" . $incrementId . ";" . $couponCode . PHP_EOL;
	            fwrite($myfile, $txt);
	            fclose($myfile);


				$coupon = array(
				       'increment_id' => $incrementId,
				       'couponcode' => $couponCode,
				       'created_at' => date('Y-m-d H:i:s')
				);
				  
			 	Mage::getModel('onestic_overcoupom/coupon')
				       ->setData($coupon)
				       ->save();
				
        	} else if ($couponCode != "") {

	        	$coupon = array(
				       'increment_id' => $incrementId,
				       'couponcode'   => $couponCode,
				       'created_at'   => date('Y-m-d H:i:s')
				);
				  
			 	Mage::getModel('onestic_overcoupom/coupon')
				       ->setData($coupon)
				       ->save();

			}

        }
        
    }