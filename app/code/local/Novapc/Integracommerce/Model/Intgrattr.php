<?php
/**
 * PHP version 5
 * Novapc Integracommerce
 *
 * @category  Magento
 * @package   Novapc_Integracommerce
 * @author    Novapc <novapc@novapc.com.br>
 * @copyright 2017 Integracommerce
 * @license   https://opensource.org/licenses/osl-3.0.php PHP License 3.0
 * @version   GIT: 1.0
 * @link      https://github.com/integracommerce/modulo-magento
 */

class Novapc_Integracommerce_Model_Intgrattr extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        $integraAttrs = Mage::getModel('integracommerce/attributes')->load(1, 'entity_id');
        $integraAttrs->setData($this->getField(), $this->getValue());
        $integraAttrs->save();
    }
} 