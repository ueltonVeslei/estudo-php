<?php
class Magpleasure_Adminlogger_Block_Adminhtml_Widget_Grid_Renderer_Actiontype extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /**
     * Helper
     *
     * @return Magpleasure_Adminlogger_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actioGroupId = $row->getActionGroup();
        $actionGroup = $this->_helper()->getActionGroup($actioGroupId);
        $options = $actionGroup->getOptions();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $options[$item];
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
                return $options[$value];
            } elseif (in_array($value, $options)) {
                return $value;
            }
            return '';
        }
    }


}