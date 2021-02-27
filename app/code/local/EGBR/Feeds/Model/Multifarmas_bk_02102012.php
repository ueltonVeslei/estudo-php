<?php
/**
 * @desc Model gerar feed XML para Multifarmas - STeV
 * 
 * @author Estevam Neves
 *
 */
final class EGBR_Feeds_Model_Multifarmas extends EGBR_Feeds_Model_GerarFeed{
    /**
    * This action will generate multifarmas.xml to save to <website>/var/multifarmas/
    * @return void
    */
    private $count;
    private $manufaturers;
    
    public function getStoreAttributes(){
        $attArr = array('description','manufacturer','special_price','tx_principal_indicacao');
        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        $sql_att = "
            SELECT attribute_id, attribute_code, backend_type 
            FROM eav_attribute
            WHERE entity_type_id = ".$this->product_entity_key." AND ( attribute_code = 'name'
        ";
        
        foreach($attArr as $att) $sql_att .= " OR attribute_code = '".$att."' ";
        $sql_att .= ")";
        
        return $readConnection->fetchAll($sql_att);
    }
    
    private function getCustomStoreCollection(){
        $default_args = array(
            'website_id' => 1,
            'status' => 1,
            'limit' => false
        );
        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $attResArr = $this->getStoreAttributes();
        
        // Main SQL - STeV
        $sql_join = "";
        $sql_selectArr = array();
        
        for($i=0;$i<count($attResArr);$i++):
            $attEl = "att".$i;
            $sql_selectArr[] = " $attEl.value as ".$attResArr[$i]['attribute_code'];
            $sql_join .= " LEFT JOIN catalog_product_entity_".$attResArr[$i]['backend_type']." $attEl ON( $attEl.entity_id = CPF.entity_id AND $attEl.attribute_id = ".$attResArr[$i]['attribute_id'].")";
        endfor;
        
        $sql = "SELECT CPF.entity_id as ID, CPF.sku, CPF.short_description, CPF.nr_ministerio_da_saude, CPF.price, CPF.url_path, CPF.small_image, CPEI_status.value, CISI.qty, CISI.is_in_stock ";
        if($sql_selectArr) $sql .= ', '.implode(',',$sql_selectArr);
        
        // Status 1.4 = 69, 1.7 = 273
        $sql .= "
                FROM catalog_product_flat_1 CPF
                LEFT JOIN catalog_product_entity_int CPEI_status ON( CPEI_status.entity_id = CPF.entity_id AND CPEI_status.attribute_id = 273)
                LEFT JOIN catalog_product_website CPW ON(CPW.product_id = CPF.entity_id)
                LEFT JOIN catalog_product_entity_int CPEI_multifarmas ON( CPEI_multifarmas.entity_id = CPF.entity_id AND CPEI_multifarmas.attribute_id = 1029)
                LEFT JOIN cataloginventory_stock_item CISI ON(CISI.product_id = CPF.entity_id)";
        $sql .= $sql_join;
        $sql .= "
                WHERE CPEI_status.value = ".$default_args['status']." 
                AND CPEI_multifarmas.value = 1
                AND CPW.website_id = ".$default_args['website_id']." 
                AND CPF.visibility = 4
                GROUP BY CPF.entity_id 
                ORDER BY CPEI_status.value DESC";
        if($default_args['limit']) $sql .= " LIMIT ".$default_args['limit'];
        
        return $readConnection->fetchAll($sql);
    }
    
    public function bySql(){
        echo'<pre>';
        print_r($this->getCustomStoreCollection());
        echo'</pre>';
    }
    
    public function toMultiFarmas(){
        
        $send_multifarmas = Mage::getStoreConfig('catalog/multifarmas/send_to_multifarmas');
        if($send_multifarmas == 0) die("A atualziação de XML da multifarma encontra-se desabilitada.");
        parent::initDoc();
        
//        $_products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter(array(array('attribute' => 'enviar_para_multifarmas','eq' => '1')))->getAllIds();
        $_products = $this->getCustomStoreCollection();
//        ->addFieldToFilter('visibility',array('eq'=>4))
//        ->getSelectSql(true)
        
        echo'<br />Início: '.date('d/m/Y h:i:s');
        echo'<br />Multifarmas product count: '.count($_products);
        $this->count = 0;
        if(count($_products)) $this->formatProductsMultifarmas($_products);
        echo'<br />Fim: '.date('d/m/Y h:i:s');
        echo'<br />Exportados para o xml: '.$this->count;
        parent::saveDoc("multifarmas");
    }
    
    private function isInvalidCategory($catIds){
        $invalidIds = explode(',',Mage::getStoreConfig('catalog/multifarmas/categories_excluded'));
        if(!$invalidIds || !$catIds) return false;
        
        foreach($catIds as $catId):
            if(in_array($catId, $invalidIds)) return true;
        endforeach;
        return false;
    }
    
    private function getProductCategoryIds($product_id){
        $categories = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        // 1.4 = 22, 1.7 = 111
        $sql = "SELECT DISTINCT CCP.category_id, CCEV.value as category_name FROM catalog_category_product CCP LEFT JOIN catalog_category_entity_varchar CCEV ON(CCP.category_id = CCEV.entity_id AND CCEV.attribute_id = 111) WHERE CCP.product_id = ".$product_id;
        $catRes = $readConnection->fetchAll($sql);
        foreach($catRes as $cat):
            $categories['category_id'][] = $cat['category_id'];
            $categories['category_name'][] = $cat['category_name'];
        endforeach;
        
        return $categories;
    }
    
    private function setManufacturerNames(){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        $sql = "SELECT EAO.option_id ,EAOV.value FROM eav_attribute_option EAO LEFT JOIN eav_attribute_option_value EAOV ON(EAO.option_id = EAOV.option_id) WHERE EAO.attribute_id = 55";
        $manufRes = $readConnection->fetchAll($sql);
        foreach($manufRes as $manuf):
            $this->manufaturers[$manuf['option_id']] = $manuf['value'];
        endforeach;
    }
    
    private function formatProductsMultifarmas($_products){
        $product_sufix = Mage::getStoreConfig('catalog/multifarmas/product_suffix_url');
        $this->setManufacturerNames();
        foreach ($_products as $_product):
            $cat_arr = array();
            $_prod = array();
            
            
            $categories = array();
            $categories = $this->getProductCategoryIds($_product['ID']);
//            $_product = Mage::getModel('catalog/product')->load($_prodId);
//            $categories = $_product->getCategoryIds();
//            
            if(count($categories) > 0) if($this->isInvalidCategory($categories['category_id'])) continue;
            $this->count++;
            
//            $_prodStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

//            foreach($categories as $catID):
//                $category = Mage::getModel('catalog/category')->load($catID);
//                if($category->getName()) $cat_arr[] = $category->getName();
//            endforeach;

            $_prod['nome'] = $_product['name'];
            $_prod['foto'] = Mage::getUrl("media/catalog/product").$_product['small_image'];
            $_prod['categoria'] = (count($categories) > 0)?implode(',',array_unique($categories['category_name'])):"";
            $_prod['pag_destino'] = Mage::getUrl($_product['url_path']).$product_sufix;
            $_prod['detalhes'] = $_product['short_description'];
            $_prod['descricao'] = $_product['description'];
            $_prod['laboratorio'] = ($_product['manufacturer'] && isset($this->manufaturers[$_product['manufacturer']]))?$this->manufaturers[$_product['manufacturer']]:"";
            $_prod['codbarras'] = "";
            $_prod['codms'] = $_product['nr_ministerio_da_saude'];
            $_prod['codinterno'] = $_product['sku'];
            $_prod['precocheio'] = number_format($_product['price'], 2, ',', '.');
            $preco_final = ($_product['special_price'])?$_product['special_price']:$_product['price'];
            $_prod['precofinal'] = number_format($preco_final, 2, ',', '.');
            $_prod['qtdestoque'] = (int)$_product['qty'];
            $_prod['disponibilidade'] = ($_product['is_in_stock'] == 0)?'Não':'Sim';
            $_prod['principalindicacao'] = $_product['tx_principal_indicacao'];

            parent::productsToDoc($_prod);
        endforeach;
    }
}