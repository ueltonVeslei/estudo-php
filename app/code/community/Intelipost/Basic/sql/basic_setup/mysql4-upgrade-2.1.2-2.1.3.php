<?php

$entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

$modelGroup = Mage::getModel('eav/entity_attribute_group');
$modelGroup->setAttributeGroupName('Intelipost');
$modelGroup->setSortOrder(100);
$modelGroup->setAttributeSetId($entityTypeID);
$modelGroup->save();