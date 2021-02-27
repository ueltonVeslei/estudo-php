<?php

class Edrone_Base_Model_Utilities_EdroneEventSubscribe extends Edrone_Base_Model_Utilities_EdroneEvent
{

    public function init()
    {
        $this->field['action_type'] = 'subscribe';
        $this->field['customer_tags'] = 'From PopUp';
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
     * @return EdroneEventOther
     */
    public static function create()
    {
        return new Edrone_Base_Model_Utilities_EdroneEventSubscribe();
    }

}
