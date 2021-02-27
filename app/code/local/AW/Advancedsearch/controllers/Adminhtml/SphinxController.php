<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedsearch
 * @version    1.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Advancedsearch_Adminhtml_SphinxController extends Mage_Adminhtml_Controller_Action
{
    public function checkstateAction()
    {
        $sphinx = Mage::getModel('awadvancedsearch/engine_sphinx');
        $state = $sphinx->checkSearchdState();
        $block = Mage::getSingleton('core/layout')->createBlock('adminhtml/template');
        $block->setData('state', $state)
              ->setTemplate('aw_advancedsearch/system/config/form/fieldset/state.phtml');
        $response = array(
            'state' => $state,
            'html' => $block->toHtml()
        );
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    public function stopAction()
    {
        $sphinx = Mage::getModel('awadvancedsearch/engine_sphinx');
        $response = array('r' => $sphinx->stopSearchd());
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    public function startAction()
    {
        $sphinx = Mage::getModel('awadvancedsearch/engine_sphinx');
        $response = array('r' => $sphinx->startSearchd());
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }
}
