<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
class Magpleasure_Adminlogger_Model_Actiongroup_Newslettersubscribers extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 16;
    const ACTION_NEWSLETTER_SUBSCRIBERS_UNSUBSCRIBED = 1;
    const ACTION_NEWSLETTER_SUBSCRIBERS_DELETE = 2;

    public function getLabel()
    {
        return $this->_helper()->__("Newsletter subscribers");
    }

    public function getDetailsRenderer($type = false)
    {
        return 'list';
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_NEWSLETTER_SUBSCRIBERS_UNSUBSCRIBED, 'label' => $this->_helper()->__("Unsubscribed")),
            array('value' => self::ACTION_NEWSLETTER_SUBSCRIBERS_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }

}
