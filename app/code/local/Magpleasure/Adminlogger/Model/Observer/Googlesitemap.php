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
class Magpleasure_Adminlogger_Model_Observer_Googlesitemap extends Magpleasure_Adminlogger_Model_Observer
{

    const REGISTRY_FLAG = 'adminlogger_sitemap_generation';

    public function GoogleSitemapLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('googlesitemap')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Googlesitemap::ACTION_SITEMAP_LOAD,
            Mage::app()->getRequest()->getParam('sitemap_id')
        );
    }

    public function GoogleSitemapSave($event)
    {
        if (Mage::registry(self::REGISTRY_FLAG)){
            return $this;
        }

        $sitemap = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('googlesitemap')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Googlesitemap::ACTION_SITEMAP_SAVE,
            $sitemap->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($sitemap->getData(), $sitemap->getOrigData())
            );
        }
    }

    public function GoogleSitemapDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('googlesitemap')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Googlesitemap::ACTION_SITEMAP_DELETE
        );
    }

    public function GoogleSitemapGenerate($event)
    {
        Mage::register(self::REGISTRY_FLAG, true, true);
        $this->createLogRecord(
            $this->getActionGroup('googlesitemap')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Googlesitemap::ACTION_SITEMAP_GENERATE,
            Mage::app()->getRequest()->getParam('sitemap_id')
        );
    }
}