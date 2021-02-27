
<?php

require_once('app/Mage.php');
umask(0);
Mage::app();

$resource = Mage::getSingleton('core/resource');

$writeConnection = $resource->getConnection('core_write');

$query = "update sales_flat_order_grid g inner join sales_flat_order o on o.entity_id = g.entity_id and o.status <> g.status and o.updated_at > (NOW() - INTERVAL 48 HOUR) set g.status = o.status";

$writeConnection->query($query);
