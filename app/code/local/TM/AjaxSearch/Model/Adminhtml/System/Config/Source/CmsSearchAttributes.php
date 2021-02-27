<?php
class TM_AjaxSearch_Model_Adminhtml_System_Config_Source_CmsSearchAttributes
{
    public function toOptionArray()
    {
        $attributes = array('title', 'content');
        $result = array();
        foreach ($attributes as $attribute) {
            $result[] = array(
                'value' => $attribute,
                'label' => ucfirst($attribute)
            );
        }
        return $result;
    }
}
