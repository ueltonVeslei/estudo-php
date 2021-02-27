<?php
/**
 * Smart E-commerce do Brasil Tecnologia LTDA
 *
 * INFORMAÇÕES SOBRE LICENÇA
 *
 * Open Software License (OSL 3.0).
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Não edite este arquivo caso você pretenda atualizar este módulo futuramente
 * para novas versões.
 *
 * @category    Esmart
 * @package     Esmart_PayPalBrasil
 * @copyright   Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author     	Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

class Esmart_PayPalBrasil_Helper_Data extends Mage_Core_Helper_Data
{

	/**
	 * @var string
	 */
	protected $_ppbUrl = 'https://www.paypal-brasil.com.br';


	/**
	 * Returns PayPal Brasil URL
	 *
	 * @return string
	 */
	public function getPPBUrl()
	{
		return $this->_ppbUrl;
	}


	/**
	 * Returns PayPal's logo center URL
	 *
	 * @return string
	 */
	public function getLogoCenterUrl()
	{
		return implode('/', array($this->getPPBUrl(), 'logocenter', 'util', 'img'));
	}


	/**
	 * Returns the image URL in PayPal Logo Center
	 *
	 * @param string $imageName
	 * @param string $extension
	 *
	 * @return string
	 */
	public function getLogoCenterImageUrl($imageName = null, $extension = null)
	{
		if(!is_null($imageName)) {
			$_imageFullName = is_null($extension) ? $imageName : implode('.', array($imageName, $extension));

			return implode('/', array($this->getLogoCenterUrl(), $_imageFullName));
		}

		return null;
	}


    /**
     * Get General Config
     *
     * @param string $group
     * @param string $field
     *
     * @return string|null
     */
    public function getConfig($group = null, $field = null)
    {
        if(!is_null($group) && !is_null($field)) {
            return Mage::getStoreConfig("payment/{$group}/{$field}");
        }

        return null;
    }


    /**
     * Get PayPal Express Config
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getExpressConfig($field = null)
    {
        if(!is_null($field)) {
            return $this->getConfig('paypal_express', $field);
        }

        return null;
    }


    /**
     * Get PayPal Standard Config
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getStandardConfig($field = null)
    {
        if(!is_null($field)) {
            return $this->getConfig('paypal_standard', $field);
        }

        return null;
    }

}
