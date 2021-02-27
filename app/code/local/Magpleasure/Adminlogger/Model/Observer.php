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

class Magpleasure_Adminlogger_Model_Observer
{

    protected function _prepareDetailsFromArray(array $types)
    {
        $result = array();
        foreach ($types as $type){
            $result[] = array('attribute_code'=>$type, 'from'=> null, 'to'=> null);
        }
        return $result;
    }


    /**
     * @param $type
     * @return Magpleasure_Adminlogger_Model_Actiongroup_Abstract
     */
    public function getActionGroup($type)
    {
        $type = strtolower($type);
        return Mage::getModel("adminlogger/actiongroup_{$type}");
    }

    /**
     * Retrieves user
     *
     * @return bool|Mage_Admin_Model_User
     */
    public function getUser()
    {
        /** @var Mage_Admin_Model_Session $session  */
        $session = Mage::getSingleton('admin/session');
        if ($session->isLoggedIn()) {
            return $session->getUser();
        }
        return false;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Adminlogger_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    protected function _getStore()
    {
        $store = Mage::app()->getRequest()->getParam('store');
        if ($store){
            if (is_string($store)){
                $storeModel = Mage::getModel('core/store')->load($store, 'code');
                if ($storeModel->getId()){
                    return $storeModel->getId();
                }
                return $store;
            }
        }
        return 0;
    }

    protected function _getWebsite()
    {
        $website = Mage::app()->getRequest()->getParam('website');
        if ($website){
            if (is_string($website)){
                $websiteModel = Mage::getModel('core/website')->load($website, 'code');
                if ($websiteModel->getId()){
                    return $websiteModel->getId();
                }
                return $website;
            }
        }
        return 0;
    }


    /**
     * @param $actionGroup
     * @param $actionType
     * @return Magpleasure_Adminlogger_Model_Log
     */
    protected function createLogRecord($actionGroup, $actionType, $entityId = null)
    {
        /** @var Magpleasure_Adminlogger_Model_Log $log  */
        $log = Mage::getModel('adminlogger/log');
        $userId = $this->getUser() ? $this->getUser()->getId() : null;

        if (!$this->_helper()->getConfLogEnabled()){
            return $log;
        }

        if (!$this->_helper()->needLogForUser($userId)){
            return false;
        }

        if (!$this->_helper()->needLogForActionGroup($actionGroup)){
            return false;
        }

        /* @var $helper Mage_Core_Helper_Http */
        $helper = Mage::helper('core/http');

        $log
            ->setActionGroup($actionGroup)
            ->setActionType($actionType)
            ->setEntityId($entityId)
            ->setRemoteAddr($helper->getRemoteAddr(true))
            ->setStoreId($this->_getStore())
            ->setWebsiteId($this->_getWebsite())
            ->setActionTime($this->_helper()->getCurrentTime())
            ->setUserId($userId)
            ->save();

        return $log;
    }

    public function modelSaveBefore($event)
    {
        $object = $event->getObject();
        if ($object instanceof Mage_Cms_Model_Block) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Cmsstaticblocks $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_cmsstaticblocks');
            $observer->CmsBlocksSave($event);
        } elseif ($object instanceof Mage_Poll_Model_Poll) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Cmspolls $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_cmspolls');
            $observer->CmsPollsSave($event);
        } elseif ($object instanceof Mage_Tax_Model_Class) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Customertaxclasses $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_customertaxclasses');
            $observer->CustomerTaxClassesSave($event);
            /** @var Magpleasure_Adminlogger_Model_Observer_Producttaxclasses $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_producttaxclasses');
            $observer->ProductTaxClassesSave($event);
        } elseif ($object instanceof Mage_Core_Model_Design) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Systemdesign $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_systemdesign');
            $observer->SystemDesignSave($event);
        } elseif ($object instanceof Mage_Core_Model_Url_Rewrite) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Urlrewrites $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_urlrewrites');
            $observer->UrlRewriteSave($event);
        } elseif ($object instanceof Mage_Tax_Model_Calculation_Rule) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Taxrules $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_taxrules');
            $observer->TaxRulesSave($event);
        } elseif ($object instanceof Mage_Tax_Model_Calculation_Rate) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Taxrates $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_taxrates');
            $observer->TaxRatesSave($event);
        } elseif ($object instanceof Mage_Newsletter_Model_Template) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Newslettertemplates $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_newslettertemplates');
            $observer->NewsletterTemplatesSave($event);
        } elseif ($object instanceof Mage_Sitemap_Model_Sitemap) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Googlesitemap $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_googlesitemap');
            $observer->GoogleSitemapSave($event);
        } elseif ($object instanceof Mage_Rating_Model_Rating) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Catalogratings $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_catalogratings');
            $observer->CatalogRatingsSave($event);
        } elseif ($object instanceof Mage_Api_Model_User) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Webservicesapiusers $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_webservicesapiusers');
            $observer->ApiUsersSave($event);
        } elseif ($object instanceof Mage_Api_Model_Roles) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Webservicesapiroles $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_webservicesapiroles');
            $observer->ApiRolesSave($event);
        } elseif ($object instanceof Mage_Eav_Model_Entity_Attribute_Set) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Catalogattributesets $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_catalogattributesets');
            $observer->CatalogAttributeSetsSave($event);
        } elseif ($object instanceof Mage_Core_Model_Email_Template) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Transactionalemails $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_transactionalemails');
            $observer->TransactionalEmailsSave($event);
        } elseif ($object instanceof Mage_Index_Model_Process) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Indexmanagement $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_indexmanagement');
            $observer->IndexManagementSave($event);
        } elseif ($object instanceof Mage_Newsletter_Model_Queue) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Newsletterqueue $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_newsletterqueue');
            $observer->NewsletterQueueSave($event);
        } elseif ($object instanceof Magpleasure_Guestbook_Model_Message) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpguestbook $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpguestbook');
            $observer->MpGuestbookMessageSave($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Post) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogpost $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogpost');
            $observer->MpBlogPostSave($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Category) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogcategory $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogcategory');
            $observer->MpBlogCategorySave($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Comment) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogcomment $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogcomment');
            $observer->MpBlogCommentSave($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Tag) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogtag $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogtag');
            $observer->MpBlogTagSave($event);
        } elseif ($object instanceof AW_Helpdeskultimate_Model_Ticket) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Awhduticket $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_awhduticket');
            $observer->AwHduTicketSave($event);
        } elseif ($object instanceof Magpleasure_Activecontent_Model_Content) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpacslide $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpacslide');
            $observer->MpAcSlideSave($event);
        } elseif ($object instanceof Magpleasure_Activecontent_Model_Block) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpacslider $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpacslider');
            $observer->MpAcSliderSave($event);
        }
    }

    public function modelDeleteBefore($event)
    {
        $object = $event->getObject();
        if ($object instanceof Mage_Cms_Model_Block) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Cmsstaticblocks $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_cmsstaticblocks');
            $observer->CmsBlocksDelete($event);
        } elseif ($object instanceof Mage_Poll_Model_Poll) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Cmspolls $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_cmspolls');
            $observer->CmsPollsDelete($event);
        } elseif ($object instanceof Magpleasure_Guestbook_Model_Message) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpguestbook $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpguestbook');
            $observer->MpGuestbookMessageDelete($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Post) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogpost $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogpost');
            $observer->MpBlogPostDelete($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Category) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogcategory $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogcategory');
            $observer->MpBlogCategoryDelete($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Comment) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogcomment $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogcomment');
            $observer->MpBlogCommentDelete($event);
        } elseif ($object instanceof Magpleasure_Blog_Model_Tag) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpblogtag $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpblogtag');
            $observer->MpBlogTagDelete($event);
        } elseif ($object instanceof Magpleasure_Activecontent_Model_Content) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpacslide $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpacslide');
            $observer->MpAcSlideDelete($event);
        } elseif ($object instanceof Magpleasure_Activecontent_Model_Block) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Mpacslider $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_mpacslider');
            $observer->MpAcSliderDelete($event);
        }
    }

    public function modelDeleteAfter($event)
    {
        $object = $event->getObject();
        if ($object instanceof AW_Helpdeskultimate_Model_Ticket) {
            /** @var Magpleasure_Adminlogger_Model_Observer_Awhduticket $observer  */
            $observer = Mage::getSingleton('adminlogger/observer_awhduticket');
            $observer->AwHduTicketDelete($event);
        }
    }

}