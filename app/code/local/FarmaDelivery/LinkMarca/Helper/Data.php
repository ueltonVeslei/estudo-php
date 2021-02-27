<?php
class FarmaDelivery_LinkMarca_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getLinkMarca($product) {
        $manufacturer = $product->getAttributeText('manufacturer');

        $url = 'marca/';
        if ($manufacturer == "JOHNSON & JOHNSON") {
            $url .= "jnjbrasil";
        } else {
            $url .= str_replace(' ', '-',(strtolower($manufacturer)));
        }

        $marca = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('url_path')
            ->addAttributeToFilter('url_path',['like' => $url])
            ->getFirstItem();

        if ($marca->getId()) {
            return [
                'url'   => Mage::getBaseUrl() . $url,
                'name'  => $manufacturer
            ];
        }

        $marca = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('url_path')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('url_path',['like' => 'marca/%'])
            ->addFieldToFilter('entity_id',['in' => $product->getCategoryIds()])
            ->getFirstItem();

        if ($marca->getId()) {
            return [
                'url'   => Mage::getBaseUrl() . $marca->getUrlPath(),
                'name'  => $marca->getName()
            ];
        }

        return [
            'url'   => false,
            'name'  => $manufacturer
        ];
    }
}
