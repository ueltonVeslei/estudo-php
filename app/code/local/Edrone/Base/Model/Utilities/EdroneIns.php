<?php
if (!defined('EDRONE_SDK_VERSION')) {
    define('EDRONE_SDK_VERSION', '1.1.37');
}

class Edrone_Base_Model_Utilities_EdroneIns
{

    /** @var string AppId */
    private $appid = '';

    /** @var string $secret Secret */
    private $secret = '';

    /** @var string Trace url. def https://api.edrone.me/trace */
    private $trace_url = '';

    /** @var array  Prepared request array */
    private $preparedpack = array();

    /** @var Closure Closure method called on error */
    private $errorHandle = null;

    /** @var Closure Closure method called on success */
    private $readyHandle = null;

    /** @var Array Last request information */
    private $lastRequest = null;

    /**
     * Construct new Edrone Request instace
     * @param string $appid	AppId of tracker
     * @param string $secret Secret of tracer
     * @param string $trace_url def https://api.edrone.me/trace
     * @since 1.0.0
     */
    function __construct($appid, $secret, $trace_url = 'https://api.edrone.me/trace.php')
    {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->trace_url = $trace_url;
    }

    /**
     * 
     * Prepare event to send .5BLACK
     * 
     * @param EdroneEvent $event Use object of EdroneEventAddToCart,EdroneEventOrder,EdroneEventOrder,EdroneEventOther
     * @return EdroneIns.5BLACK
     * @since 1.0.0
     */
    public function prepare($event)
    {
        if (!($event instanceof Edrone_Base_Model_Utilities_EdroneEvent )) {
            throw new Exception('Event must by child EdroneEvent class ');
        }
        $event->pre_init();
        $event->init();
        $this->preparedpack = array_merge($event->get(), array(
            "app_id" => $this->appid,
            "version" => EDRONE_SDK_VERSION,
            "platform" => 'magento',
            "sender_type" => 'server',
        ));
        return $this;
    }

    /**
     * 
     * Force send prepared data
     * @since 1.0.0
     */
    public function send()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->trace_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->preparedpack));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $report = curl_getinfo($ch);
        curl_close($ch);
        $this->lastRequest = array("code" => $httpcode, "response" => $data, "info" => $report, 'fields' => $this->preparedpack);
        if (($httpcode !== 200) && ($this->errorHandle !== null)) {
            call_user_func_array($this->errorHandle, array($this));
        } elseif (($httpcode === 200) && ($this->readyHandle !== null)) {
            call_user_func_array($this->readyHandle, array($this));
        }
    }

    /**
     * Return last request as array (debug)
     * @return array
     * @since 1.0.0
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Set Callbacks for error action and ready action
     * @param \Closure $errorHandle
     * @param \Closure $readyHandle
     * @since 1.0.0
     */
    public function setCallbacks($errorHandle = null, $readyHandle = null)
    {
        $this->errorHandle = $errorHandle;
        $this->readyHandle = $readyHandle;
    }

}