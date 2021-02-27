
<?php

require_once('app/Mage.php');
umask(0);
Mage::app();

$resource = Mage::getSingleton('core/resource');

$writeConnection = $resource->getConnection('core_write');

$query = "insert into onestic_overcoupom (increment_id, couponcode, created_at) SELECT o.increment_id, o.coupon_code as couponcode, o.created_at FROM sales_flat_order o where o.coupon_code in ('PROMOSENIOR', 'NUTREN', 'PROMOACTIVE', 'PROMOBEAUTY', 'BEAUTY15', 'PROMOFIBER') and not exists (select ov.increment_id from onestic_overcoupom ov where ov.increment_id = o.increment_id)";

$writeConnection->query($query);