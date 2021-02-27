<?php
/**
 * @desc Model gerar feed XML para Cliquefarma - STeV
 * 
 * @author Estevam Neves
 *
 */
final class EGBR_Feeds_Model_Cliquefarma extends EGBR_Feeds_Model_GerarFeed{
    /**
    * This action will generate cliquefarma.xml to save to <website>/var/cliquefarma/
    * @return void
    */
    public function toCliqueFarma(){
        if(!Mage::getStoreConfig('catalog/cliquefarma/send_to_cliquefarma')) die("A atualziação de XML da cliquefarma encontra-se desabilitada.");
        parent::initDoc();

        $_products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter(array(array('attribute' => 'enviar_para_cliquefarma','eq' => '1')))->setPage(0,1)->getAllIds();
//        ->addFieldToFilter('visibility',array('eq'=>4))
        
        echo'<br />Cliquefarma product count: '.count($_products);
        if(count($_products)) $this->formatProductsCliquefarma($_products);
        parent::saveDoc("cliquefarma");
    }
    
    private function formatProductsCliquefarma($_products){
        $product_sufix = Mage::getStoreConfig('catalog/cliquefarma/product_suffix_url');
        foreach ($_products as $_prodId):
            $_product = Mage::getModel('catalog/product')->load($_prodId);
            $_prodStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

            $_prod['oferta_id'] = $_product->getSku();
            $_prod['oferta_descricao'] = $_product->getName();
            $_prod['empresa_id'] = 54;
            $preco_final = ($_product->getSpecialPrice())?$_product->getSpecialPrice():$_product->getPrice();
            $_prod['oferta_valor'] = number_format($preco_final, 2, ',', '.');
            $_prod['oferta_data'] = date('d/m/Y h:i');
            $_prod['link_produto'] = $_product->getProductUrl().$product_sufix;
            $_prod['oferta_imgproduto'] = $_product->getImageUrl();

            parent::productsToDoc($_prod);
        endforeach;
    }
}