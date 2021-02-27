<?php 
class Netreviews_Pla_Helper_Data extends Mage_Core_Helper_Abstract{
	
	public function getGoogleContentArray(){
		$data[] = array(
			"name" => "id",
			"attribute_value"  => "entity_id"
		);
		$data[] = array(
			"name" => "sku",
			"attribute_value"  => "sku"
		);
		$data[] = array(
			"name" => "product_name",
			"attribute_value" => "product_name"
		);
		$data[] = array(
			"name" => "link",
			"attribute_value" => "url_path"
		);
		$data[] = array(
			"name" => "image_link",
			"attribute_value"  => "image_link"
		);
		$data[] = array(
			"name" => "brand",
			"attribute_value"  => "brand"
		);
		$data[] = array(
			"name" => "category",
			"attribute_value"  => "category"
		);
		$data[] = array(
			"name" => "gtin_ean",
			"attribute_value"  => "gtin_ean"
		);
		$data[] = array(
			"name" => "gtin_upc",
			"attribute_value"  => "gtin_upc"
		);
		$data[] = array(
			"name" => "gtin_jan",
			"attribute_value"  => "gtin_jan"
		);
		$data[] = array(
			"name" => "gtin_isbn",
			"attribute_value"  => "gtin_isbn"
		);
		$data[] = array(
			"name" => "mpn",
			"attribute_value"  => "mpn"
		);
		for($i=1;$i<11;$i++){
			$data[] = array(
				"name" => "Extra Info".$i,
				"attribute_value"  => "info".$i
			);
		}
		
        return $data;
        
    }
}