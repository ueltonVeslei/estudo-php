<?php
class AcessRestriction {

    public static function isAccessMethodController($typeUser, $nameController, $method){
        //METHODS_CONTROLLERS_ACESS[$typeUser]['Controller_Login']['_get'];
        foreach(Config::METHODS_CONTROLLERS_ACESS[$typeUser] as $key => $value){
            if($value == 'FULL' || ($nameController == $key && Utils::containsStringInArray($value, $method))){
                return true;
            }
        }
        return false;
    }

    //Como no caso existe apenas dois tipos de usuarios na api   
    public static function getTypeUserAcess($idUser){
        if(is_numeric($idUser) && $idUser > 0)
            return 1;
        else 
            return 0;
    }
    

}

 