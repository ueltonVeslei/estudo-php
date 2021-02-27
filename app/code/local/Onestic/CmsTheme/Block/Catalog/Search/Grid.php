<?php
class Onestic_CmsTheme_Block_Catalog_Search_Grid extends Mage_Adminhtml_Block_Catalog_Search_Grid
{
    protected function _prepareMassaction()
    {
      //parent::_prepareMassaction();

      // Append new mass action option
      /*$this->getMassactionBlock()->addItem(
          'newmodule',
          array('label' => $this->__('New Mass Action Title'),
                'url'   => $this->getUrl('newmodule/controller/action') //this should be the url where there will be mass operation
          )
      );*/
    }
}
