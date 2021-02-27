<?php
/**
 * @desc Model de feed XML para buscadores de produtos - STeV
 * 
 * @author Estevam Neves
 *
 */
class EGBR_Feeds_Model_GerarFeed{
    private $root;
    private $doc;
    protected $product_entity_key = 10;
    
    protected function initDoc($mainEl = 'produtos'){
        $this->doc = new DOMDocument('1.0');
        $this->doc->formatOutput = true;
        $this->root = $this->doc->createElement($mainEl);
        $this->root = $this->doc->appendChild($this->root);
    }
    
    protected function productsToDoc($_prod, $mailEl = 'produto'){
        $produto = $this->doc->createElement($mailEl);

        foreach($_prod as $prod_k => $prod_v):
            $element = $this->doc->createElement($prod_k);
            $element->appendChild(
                $this->doc->createTextNode($prod_v)
            );
            $produto->appendChild($element);
        endforeach;

        $this->root->appendChild($produto);
    }
    
    protected function saveDoc($mod){
//        header('Content-Type: text/xml');
//        echo $doc->saveXML();
        
        $path_to_save_xml = Mage::getBaseDir()."/$mod/";
        
        if(is_dir($path_to_save_xml)):
            $this->doc->save($path_to_save_xml.$mod.'.xml');
        else:
            mkdir($path_to_save_xml, 0777);
            $this->doc->save($path_to_save_xml.$mod.'.xml');
        endif;
        
        echo'<br />Saved: '.$mod;
    }
}