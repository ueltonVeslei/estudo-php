<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package	Plumrocket_Base-v1.x.x
@copyright	Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
@license	http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

class Plumrocket_Base_Adminhtml_PlumbaseController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $data = $this->getRequest()->getParams();
        $return = array();

        if (isset($data['order_id']) && isset($data['account_email']) && isset($data['module'])) {
            $v = (string)Mage::getConfig()->getNode('modules/'.'Plumrocket_'.$data['module'])->version;

            $postData = array(
                'order' => $data['order_id'],
                'email' => $data['account_email'],
                'base_urls' => $this->getBaseUrl(),
                'name' => $data['module'],
                'name_version' => $v,
                'edition' => $this->getEdition(),
                'pixel' => 0,
                'v' => 1
            );

            $response = $this->call($postData);
            $response = (array)json_decode($response);

            if (!empty($response['hash'])) {
                Mage::getConfig()->saveConfig($data['module'].'/module/data',
                    $response['hash'], 'default', 0);
                $return['hash'] = true;
            } else {
                $return['hash'] = false;
            }

            if (isset($response['data'])) {
                $return['data'] = $response['data'];
            }

            if (!empty($response['errors'])) {
                if (is_array($response['errors']))  {
                    $error = implode("<br />", $response['errors']);
                } else {
                    $error = $response['errors'];
                }
                $return['error'] = $error;
            }
        }

        $this->getResponse()->setBody(json_encode($return));
    }

    protected function call($postData)
    {
        $url = implode('',
            array_map('c'.'hr', explode('.','104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.105.110.100.101.120.46.112.104.112.47.105.108.103.47.112.105.110.103.98.97.99.107.47.109.97.114.107.101.116.112.108.97.99.101.47'))
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    protected function _isAllowed()
    {
        return true;
    }

    private function getEdition()
    {
        $conf = Mage::getConfig();
        $ep = 'Enter'.'prise';
        $edt = ($conf->getModuleConfig( $ep.'_'.$ep)
            || $conf->getModuleConfig($ep.'_AdminGws')
            || $conf->getModuleConfig($ep.'_Checkout')
            || $conf->getModuleConfig($ep.'_Customer')) ? $ep : 'Com'.'munity';

        return $edt;
    }

    private function getBaseUrl()
    {
        $k = strrev('lru_'.'esab'.'/'.'eruces/bew'); $us = array(); $u = Mage::getStoreConfig($k, 0); $us[$u] = $u;
        foreach(Mage::app()->getStores() as $store) { if ($store->getIsActive()) { $u = Mage::getStoreConfig($k, $store->getId()); $us[$u] = $u; }}

        return  array_values($us);
    }
}