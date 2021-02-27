
<?php

$installer = $this;
$installer->startSetup();

$resource = Mage::getResourceModel('bannerslider/imagedetail_collection');
if(!method_exists($resource, 'getEntity')){

    $table = $this->getTable('bannerdetail');
    $installer->getConnection()
        ->addColumn($installer->getTable($table),'slide_position', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable'  => false,
            'length'    => 255,
            'after'     => null, // column name to insert new column after
            'comment'   => 'Posição em que vai ser exibido no frontend',
            'default'   => '1'
        ));
}

$installer->endSetup();
?>
