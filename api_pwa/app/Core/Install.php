<?php
class Install{

    public static function init(){
        try{
            //Cria as tabelas
            self::createTables();
            return true;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    public static function createTables(){
        $sql = array();
        $sql[] = '
        CREATE TABLE IF NOT EXISTS User (
          id_user INT NOT NULL,
          active INT NOT NULL,
          PRIMARY KEY (`id_user`))
        ENGINE = InnoDB;';
        
        $sql[] = '
        CREATE TABLE IF NOT EXISTS Token (
          token VARCHAR(250) NOT NULL,
          data_expiracao DATE NOT NULL,
          ip VARCHAR(250) NOT NULL,
          dns_name VARCHAR(250) NOT NULL,
          user_agent VARCHAR(250) NOT NULL,
          id_user INT NULL,
          type_login_permission INT NOT NULL,
          PRIMARY KEY (token),
          INDEX fk_Token_User1_idx (id_user ASC),
          CONSTRAINT k_Token_User1
            FOREIGN KEY (id_user)
            REFERENCES User (id_user)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION)
        ENGINE = InnoDB;
        ';
        $sql[] = '
        CREATE TABLE IF NOT EXISTS Registry (
          id_user INT NULL,
          token VARCHAR(250) NOT NULL,
          date_time_now DATETIME NOT NULL,
          method_controller VARCHAR(250) NOT NULL,
          data_json TEXT NOT NULL,
          CONSTRAINT fk_Registry_User
            FOREIGN KEY (id_user)
            REFERENCES User (id_user),
          CONSTRAINT fk_Registry_Token1
            FOREIGN KEY (token)
            REFERENCES Token (token))
        ENGINE = InnoDB;';
        
        $sql[] = '
        CREATE TABLE IF NOT EXISTS Blocked (
          ip VARCHAR(250) NOT NULL,
          token VARCHAR(250) NOT NULL,
          PRIMARY KEY (ip),
          CONSTRAINT fk_Blocked_Token1
            FOREIGN KEY (token)
            REFERENCES Token (token))
        ENGINE = InnoDB;';

        $sql[] = '
        CREATE TABLE IF NOT EXISTS `RecoveryPassword` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `chave` VARCHAR(6) NOT NULL,
          `status` TINYINT NOT NULL,
          `id_user` INT NOT NULL,
          `email` VARCHAR(250) NOT NULL,
          PRIMARY KEY (`id`),
          INDEX `fk_RecoveryPassword_User1_idx` (`id_user` ASC),
          CONSTRAINT `fk_RecoveryPassword_User1`
            FOREIGN KEY (`id_user`)
            REFERENCES `User` (`id_user`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION)
        ENGINE = InnoDB;';

        // Create connection
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach($sql as $table){
            $connection->query($table);
        }

        $connection = null;
    }
}