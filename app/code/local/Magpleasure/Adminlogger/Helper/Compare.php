<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
class Magpleasure_Adminlogger_Helper_Compare extends Mage_Core_Helper_Abstract
{
    protected function _typeWrapper($value)
    {
        if (is_bool($value)){
            return $value ? '1' : '0';
        }
        return $value;
    }

    /**
     * Retrieves difference between arrays
     *
     * @param array $a
     * @param array $b
     * @return array
     */
    public function diff(array $a, array $b = null)
    {
        $result = array();

        if (is_null($b)) {
            $b = array();
        }

        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));

        foreach ($keys as $key) {
            if ($key == 'form_key'){
                continue;
            }

            if (isset($a[$key]) && isset($b[$key])) {
                if (!is_array($a[$key]) && !is_array($b[$key]) && !is_object($a[$key]) && !is_object($b[$key])){
                    if ($this->_typeWrapper($a[$key]) != $this->_typeWrapper($b[$key])) {
                        $result[] = array('attribute_code'=>$key, 'to'=>$this->_typeWrapper($a[$key]), 'from'=>$this->_typeWrapper($b[$key])  );
                    }
                }

            } elseif (isset($a[$key]) && !isset($b[$key])) {
                if ($this->_typeWrapper($a[$key]) && !is_array($a[$key]) && !is_object($a[$key])){
                    $result[] = array('attribute_code'=>$key, 'to'=>$this->_typeWrapper($a[$key]), 'from'=>null  );
                }
            }

        }
        ///TODO Image, Arrays and Objects processing

        return $result;
    }


    public function textDiff($old, $new)
    {
        $maxlen = 0;
        foreach($old as $oindex => $ovalue){
            $nkeys = array_keys($new, $ovalue);
            foreach($nkeys as $nindex){
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if($matrix[$oindex][$nindex] > $maxlen){
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
        return array_merge(
            $this->textDiff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->textDiff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    public function htmlDiff($old, $new)
    {
        $ret = "";
        $diff = $this->textDiff(explode(' ', $old), explode(' ', $new));
        foreach($diff as $k){
            if(is_array($k)){
                $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
                    (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
            } else {
                $ret .= $k . ' ';
            }
        }
        return $ret;
    }

    public function htmlToDiff($old, $new, $htmlEscape = false)
    {
        $ret = "";
        $diff = $this->textDiff(explode(' ', $old), explode(' ', $new));
        foreach($diff as $k){
            if(is_array($k)){
                $ret .= (!empty($k['i'])?"<ins>".implode(' ', $htmlEscape ? $this->escapeHtml($k['i']) : $k['i'])."</ins> ":'');
            } else {
                $ret .= ($htmlEscape ? $this->escapeHtml($k) : $k) . ' ';
            }
        }
        return $ret;
    }

    public function htmlFromDiff($old, $new, $htmlEscape = false)
    {
        $ret = "";
        $diff = $this->textDiff(explode(' ', $old), explode(' ', $new));
        foreach($diff as $k){
            if(is_array($k)){
                $ret .= (!empty($k['d'])?"<del>".implode(' ', $htmlEscape ? $this->escapeHtml($k['d']) : $k['d'])."</del> ":'');
            } else {
                $ret .= ($htmlEscape ? $this->escapeHtml($k) : $k) . ' ';
            }
        }
        return $ret;
    }

}