  <?php
    define('USERNAME','everton');  // Define username
    define('EMAIL','e.bresqui@onestic.com.br'); // Define a Email
    define('PASSWORD','12345678963gb');    // Define password  


    if(!defined('USERNAME') || !defined('EMAIL') || !defined('PASSWORD')){
        echo 'Define USERNAME, EMAIL and PASSWORD.';
        exit;
    }


    $mageFilename = 'app/Mage.php';
    if (!file_exists($mageFilename)) {
        echo $mageFilename." was not found";
        exit;
    }
    require_once $mageFilename;
    Mage::app();

    try {
        //create admin new user
        $user = Mage::getModel('admin/user')
            ->setData(array(
                'username'  => USERNAME,
                'firstname' => 'Everton',
                'lastname'  => 'Bresqui',
                'email'     => EMAIL,
                'password'  => PASSWORD,
                'is_active' => 1
            ))->save();

    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }

    try {
        //create new role
        $role = Mage::getModel("admin/roles")
                ->setName('Develoeprs')
                ->setRoleType('G')
                ->save();

        //give "all" privileges to role
        Mage::getModel("admin/rules")
                ->setRoleId($role->getId())
                ->setResources(array("all"))
                ->saveRel();

    } catch (Mage_Core_Exception $e) {
        echo $e->getMessage();
        exit;
    } catch (Exception $e) {
        echo 'Error while saving role.';
        exit;
    }

    try {
        //assign user to role
        $user->setRoleIds(array($role->getId()))
            ->setRoleUserId($user->getUserId())
            ->saveRelations();

    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }

    echo 'Admin User successfully created!';
