<?php

/**
 * @category   RMO
 * @package    RMO_Integrator
 * @author     Renato Marcelino <renato@skyhub.com.br>
 * @company    SkyHub
 * @copyright (c) 2013, SkyHub
 * 
 * 
 * SkyHub: Especialista em integrações para e-commerce.
 * Integramos sua loja Magento com os principais Marketplaces
 * e ERPs do mercado nacional. 
 * Para mais informações acesse: www.skyhub.com.br
 */

class RMO_Integrator_Model_Resource_Catalog_Product_Integrator extends Mage_Core_Model_Resource_Db_Abstract {
    
    protected function _construct() {
        $this->_init('rmointegrator/catalog_product_integrator', 'integrator_product_id');
    }
}