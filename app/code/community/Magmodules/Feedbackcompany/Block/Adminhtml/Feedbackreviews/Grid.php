<?php
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_Feedbackcompany
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magmodules_Feedbackcompany_Block_Adminhtml_Feedbackreviews_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Magmodules_Feedbackcompany_Block_Adminhtml_Feedbackreviews_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('reviewsGrid');
        $this->setDefaultSort('date_created');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @param $row
     *
     * @return bool
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('feedbackcompany/reviews')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'review_id', array(
                'header' => Mage::helper('feedbackcompany')->__('ID'),
                'index' => 'feedback_id',
                'width' => '60px',
            )
        );

        $this->addColumn(
            'company', array(
                'header' => Mage::helper('feedbackcompany')->__('Shop'),
                'index' => 'company',
            )
        );


        $this->addColumn(
            'customer_name', array(
                'header' => Mage::helper('feedbackcompany')->__('Name'),
                'align' => 'left',
                'index' => 'customer_name',
            )
        );

        $this->addColumn(
            'customer_email', array(
                'header' => Mage::helper('feedbackcompany')->__('Email'),
                'align' => 'left',
                'index' => 'customer_email',
            )
        );

        $this->addColumn(
            'review_text', array(
                'header' => Mage::helper('feedbackcompany')->__('Review'),
                'align' => 'left',
                'index' => 'review_text',
                'renderer' => 'feedbackcompany/adminhtml_widget_grid_experience',
            )
        );

        $this->addColumn(
            'score', array(
                'header' => Mage::helper('feedbackcompany')->__('Score'),
                'align' => 'left',
                'index' => 'score',
                'renderer' => 'feedbackcompany/adminhtml_widget_grid_stars',
                'width' => '110',
                'filter' => false,
                'sortable' => true,
            )
        );

        $this->addColumn(
            'date_created', array(
                'header' => Mage::helper('feedbackcompany')->__('Date'),
                'align' => 'left',
                'type' => 'date',
                'index' => 'date_created',
                'width' => '140',
            )
        );

        $this->addColumn(
            'sidebar', array(
                'header' => Mage::helper('feedbackcompany')->__('Sidebar'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'sidebar',
                'type' => 'options',
                'options' => array(
                    0 => Mage::helper('feedbackcompany')->__('No'),
                    1 => Mage::helper('feedbackcompany')->__('Yes'),
                ),
            )
        );

        $this->addColumn(
            'status', array(
                'header' => Mage::helper('feedbackcompany')->__('Active'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'status',
                'type' => 'options',
                'options' => array(
                    0 => Mage::helper('feedbackcompany')->__('No'),
                    1 => Mage::helper('feedbackcompany')->__('Yes'),
                ),
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('review_id');
        $this->getMassactionBlock()->setFormFieldName('reviewids');

        $this->getMassactionBlock()->addItem(
            'hide', array(
                'label' => Mage::helper('feedbackcompany')->__('Set to invisible'),
                'url' => $this->getUrl('*/*/massDisable'),
            )
        );
        $this->getMassactionBlock()->addItem(
            'visible', array(
                'label' => Mage::helper('feedbackcompany')->__('Set to visible'),
                'url' => $this->getUrl('*/*/massEnable'),
            )
        );
        $this->getMassactionBlock()->addItem(
            'addsidebar', array(
                'label' => Mage::helper('feedbackcompany')->__('Add to Sidebar'),
                'url' => $this->getUrl('*/*/massEnableSidebar'),
            )
        );
        $this->getMassactionBlock()->addItem(
            'removesidebar', array(
                'label' => Mage::helper('feedbackcompany')->__('Remove from Sidebar'),
                'url' => $this->getUrl('*/*/massDisableSidebar'),
            )
        );

        return $this;
    }

}