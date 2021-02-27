<?php

class Edrone_Base_Model_Utilities_EdroneEventOrder extends Edrone_Base_Model_Utilities_EdroneEvent
{

    public function init()
    {
        $this->field['action_type'] = 'order';
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userCid($value)
    {
        parent::userCid($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userEmail($value)
    {
        parent::userEmail($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function platformVersion($value)
    {
        parent::platformVersion($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userFirstName($value)
    {
        parent::userFirstName($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userLastName($value)
    {
        parent::userLastName($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userSubscriberStatus($value)
    {
        parent::userSubscriberStatus($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userCountry($value)
    {
        parent::userCountry($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userCity($value)
    {
        parent::userCity($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function userOrder($value)
    {
        parent::userPhone($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOther
     */
    public function userTag($value)
    {
        parent::userTag($value);
        return $this;
    }

    /**
     *
     * @param type $value
     * @return  \EdroneEventOrder Description
     */
    public function productSkus($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_skus'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productIds($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_ids'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productTitles($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_titles'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productImages($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_images'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productUrls($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_urls'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productCounts($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_counts'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productCategoryIds($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_category_ids'] = $value;
        return $this;
    }

    /**
     *
     * @param type $value
     * @return \EdroneEventOrder
     */
    public function productCategoryNames($value)
    {
        if (is_array($value)) {
            $value = implode('|', $value);
        }
        $this->field['product_category_names'] = $value;
        return $this;
    }

    public function orderId($value)
    {
        $this->field['order_id'] = $value;
        return $this;
    }

    public function orderPaymentValue($value)
    {
        $this->field['order_payment_value'] = $value;
        return $this;
    }

    public function orderBasePaymentValue($value)
    {
        $this->field['base_payment_value'] = $value;
        return $this;
    }

    public function orderDetails($value)
    {
        $this->field['order_details'] = $value;
        return $this;
    }

    public function orderCurrency($value)
    {
        $this->field['order_currency'] = $value;
        return $this;
    }

    public function orderBaseCurrency($value)
    {
        $this->field['base_currency'] = $value;
        return $this;
    }

    /**
     * @return EdroneEventOrder
     */
    public static function create()
    {
        return new Edrone_Base_Model_Utilities_EdroneEventOrder();
    }

}
