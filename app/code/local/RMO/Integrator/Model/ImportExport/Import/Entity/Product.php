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

class RMO_Integrator_Model_ImportExport_Import_Entity_Product extends AvS_FastSimpleImport_Model_Import_Entity_Product {
    
    /**
     * Check one attribute. Can be overridden in child.
     *
     * @param string $attrCode Attribute code
     * @param array $attrParams Attribute params
     * @param array $rowData Row data
     * @param int $rowNum
     * @return boolean
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        $message = '';
        switch ($attrParams['type']) {
            case 'varchar':
                $val   = Mage::helper('core/string')->cleanString($rowData[$attrCode]);
                $valid = Mage::helper('core/string')->strlen($val) < self::DB_MAX_VARCHAR_LENGTH;
                $message = 'String is too long, only ' . self::DB_MAX_VARCHAR_LENGTH . ' characters allowed.';
                break;
            case 'decimal':
                $val   = trim($rowData[$attrCode]);
                $valid = (float)$val == $val;
                $message = 'Decimal value expected.';
                break;
            case 'select':
            case 'multiselect':
                $valid = isset($attrParams['options'][strtolower($rowData[$attrCode])]);
                $message = 'Possible options are: ' . implode(', ', array_keys($attrParams['options']));
                break;
            case 'int':
                $val   = (int)trim($rowData[$attrCode]);
                $valid = (int)$val == $val;
                $message = 'Integer value expected.';
                break;
            case 'datetime':
                $val   = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false
                    || preg_match('/^\d{2}.\d{2}.\d{2,4}(?:\s+\d{1,2}.\d{1,2}(?:.\d{1,2})?)?$/', $val);
                $message = 'Datetime value expected.';
                break;
            case 'text':
                $val   = Mage::helper('core/string')->cleanString($rowData[$attrCode]);
                $valid = Mage::helper('core/string')->strlen($val) < self::DB_MAX_TEXT_LENGTH;
                $message = 'String is too long, only ' . self::DB_MAX_TEXT_LENGTH . ' characters allowed.';
                break;
            default:
                $valid = true;
                break;
        }

        if (!$valid) {
            $this->addRowError(Mage::helper('importexport')->__("Invalid value for '%s'") . '. ' . $message, $rowNum, $attrCode);
        } elseif (!empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]])) {
                $this->addRowError(Mage::helper('importexport')->__("Duplicate Unique Attribute for '%s'"), $rowNum, $attrCode);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = true;
        }
        return (bool) $valid;
    }
    
    
     
}