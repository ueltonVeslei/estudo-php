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

class Plumrocket_AdvancedPromotions_Block_Adminhtml_Catalog_Grid extends Mage_Adminhtml_Block_Promo_Catalog_Grid
{
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('field_rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule_id');

        $this->getMassactionBlock()->addItem('Export', array(
            'label'=> $this->helper('adminhtml')->__('Export'),
            'url'  => $this->getUrl('adminhtml/prpromo_catalog/exportRules')
        ));

        $this->getMassactionBlock()->addItem('status', array(
                'label'  => Mage::helper('tax')->__('Change status'),
                'url'    => $this->getUrl('*/prpromo_catalog/massChangeStatus', array('_current' => true)),
                'additional' => array(
                    'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'   => 'required-entry',
                    'label'   => Mage::helper('tax')->__('Status'),
                    'values'  => array(
                        1 => Mage::helper('tax')->__('Active'),
                        0 => Mage::helper('tax')->__('Inactive'),
                    ),
                )
            )
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('tax')->__('Delete'),
            'url'     => $this->getUrl('*/prpromo_catalog/massDelete', array('' => '')),
            'confirm' => Mage::helper('tax')->__('Are you sure?')
        ));

        return $this;
    }
}