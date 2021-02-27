<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Onestic
 * @package    Onestic_FreteProduto
 * @copyright  Copyright (c) 2017 Ecommerce Developer Blog (http://www.onestic.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract block for estimate module
 *
 */
abstract class Onestic_FreteProduto_Block_Estimate_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Estimate model
     *
     * @var Onestic_FreteProduto_Model_Estimate
     */
    protected $_estimate = null;


    /**
     * Config model
     *
     * @var Onestic_FreteProduto_Model_Config
     */
    protected $_config = null;


    /**
     * Module session model
     *
     * @var $_session Av5_FreteProduto_Model_Session
     */
    protected $_session = null;

    /**
     * List of carriers
     *
     * @var array
     */
    protected $_carriers = null;

    /**
     * Retrieve estimate data model
     *
     * @return Av5_FreteProduto_Model_Estimate
     */
    public function getEstimate()
    {
        if ($this->_estimate === null) {
            $this->_estimate = Mage::getSingleton('onestic_freteproduto/estimate');
        }

        return $this->_estimate;
    }

    /**
     * Retrieve configuration model for module
     *
     * @return Onestic_FreteProduto_Model_Config
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = Mage::getSingleton('onestic_freteproduto/config');
        }

        return $this->_config;
    }

    /**
     * Retrieve session model object
     *
     * @return Av5_FreteProduto_Model_Session
     */
    public function getSession()
    {
        if ($this->_session === null) {
            $this->_session = Mage::getSingleton('onestic_freteproduto/session');
        }

        return $this->_session;
    }

    /**
     * Check is enabled functionality
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getConfig()->isEnabled() && !$this->getProduct()->isVirtual();
    }
}

