<?php

class RMO_Integrator_Block_Ga  extends Mage_GoogleAnalytics_Block_Ga
{
    
    protected function _getPageTrackingCode($accountId)
    {
        $pageName   = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && preg_match('/^\/.*/i', $pageName)) {
            $optPageURL = ", '{$this->jsQuoteEscape($pageName)}'";
        }
        return "
_gaq.push(['_setAccount', '{$this->jsQuoteEscape($accountId)}']);
    setSkyhubCustomVariables(_gaq);
_gaq.push(['_trackPageview'{$optPageURL}]);
";
    }
}