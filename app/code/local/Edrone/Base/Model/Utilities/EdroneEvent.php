<?php

abstract class Edrone_Base_Model_Utilities_EdroneEvent
{

    protected $field = array();

    abstract public function init();

    public function pre_init()
    {
        //preInitObject
    }

    public function userCid($value)
    {
        $this->field['c_id'] = trim('phpsd_' . $value);
    }

    public function userEmail($value)
    {
        $this->field['email'] = trim($value);
    }

    public function userFirstName($value)
    {
        $this->field['first_name'] = trim($value);
    }

    public function userLastName($value)
    {
        $this->field['last_name'] = trim($value);
    }

    public function userSubscriberStatus($value)
    {
        $this->field['subscriber_status'] = trim($value);
    }

    public function userCountry($value)
    {
        $this->field['country'] = trim($value);
    }

    public function userCity($value)
    {
        $this->field['city'] = trim($value);
    }

    public function userPhone($value)
    {
        $this->field['phone'] = trim($value);
    }

    public function userTag($value)
    {
        $this->field['customer_tags'] = trim($value);
    }

    public function platformVersion($value)
    {
        $this->field['platform_version'] = trim($value);
    }

    public function get()
    {
        return $this->field;
    }
}