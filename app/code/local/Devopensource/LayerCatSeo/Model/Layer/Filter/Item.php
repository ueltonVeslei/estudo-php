<?php
/**
 * Filter item model
 *
 * @category    Devopensource
 * @package     Devopensource_LayerCatSeo
 * @author      Jose Ruzafa <jose.ruzafa@devopensource.com>
 */
class Devopensource_LayerCatSeo_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        if($this->getFilter()->getRequestVar() == "cat"){
            $category_url = Mage::getModel('catalog/category')->load($this->getValue())->getUrl();
            $return = $category_url;
            $request = Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
            if(strpos($request,'?') !== false ){
                $query_string = substr($request,strpos($request,'?'));
            }
            else{
                $query_string = '';
            }
            if(!empty($query_string)){
                $return .= $query_string;
            }
            return $return;
        }
        else{
            $query = array(
                $this->getFilter()->getRequestVar()=>$this->getValue(),
                Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
            );

            return Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_query'=>$query));
        }
    }
}