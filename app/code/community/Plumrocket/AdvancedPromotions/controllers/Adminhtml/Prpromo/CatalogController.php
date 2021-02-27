<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_AdvancedPromotions
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Promo' . DS . 'CatalogController.php');

class Plumrocket_AdvancedPromotions_Adminhtml_Prpromo_CatalogController extends Mage_Adminhtml_Promo_CatalogController
{
    public function exportRulesAction()
    {
        $file = 'catalog-price-rules-' . date("mdy-Hmi", Mage::getModel('core/date')->timestamp()) . '.json';
        $toFile = "";

        $rulesIds = $this->getRequest()->getParam('rule_id');
        if (is_string($rulesIds)) {
            $rulesIds = array($rulesIds);
        }

        if (!is_array($rulesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalogrule')->__('Please select rule(s).'));
        } else {
            try {
                foreach ($rulesIds as $ruleId) {
                    $model = Mage::getModel('catalogrule/rule');
                    $toFile .= json_encode($model->load($ruleId)->getData());
                    $toFile .= "\n";
                }
                $this->_prepareDownloadResponse($file, $toFile);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    public function duplicateRuleAction()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');

        if (!$ruleId) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalogrule')->__('Wrong parameter [\'rule_id\']'));
            $this->_redirect('*/promo_catalog/index');
            return false;
        }

        try {
            $source = Mage::getModel('catalogrule/rule');
            $data   = $source->load($ruleId)->getData();

            $data['rule_id'] = null;
            $data['name'] .= ' (Duplicate)';

            $target = Mage::getModel('catalogrule/rule')->setData($data);
            $id = $target->save()->getId();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/promo_catalog/edit', array('id' => $id));
    }

    public function massChangeStatusAction()
    {
        $rulesIds = $this->getRequest()->getParam('rule_id');
        $status = $this->getRequest()->getParam('status');

        try {
            foreach ($rulesIds as $rulesId) {
                $model = Mage::getModel('catalogrule/rule');
                $model->load($rulesId);
                $model->setIsActive($status)->save();
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pradvancedpromotions')->__($e->getMessage()));
            return $this->_redirect('*/promo_catalog/index');
        }

        $this->_redirect('*/promo_catalog/index');
    }

    public function massDeleteAction()
    {
        $rulesIds = $this->getRequest()->getParam('rule_id');
        $status = $this->getRequest()->getParam('status');

        try {
            foreach ($rulesIds as $rulesId) {
                $model = Mage::getModel('catalogrule/rule');
                $model->load($rulesId);
                $model->delete();
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pradvancedpromotions')->__($e->getMessage()));
            return $this->_redirect('*/promo_catalog/index');
        }

        $this->_redirect('*/promo_catalog/index');
    }

    public function importAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo')
            ->_title($this->__('Import'));

        $this->renderLayout();
    }

    public function importPostAction()
    {
        $data_arr = array();

        try {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            if ($ext != "json") {
                throw new Exception('Unsupported file format. JSON require.', 1);
            }

            $data = file($_FILES['file']['tmp_name']);

            if (count($data) <= 0) {
                throw new Exception('File is empty.', 1);
            }

            $count = 0;

            foreach ($data as $key => $json) {
                $data_arr[] = (array)json_decode($json);

                array_splice($data_arr[$key], 0, 1);

                $catalogrule = Mage::getModel('catalogrule/rule')->setData($data_arr[$key]);
                $catalogrule->save();
                $count++;
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($count . " catalog price rules was successfully imported.");
            return $this->_redirect('*/promo_catalog/index');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pradvancedpromotions')->__($e->getMessage()));
            return $this->_redirect('*/*/import');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/catalog');
    }
}
