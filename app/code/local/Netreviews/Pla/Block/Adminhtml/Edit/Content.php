<?php

class Netreviews_Pla_Block_Adminhtml_Edit_Content extends Mage_Adminhtml_Block_Template {

    protected static $feed = array();

    public static function loadFeed() {
        // get current store id 
        $storeCode = Mage::app()->getRequest()->getParam('store');
        if (empty($storeCode)) {
            static::$feed = array();
            return '';
        }
        $store = Mage::getModel('core/store')->load($storeCode);
        $storeId = $store->getId();
        $config = Mage::getStoreConfig('avisverifies/extra/pla_configuration', $storeId);
        // json decode
        $json = json_decode($config, true);
        static::$feed = (is_array($json)) ? $json : array();
    }

    public static function getAttributeCollection() {
        $attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setItemObjectClass('catalog/resource_eav_attribute')
                ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());
        $array = array();

        foreach ($attribute_collection as $attribute) {
            if (!self::skipAttribute($attribute->getAttributeCode())) {
                $array[] = $attribute;
            }
        }

        return $array;
    }

    public static function getAttributeOptionsArray($field) {

        $options = array();
        if ($field == 'id' || $field == 'sku') {
            $options['Product Id'] = array(
                'code'  => 'entity_id',
                'label' => 'Product Id',
            );
        }
        foreach (self::getAttributeCollection() as $attribute) {
            if ($field == 'id') {
                if (!self::skipAttributeEntityId($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'sku') {
                if (!self::skipAttributeEntityId($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'product_name') {
                if (!self::skipAttributeDescription($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'url_path') {
                if (!self::skipAttributeLink($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'brand') {
                if (!self::skipAttributeBrand($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'category') {
                if (!self::skipAttributeBrand($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'image_link') {
                if (!self::skipAttributeImageLink($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'gtin_ean') {
                if (!self::skipAttributeGTIN($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'gtin_upc') {
                if (!self::skipAttributeGTIN($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'gtin_jan') {
                if (!self::skipAttributeGTIN($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'gtin_isbn') {
                if (!self::skipAttributeGTIN($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } elseif ($field == 'mpn') {
                if (!self::skipAttributeMPN($attribute->getAttributeCode())) {
                    $options[$attribute->getFrontendLabel()] = array(
                        'code'  => $attribute->getAttributeCode(),
                        'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                    );
                }
            } else {
                $options[$attribute->getFrontendLabel()] = array(
                    'code'  => $attribute->getAttributeCode(),
                    'label' => ($attribute->getFrontendLabel()) ? $attribute->getFrontendLabel() : $attribute->getAttributeCode(),
                );
            }
        }
        ksort($options);
        return $options;
    }

    public static function skipAttributeMPN($code) {
        $array = array('product_name', 'image', 'name', 'samples_title',
            'short_description', 'small_image', 'thumbnail', 'url_path');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttributeGTIN($code) {
        $array = array('product_name', 'image', 'name', 'samples_title',
            'short_description', 'small_image', 'thumbnail', 'url_path');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttributeImageLink($code) {
        $array = array('brand', 'country_of_manufacture', 'product_name', 'manufacturer', 'name', 'samples_title',
            'short_description', 'sku', 'url_path');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttributeBrand($code) {
        $array = array('url_path', 'image', 'small_image', 'thumbnail', 'sku');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttributeLink($code) {
        $array = array('brand', 'country_of_manufacture', 'product_name', 'image', 'manufacturer', 'name', 'samples_title',
            'short_description', 'small_image', 'thumbnail', 'sku');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttributeDescription($code) {
        $array = array('country_of_manufacture', 'image', 'small_image', 'thumbnail', 'url_path', 'sku');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttributeEntityId($code) {
        $array = array('brand', 'country_of_manufacture', 'product_name', 'image', 'manufacturer', 'name', 'samples_title',
            'short_description', 'small_image', 'thumbnail', 'url_path');
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function skipAttribute($code) {
        $array = array('color', 'cost', 'created_at', 'custom_design', 'custom_design_from', 'custom_design_to',
            'custom_layout_update', 'gift_message_available', 'group_price', 'has_options', 'is_recurring', 'links_exist',
            'links_purchased_separately', 'links_title', 'minimal_price', 'news_from_date', 'news_to_date', 'options_container',
            'page_layout', 'price', 'msrp', 'msrp_display_actual_price_type', 'msrp_enabled', 'price_type', 'price_view', 'recurring_profile',
            'shirt_size', 'special_from_date', 'special_price', 'special_to_date', 'status', 'tax_class_id', 'tier_price', 'updated_at', 'weight',
            'weight_type', 'meta_description', 'meta_keyword', 'meta_title', 'category_ids', 'gallery', 'image_label', 'media_gallery', 'old_id',
            'required_options', 'shipment_type', 'sku_type', 'small_image_label', 'thumbnail_label', 'visibility', 'url_key'
        );
        if (in_array($code, $array)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getAttributeSelect($field) {
        $options = array();
        $options[] = "<option value=''>Not Set</option>";
        foreach (self::getAttributeOptionsArray($field) as $attribute) {
            $selected = self::getIsSelected($field, $attribute['code']);
            $options[] = "<option value=\"{$attribute['code']}\" {$selected}>{$attribute['label']}</option>";
        }
        return implode('', $options);
    }

    public static function getIsSelected($field, $code) {
        // if feed is empty then use standard mapping
        if (static::getFeedArrayData($field) == $code) {
            return ' selected="selected" ';
        }// else use the saved mapping
        else {
            return '';
        }
    }

    public static function getFeedArrayData($field) {
        foreach (static::$feed as $array) {
            if (isset($array['name']) && $array['name'] == $field) {
                return $array['static_value'];
            }
        }
        return false;
    }

}
