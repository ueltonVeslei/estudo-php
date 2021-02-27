<?php
/*
 * HeadFeeder Model Class - STeV
 * Last Update: 02/03/2012
 */

/*
class EGBR_Headfeeder_Model_Cron {

    private $products_id;
    private $read;
    private $fd_attID;
    private $status_attID;
    private $visibility_attID;
    private $product_limit;
    private $currentStore;
    private $allCats;
    private $category_table;
    private $release_version;
    private $debug_string;
    
    public function __construct(){
        $this->setFeedRelease();
        $this->setMagentoDefaultTables();
        $this->identifyAttIDs();
    }
    
    private function setFeedRelease(){
        // check Magento point release version
        $point_release = explode('.', Mage::getVersion());
        $this->release_version = (int) $point_release[1];
    }
    
    private function setMagentoDefaultTables(){
        $resource = Mage::getSingleton('core/resource');
        $this->category_table = $resource->getTableName('catalog/category');
        
        // Aproveitando aqui para tambem setar o modulo do magento db write - STeV
        $this->read = $resource->getConnection('core_read');        
    }
    
    private function identifyAttIDs(){
        // A espectativa Ž de que pegue somente 2, feed_status e status nessa ordem - STeV
        $fd_attrID = $this->read->fetchCol("SELECT attribute_id FROM eav_attribute WHERE (attribute_code = 'feed_status' OR attribute_code = 'status' OR attribute_code = 'visibility') AND entity_type_id = 4 ORDER BY attribute_code");
        $this->fd_attID = $fd_attrID[0];
        $this->status_attID = $fd_attrID[1];
        $this->visibility_attID = $fd_attrID[2];
    }

    // Funcao para remover a palavra "Descricao" da descricao do produto se esta palavra existir no inicio da frase - STeV
    public function removeDescrFromBegin($descr) {
        $findStr = preg_match('/descri..o/i', utf8_decode($descr));
        if ($findStr > 0):
            $descr_arr = explode(' ', $descr);
            $descr_k = false;
            foreach ($descr_arr as $k => $descr_word):
                if ($k > 5)
                    break; // Para nao pegar a palavra descricao no meio do texto - STeV
                if (preg_match('/descri..o/i', utf8_decode($descr_word)) > 0):
                    $descr_k = $k;
                    break;
                endif;
            endforeach;
            if ($descr_k)
                $descr_arr[$descr_k] = utf8_encode(preg_replace('/descri..o/i', '', utf8_decode($descr_arr[$descr_k])));
            $descr = implode(' ', $descr_arr);
        endif;
        return $descr;
    }
    
    // Limita string em quantidade aproximada para menos, n‹o come palavras - STeV copyrights =)
    private function strLimit($str, $limit = 250, $etc = ' [...]'){
        if(strlen($str) <= $limit) return $str;
        $string_final = array();
        $str_count = 0;
        
        $string_arr = explode(' ', $str);
        foreach($string_arr as $string):
            $str_count += strlen($string);
            if($str_count > $limit) break;
            $string_final[] = $string;
        endforeach;
        return implode(' ', $string_final).$etc;
    }
    
    private function getStores(){
        $stores = Mage::app()->getStores();

        $active_stores = array();
        foreach ($stores as $store) {
            if ($store->getIsActive() == '1') array_push($active_stores, $store); // check if the store is active
        }
        return $active_stores;
    }

    private function getRootSubcategories($store) {
        $_rootcatID = $store->getRootCategoryId();
        $this->debug_string .= 'Root Category: '.$_rootcatID . '<br />';
        $this->allCats = array(); // Zerar subcategorias supondo que estamos em uma nova loja - STeV
        $this->getSubcategories($_rootcatID);
    }

    public function getCategoryProducts() {
        if (count($this->products_id) >= $this->product_limit)
            return false;
        
        // Attribute list, atualmente buscando autom‡tico no banco - STeV
        // Query to find - SELECT * FROM `catalog_product_entity_int` c LEFT JOIN eav_attribute a ON ( a.`attribute_id` = c.`attribute_id` ) WHERE c.`entity_id` =1
        // Status: 69, Visibility: 74 - 1.4
        // Status: 80, Visibility: 85 - 1.3
        
        $sql_cats = implode(',',$this->allCats);
        $sql_product_category = "SELECT DISTINCT p.product_id FROM catalog_category_product p ";
        // Pegar data da ultima atualizacao para ordenar - STeV
        $sql_product_category .= " LEFT JOIN catalog_product_entity cpe ON(cpe.entity_id = p.product_id) ";
        // Filtrar por feed_status = 0 - STeV
        $sql_product_category .= " RIGHT JOIN catalog_product_entity_int pe ON(p.product_id = pe.entity_id 
                                   AND pe.attribute_id = %s AND pe.value = 0) ";
        // Fitrar por status = 1 / Ativo - STeV
        $sql_product_category .= " RIGHT JOIN catalog_product_entity_int pe2 ON(p.product_id = pe2.entity_id
                                   AND pe2.attribute_id = %s AND pe2.value = 1)";
        // Filtrar por visibility = 2,3,4 - STeV
        $sql_product_category .= " RIGHT JOIN catalog_product_entity_int pe3 ON(p.product_id = pe3.entity_id 
                                   AND pe3.attribute_id = %s AND pe3.value IN(2,3,4)) ";
        // Where, order, limit - STeV
        $sql_product_category .= " WHERE category_id IN(%s)
                                   ORDER BY cpe.updated_at DESC
                                   LIMIT %s";
	$this->debug_string .= '<br /><br />'.sprintf($sql_product_category, $this->fd_attID, $this->status_attID, $this->visibility_attID, $sql_cats, $this->product_limit).'<br >';
        $products_list = $this->read->query(sprintf($sql_product_category, $this->fd_attID, $this->status_attID, $this->visibility_attID, $sql_cats, $this->product_limit));
        foreach ($products_list as $product):
            $this->products_id[] = $product['product_id'];
            if (count($this->products_id) >= $this->product_limit)
                return false;
        endforeach;
        return $this->products_id;
    }

    public function getSubcategories($cat_id, $subsub = false) {
        if (count($this->products_id) >= $this->product_limit)
            return false;

        $products_sql = "SELECT * FROM %s WHERE parent_id = %d";
        $subcats = $this->read->query(sprintf($products_sql, $this->category_table, $cat_id));

        foreach ($subcats as $subcat):
            //echo'<br />Sub: '.$subcat['entity_id'].' - '.$subcat['children_count'].'<br />';
            $this->allCats[] = (int)$subcat['entity_id'];
            if ($subcat['children_count'] > 0):
                $this->getSubcategories($subcat['entity_id'], true);
            endif;
        endforeach;
        if(!$subsub):
            $this->debug_string .= print_r($this->allCats, true);
            $this->getCategoryProducts(); // $subsub evita que rode mais de uma vez a query de produtos - STeV
        endif;
    }
    
    private function resetFeedList(){
        // we need to save the products that havent reached the quantity number
        $store_id = (int)$this->currentStore->getStoreId();
        $collection_array = array();
        foreach ($this->products_id as $collection) {
            array_push($collection_array, $collection);
        }

        // Pega todos os produtos aqui, so mantem - STeV
        // we dont have any products for the feed, so we need to start a new "cycle", setting the feed_status to true
        $products_collection_cycle = Mage::getModel('catalog/product')
                ->getCollection()->addStoreFilter($store_id)
                ->addAttributeToSelect('feed_status');

        foreach ($products_collection_cycle as $product_cycle) {
            // set the feed status to 0 so this product will appear in the feed
            // we need to check Magento version first
            if ($this->release_version == 3) {
                $product_cycle->setFeedStatus('0');
                $product_cycle->save();
            } else {
                // this is much faster
                Mage::getSingleton('catalog/product_action')->updateAttributes(array($product_cycle->getId()), array('feed_status' => 0), 0);
            }
        }

        $this->product_limit = $this->product_limit - count($this->products_id);
        $this->products_id = array(); // Reseta o vetor de id dos produtos - STeV

        $this->getRootSubcategories($this->currentStore);

        foreach ($this->products_id as $difference) {
            array_push($collection_array, $difference);
        }

        return $collection_array;
    }
    

    private function saveFeedToXML($feed) {
        // Zend_Feed will take care of everything
        $feed_obj = Zend_Feed::importArray($feed, 'rss');
        $rss_content = $feed_obj->saveXML();

        $file_path = substr($_SERVER['SCRIPT_FILENAME'], 0, -10) . '/media/' . strtolower($this->store_name);

        $feed_path = $file_path . '/feed.xml';
        if (file_exists($file_path)):
            if (file_exists($feed_path)):
                unlink($feed_path);
            endif;
        else:
            mkdir($file_path);
        endif;
        $fp = fopen($feed_path, 'w');
        if (fwrite($fp, $rss_content))
            $this->debug_string .= "<br /><br />Feed de produtos salvo com sucesso em " . $file_path . "<br />";
        fclose($fp);
    }

    public function generatefeed() {
        $count = 0;
        // double check 
        if (Mage::getStoreConfig('headfeeder/generatefeed/enabled')) {
            if ($this->release_version == 3) {
                // Magento 1.3.x needs it otherwise save method won't work
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            }

            // get stores
            $active_stores = $this->getStores();

            foreach ($active_stores as $active_store) {

                $this->currentStore = $active_store;
                
                $this->products_id = array(); // Zera os ids dos produtos para cada loja - STeV
                $store_id = (int) $this->currentStore->getStoreId();
                $group_id = (int) $this->currentStore->getGroupId();

                $this->product_limit = Mage::getStoreConfig('headfeeder/generatefeed/quantity');
                // get store info
                $store_group = Mage::getModel('core/store_group')->load($group_id);
                
                // we need to filter all the crap out
                $this->store_name = ereg_replace("[^A-Za-z0-9]", "", $store_group->getName());

                $this_store = Mage::app()->getStore();
                $store_url = $this_store->getUrl();

                // Create an array for our feed
                $feed = array();
                // Setup some info about our feed
                $feed['title'] = $this->store_name;
                $feed['link'] = $store_url;
                $feed['charset'] = 'utf-8';
                $feed['language'] = 'en-us';
                $feed['published'] = time();

                // Holds the actual items
                $feed['entries'] = array();

                $this->debug_string .= '<br /><br />Loja ' . $store_id . ' - ' . $this->store_name . '<br /><br />';
                $this->getRootSubcategories($this->currentStore);
                $products_collection = $this->products_id;

                // Se o numero de produtos encontrados for menor que o limite, resetar o contador de feed da loja - STeV
                if (count($this->products_id) < $this->product_limit) $products_collection = $this->resetFeedList();
                
                // Se nao tiver produtos segue para a proxima loja - STeV
                if(count($this->products_id) == 0) continue;
				
                $this->debug_string .= "<br />Product IDs:".print_r($this->products_id, true)."<br /><br />";
                	
                // Iterate thru each product
                foreach ($products_collection as $product_id) {

                    // make up a product guid
                    $product_guid = $product_id . time();

                    $product = Mage::getModel('catalog/product')->load($product_id);

                    // get product name
                    $product_name = $product->getName();
                    $this->debug_string .= '<br />Produto: ' . $product_name;
                    // get product url
                    $product_url = $product->getProductUrl();

                    // get product image url
                    $product_image_url = $product->getImageUrl();

                    // get product description
                    $product_description = $this->strLimit(strip_tags($this->removeDescrFromBegin($product->getDescription())), 250) . '... ' . "<p>Compre: <a href=\"$product_url\">$product_name</a></p>";

                    // Container for the entry before we add it on
                    $entry = array();
                    // The title that will be displayed for the entry
                    $entry['title'] = $product_name;
                    // The url of the entry
                    $entry['link'] = $product_url;
                    // Short description of the entry
                    $entry['description'] = $product_description;

                    // get the category collection from this product
                    $category_collection = $product->getCategoryCollection()
                            ->addAttributeToSelect(array('name'))
                            ->addFieldToFilter('is_active', '1');

                    if (!empty($category_collection)) {
                        // export it to array so we can iterate over it
                        $categories = $category_collection->exportToArray();

                        if (count($categories) > 1) {

                            $product_description .= "<p>Veja mais: ";

                            foreach ($categories as $category) {
                                // load this specific category
                                $category_data = Mage::getModel('catalog/category')->load($category['entity_id']);
                                // get the category name
                                $category_name = $category_data->getName();

                                // get the category url
                                $category_url = $category_data->getUrl();

                                $product_description .= "<a href=\"$category_url\">$category_name</a>, ";
                            }

                            $product_description = substr($product_description, 0, -2);
                            $product_description .= "</p>";
                        } else {
                            foreach ($categories as $category) {
                                // load this specific category
                                $category_data = Mage::getModel('catalog/category')->load($category['entity_id']);
                                // get the category name
                                $category_name = $category_data->getName();

                                // get the category url
                                $category_url = $category_data->getUrl();

                                $product_description .= "<p>Veja mais: <a href=\"$category_url\">$category_name</a></p>";
                            }
                        }
                    }

                    // Long description of the entry
                    $entry['content'] = $product_description;
                    // Some optional entries, usually the more info you can provide, the better
                    // Unix timestamp of the last modified date
                    $entry['lastUpdate'] = time();
                    // Feed author
                    $entry['author'] = 'Equipe';

                    // Add this entry to the entries
                    $feed['entries'][] = $entry;

                    // set the feed status to 0 so this product wont be on the feed in this cycle again
                    // we need to check Magento version first
                    if ($this->release_version == 3) {
                        $product->setFeedStatus('1');
                        $product->save();
                    } else {
                        // this is much faster
                        Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('feed_status' => 1), $store_id);
                    }
                }
                $this->saveFeedToXML($feed);
            } // Fim do foreach de lojas ativas - STeV
            echo $this->debug_string; // Imprime flags de degug - STeV
        } // Fim da verificacao se a extensao esta habilitada - STeV
    } // Fim da funcao generatefeed() - STeV
} // Fim da class - STeV
*/