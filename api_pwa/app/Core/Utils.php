<?php
class Utils {
    public static function plusDayNow($qtdDays){
        $date = strftime('%Y-%m-%d',strtotime("+$qtdDays day"));
        return $date;
    }

    public static function containsStringInArray($array, $string){
        if(count(array_intersect(explode(' ', strtolower($string)), $array)) > 0)
            return true;
        return false;
    }

    public static function formatValueMoeda($valor){
        $valor = Mage::helper('core')->currency(floatval($valor), true, false);
        return $valor;
    }
}

 