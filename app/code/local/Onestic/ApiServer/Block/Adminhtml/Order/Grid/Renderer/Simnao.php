<?php
class Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $value =  $row->getData($this->getColumn()->getIndex());
        if ($value == 'SYNCED') {
        	$value = 'SIM';
        } elseif($value == 'NOT_SYNCED') {
        	$value = 'NÃO';
        }
        
        if ($value == 'SIM') {
        	$color = "#41ab08";
        } else {
        	$color = "#a90b04";
        }
        
        return '<div style="color:#FFF;font-weight:bold;background:'.$color.';border-radius:8px;width:100%">'.$value.'</div>';
    }
}