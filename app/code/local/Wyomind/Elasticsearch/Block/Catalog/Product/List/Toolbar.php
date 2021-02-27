<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

if (Mage::helper("core")->isModuleEnabled("Mirasvit_Seo") && class_exists("Mirasvit_Seo_Block_Catalog_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract extends Mirasvit_Seo_Block_Catalog_Product_List_Toolbar
    {
    }
} elseif (Mage::helper("core")->isModuleEnabled("Milkycode_ProductExtended") && class_exists("Milkycode_ProductExtended_Block_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract extends Milkycode_ProductExtended_Block_Product_List_Toolbar
    {
    }
} elseif (Mage::helper("core")->isModuleEnabled("Wyomind_Layer") && class_exists("Wyomind_Layer_Block_Catalog_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract extends Wyomind_Layer_Block_Catalog_Product_List_Toolbar
    {
    }
} elseif (Mage::helper("core")->isModuleEnabled("Amasty_Shopby") && class_exists("Amasty_Shopby_Block_Catalog_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract extends Amasty_Shopby_Block_Catalog_Product_List_Toolbar
    {
    }
} elseif (Mage::helper("core")->isModuleEnabled("MageWorx_SeoFriendlyLN") && class_exists("MageWorx_SeoFriendlyLN_Block_Catalog_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract extends MageWorx_SeoFriendlyLN_Block_Catalog_Product_List_Toolbar
    {
    }
} else {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract extends Mage_Catalog_Block_Product_List_Toolbar
    {
    }
}

class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar extends Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Abstract
{
    public function getOrderUrl($order, $direction)
    {
        return str_replace(array("\\", "<", ">"), "", urldecode(parent::getOrderUrl($order, $direction)));
    }
}