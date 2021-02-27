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
 * Module configuration model
 *
 */
class Onestic_FreteProduto_Model_Config
{
    /**
     * A configuration path for the module active state setting
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'onestic_freteproduto/settings/enabled';


    /**
     * A configuration path for the country field usage
     *
     * @var string
     */
    const XML_PATH_USE_COUNTRY = 'onestic_freteproduto/settings/use_country';

    /**
     * A configuration path for the region field usage
     *
     * @var string
     */
    const XML_PATH_USE_REGION = 'onestic_freteproduto/settings/use_region';

    /**
     * A configuration path for the city field usage
     *
     * @var string
     */
    const XML_PATH_USE_CITY = 'onestic_freteproduto/settings/use_city';

    /**
     * A configuration path for the postcode field usage
     *
     * @var string
     */
    const XML_PATH_USE_POSTCODE = 'onestic_freteproduto/settings/use_postcode';

    /**
     * A configuration path for the coupon code field usage
     *
     * @var string
     */
    const XML_PATH_USE_COUPON_CODE = 'onestic_freteproduto/settings/use_coupon_code';

    /**
     * A configuration path for the include shopping cart checkbox visibility
     *
     * @var string
     */
    const XML_PATH_USE_CART = 'onestic_freteproduto/settings/use_cart';

    /**
     * A configuration path for the using of shopping cart items by default in calculation
     *
     * @var string
     */
    const XML_PATH_USE_CART_DEFAULT = 'onestic_freteproduto/settings/use_cart_default';


    /**
     * A configuration path for the default store country usage
     *
     * @var string
     */
    const XML_PATH_DEFAULT_COUNTRY = 'shipping/origin/country_id';


    /**
     * A configuration path for the list of layout handles for displaying of estimate form
     *
     * @var string
     */
    const XML_PATH_CONTROLLER_ACTIONS = 'onestic/onestic_freteproduto/controller_actions';

    /**
     * A configuration path for the display position on the page
     *
     * @var unknown_type
     */
    const XML_PATH_DISPLAY_POSITION = 'onestic_freteproduto/settings/display_position';

    /**
     * Display positions for shipping estimation form
     * @var string
     */
    const DISPLAY_POSITION_RIGHT = 'right';
    const DISPLAY_POSITION_LEFT = 'left';
    const DISPLAY_POSITION_CUSTOM = 'custom';

    /**
     * Layout handles names for applying of positions
     *
     * @var string
     */
    const LAYOUT_HANDLE_LEFT = 'onestic_freteproduto_left';
    const LAYOUT_HANDLE_RIGHT = 'onestic_freteproduto_right';

    /**
     * Retrive a configuration flag for the country field usage in the estimate
     *
     * @return boolean
     */
    public function useCountry()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_COUNTRY);
    }

    /**
     * Retrive a configuration flag for the region field usage in the estimate
     *
     * @return boolean
     */
    public function useRegion()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_REGION);
    }

    /**
     * Retrive a configuration flag for the city field usage in the estimate
     *
     * @return boolean
     */
    public function useCity()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CITY);
    }

    /**
     * Retrive a configuration flag for the postal code field usage in the estimate
     *
     * @return boolean
     */
    public function usePostcode()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_POSTCODE);
    }

    /**
     * Retrive a configuration flag for the coupon code field usage in the estimate
     *
     * @return boolean
     */
    public function useCouponCode()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_COUPON_CODE);
    }

    /**
     * Retrive a configuration flag
     * for visibility of the include cart items field
     *
     * @return boolean
     */
    public function useCart()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CART);
    }

    /**
     * Retrive a configuration flag
     * for using of the cart items in calculation
     *
     * @return boolean
     */
    public function useCartDefault()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CART_DEFAULT);
    }


    /**
     * Retrive default country
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_COUNTRY);
    }

    /**
     * Check the module active state configuration
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Retieve display type configuration value
     *
     * @return string
     */
    public function getDisplayPosition()
    {
        return Mage::getStoreConfig(self::XML_PATH_DISPLAY_POSITION);
    }

    /**
     * Retrieve layout handles list for applying of the form
     *
     * @return array
     */
    public function getControllerActions()
    {
        $actions = array();
        foreach (Mage::getConfig()->getNode(self::XML_PATH_CONTROLLER_ACTIONS)->children() as $action => $node) {
            $actions[] = $action;
        }

        return $actions;
    }
}

