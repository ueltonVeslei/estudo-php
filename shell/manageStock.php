<?php

echo date("YmdHis")."-----------------------------------------";
require_once 'abstract.php';

ini_set('display_errors', true);
error_reporting(E_ALL & ~E_NOTICE);

ini_set('memory_limit', '4G');

$root = dirname(dirname(__FILE__));


class Mage_Shell_Onestic extends Mage_Shell_Abstract
{
   

    public function run() {     
       $resource = Mage::getSingleton('core/resource'); 
       $this->core_pre = $resource->getConnection('core_setup'); 
       
       $this->core_pro = $resource->getConnection('pro_db'); 
       
       
    $insert="INSERT IGNORE INTO  `cataloginventory_stock_item` (`item_id` ,`product_id` ,`stock_id` ,`qty` ,
            `min_qty` ,
            `use_config_min_qty` ,
            `is_qty_decimal` ,
            `backorders` ,
            `use_config_backorders` ,
            `min_sale_qty` ,
            `use_config_min_sale_qty` ,
            `max_sale_qty` ,
            `use_config_max_sale_qty` ,
            `is_in_stock` ,
            `low_stock_date` ,
            `notify_stock_qty` ,
            `use_config_notify_stock_qty` ,
            `manage_stock` ,
            `use_config_manage_stock` ,
            `stock_status_changed_auto` ,
            `use_config_qty_increments` ,
            `qty_increments` ,
            `use_config_enable_qty_inc` ,
            `enable_qty_increments` ,
            `is_decimal_divided`
            )
            VALUES";
       
       $insert2="insert ignore into cataloginventory_stock_status values"; 
       
       $web="SELECT website_id FROM  core_website;";
       $webs = $this->core_pre->fetchAll($web);
       
       $sql="SELECT entity_id FROM `catalog_product_entity` WHERE entity_id NOT IN (SELECT product_id FROM  `cataloginventory_stock_item`)";       
       $skus = $this->core_pro->fetchAll($sql);
       foreach($skus as $k => $sku){                                
            $select_product="select `item_id` ,`product_id` ,`stock_id` ,`qty` ,
            `min_qty` ,
            `use_config_min_qty` ,
            `is_qty_decimal` ,
            `backorders` ,
            `use_config_backorders` ,
            `min_sale_qty` ,
            `use_config_min_sale_qty` ,
            `max_sale_qty` ,
            `use_config_max_sale_qty` ,
            `is_in_stock` ,
            `low_stock_date` ,
            `notify_stock_qty` ,
            `use_config_notify_stock_qty` ,
            `manage_stock` ,
            `use_config_manage_stock` ,
            `stock_status_changed_auto` ,
            `use_config_qty_increments` ,
            `qty_increments` ,
            `use_config_enable_qty_inc` ,
            `enable_qty_increments` ,
            `is_decimal_divided`
            from cataloginventory_stock_item where product_id= ".$sku['entity_id'];

            $select_products = $this->core_pre->fetchAll($select_product);
            foreach($select_products as $k => $select){
                foreach($select as $index => $sel){
                    if ($index=='low_stock_date') $select[$index]="'".$select[$index]."'";
                    if (is_null($sel)) $select[$index]="NULL";
                }
                $datos="(".implode(",",$select).")";                
                $insert.=$datos;               
            }            
            //$this->core_setup ->query($insert);
            

            foreach($webs as $k => $web_id){
                $selectstatu="select * from cataloginventory_stock_status where website_id=".$web_id['website_id']." and  product_id=".$sku['entity_id']; 
                $select_status = $this->core_pre->fetchAll($selectstatu);
                foreach($select_status as $k => $select){
                    foreach($select as $index => $sel){
                    if (is_null($sel)) $select[$index]="NULL";
                }
                    $datos="(".implode(",",$select).")";
                    $insert2.=$datos;               
                }  
            }
       }
       
       if (count($skus)>0){
            $insert=  str_replace(")(", "),(", $insert);                     
            $insert.=";";
            echo $insert."\n\n\n";
            $this->core_pro->query($insert);

            $insert2=  str_replace(")(", "),(", $insert2);
            $insert2.=";";
            echo $insert2."\n\n\n";
            $this->core_pro ->query($insert2);
       }
   }
    
    
}


$shell = new Mage_Shell_Onestic();
$shell->run();
echo "------------------------------------------------------------------";