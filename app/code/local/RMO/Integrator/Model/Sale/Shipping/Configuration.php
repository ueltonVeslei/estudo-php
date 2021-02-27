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

/**
 * 
 * The purpose of this class is to serve as a message class.
 * 
 * !!!IMPORTANT!!!!
 * ########################################################################
 * This  class must be used as a SINGLETON class. This means that always
 * to get an objection of this class, the following magento method MUST be used:
 * 
 * $shippingConfiguration = Mage::getSingleton("rmointegrator/sale_shipping_configuration")
 * 
 * Before exporting an order from the Skyhub system to the Magento, first 
 * the exporter must fill in all the parameters present in this class with the shipping
 * information from the Skyhub system. It will allow this module to create a special
 * shipping method that can be used only to import skyhub orders.  
 * 
 * If these values are not set properly, Magento will rise an exception and 
 * the order will not be imported.
 * 
 * For more information @see RMO_Integrator_Model_Sale_Shipping
 * 
 * The parameters that must be set are listed bellow.
 * 
 * Shipping Configuration Model
 *
 * @method getIsActive()
 * @method setIsActive()
 * @method getShippingMethodCode()
 * @method setShippingMethodName()
 * @method getShippingPrice()
 * @method setShippingPrice()
 * 
 */
class RMO_Integrator_Model_Sale_Shipping_Configuration extends Mage_Core_Model_Abstract { 

}