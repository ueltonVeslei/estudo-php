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

class RMO_Integrator_Model_ImportExport_Import extends Varien_Object { 
    
    public function test() {
        $filename =  "test.csv";
        $stringCsv = file_get_contents($this->getWorkingDir() .  $filename);
        $data = Mage::helper('rmointegrator')->toAssociativeArray($stringCsv);
        $data = $this->unsetEmptyValues($data);
        $data = $this->addMediaAttributeId($data);
        $importer = Mage::getSingleton('fastsimpleimport/import')
                 ->setBehavior(Mage_ImportExport_Model_Import::BEHAVIOR_APPEND)
            ->setContinueAfterErrors(true);
        $validationResult = $importer->dryrunProductImport($data);
        $importer->processProductImport($data);
        $errors = array();
        if(!$validationResult) {
            $errors = $importer->getOperationResultMessages($validationResult);
        }
        return $errors;
    }
    
    public function unsetEmptyValues($data) {
        $toUnset = array('image', 'small_image', 'thumbnail');
        foreach ($data as $key => $entry) {
            foreach($toUnset as $att) {
                if(empty($entry[$att])) {
                   unset($entry[$att]); 
                }
            }
            $data[$key] = $entry;
        }
        return $data;
    }
    
    public function addMediaAttributeId($data) {
        $id = Mage::getSingleton('catalog/product')->getResource()->getAttribute('media_gallery') ->getAttributeId();
        foreach ($data as $key => $entry) {
            if(isset($entry["_media_image"])) {
               $entry["_media_attribute_id"] = $id; 
            }
            $data[$key] = $entry;
        }
        return $data;
    }
    
    
    public function importProducts($stringCsv) {
        Mage::helper('rmointegrator')->log("--- Import Products");
        Mage::helper('rmointegrator')->log("--- stringCsv");
        Mage::helper('rmointegrator')->log($stringCsv);
        
        $data = Mage::helper('rmointegrator')->toAssociativeArray($stringCsv);
        Mage::helper('rmointegrator')->log("--- toAssociativeArray ");
        Mage::helper('rmointegrator')->log($data);
        
        $data = $this->unsetEmptyValues($data);
        Mage::helper('rmointegrator')->log("--- unsetEmptyValues ");
        Mage::helper('rmointegrator')->log($data);
        
        $data = $this->addMediaAttributeId($data);
        
        Mage::helper('rmointegrator')->log("--- parsed csv");
        Mage::helper('rmointegrator')->log($data);
        
       
        
        $behavior =  null;
        if ( count($data) > 0 && count($data[0]) > 10  ) {
            $behavior = Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE;
        } else {
            $behavior = Mage_ImportExport_Model_Import::BEHAVIOR_APPEND;
        }
        
        $importer = Mage::getSingleton('fastsimpleimport/import')
                 ->setBehavior($behavior)
            ->setContinueAfterErrors(true);
        $importer->setPartialIndexing(true);
        $validationResult = $importer->dryrunProductImport($data);
        $importer->processProductImport($data);
        $errors = array();
        if(!$validationResult) {
            $errors = $importer->getOperationResultMessages($validationResult);
        }
        Mage::helper('rmointegrator')->log("--- END importProducts");
        return $errors;
    }
    
    public static function getWorkingDir() {
        return Mage::getBaseDir('var') . DS . 'rmo_integrator' . DS;
    }
}