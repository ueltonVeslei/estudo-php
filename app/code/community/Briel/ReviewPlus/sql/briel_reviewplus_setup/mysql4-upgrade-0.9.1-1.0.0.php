<?php
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('reviewplus_clientlog')};
CREATE TABLE IF NOT EXISTS {$this->getTable('reviewplus_clientlog')} (
`id` int(12) unsigned NOT NULL auto_increment,
`enable` tinyint(1) NOT NULL ,
`order_id` int(12) NOT NULL ,
`customer_name` varchar(255) NOT NULL ,
`customer_email` varchar(255) NOT NULL ,
`ordered_products` varchar(255) NOT NULL ,
`due_date` int(16) NOT NULL ,
`status` tinyint(1) NOT NULL ,
`time_sent` int(16) NOT NULL ,
PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('reviewplus_reviews')};
CREATE TABLE IF NOT EXISTS {$this->getTable('reviewplus_reviews')} (
`id` int(12) unsigned NOT NULL auto_increment,
`order_id` int(12) NOT NULL ,
`product_sku` varchar(255) NOT NULL ,
`customer_name` varchar(255) NOT NULL ,
`customer_email` varchar(255) NULL ,
`product_rating` tinyint(1) NOT NULL ,
`product_review_title` varchar(255) NOT NULL ,
`product_review` text NOT NULL ,
`review_status` varchar(100) NOT NULL ,
`store_id` int(3) NOT NULL ,
`posted_time` int(16) NOT NULL ,
PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('core_email_template')}
(template_code, template_text, template_type, template_subject)
values (
    'ReviewPlus 1.0.0 Transactional',
    '<div style=\"background: #F6F6F6; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0;\">
	<table style=\"width: 100%;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
	<tbody>
	<tr>
	<td style=\"padding: 20px 0 20px 0;\" align=\"center\" valign=\"top\">
	<table style=\"border: 1px solid #e0e0e0; width: 650px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\" bgcolor=\"FFFFFF\">
	<tbody>
	<tr>
	<td valign=\"top\"><a style=\"color: #E0E0E0;\" href=\"{{store url=\"\"}}\"><img src=\"{{skin url=\"images/logo_email.gif\" _area=\"frontend\"}}\" alt=\"{{var store_name}}\" border=\"0\" /></a></td>
	</tr>
	<tr>
	<td valign=\"top\">
	<h1 style=\"font-size: 21px; font-weight: normal; line-height: 22px; margin: 0;\">Dear <strong>{{var namevar}}</strong>,</h1>
	<p style=\"border: 1px solid #E0E0E0; font-size: 12px; line-height: 16px; margin-bottom: 10px; padding: 13px 18px; background: #F9F9F9;\">You have recently purchased the following product(s) from our store: <br /><strong>{{var purchased_products}}</strong></p>
	<p style=\"border: 1px solid #E0E0E0; font-size: 12px; line-height: 16px; margin: 0; padding: 13px 18px; background: #F9F9F9;\"><span>Please take a moment to rate the purchased product(s). Your feedback is important to us and helps improve our services and products.</span></p>
	<ul style=\"border: 1px solid #E0E0E0; list-style: none; font-size: 12px; line-height: 16px; margin-top: 0; margin-bottom: 20px; padding-top: 10px; padding-bottom: 20px; background: #F9F9F9;\">
	<li style=\"list-style: none; padding-bottom: 5px; padding-top: 5px; border-bottom: 1px solid #E0E0E0;\"><a href=\"{{var review_page_url}}&amp;rtng=5\"><img style=\"width: 125px; height: 25px; vertical-align: middle;\" src=\"{{config path=\"web/unsecure/base_url\"}}/skin/frontend/base/default/images/reviewplus_email_template/5-stars.jpg\" alt=\"Very satisfied\" />&nbsp;<span>( 5 / 5 )</span></a></li>
	<li style=\"list-style: none; padding-bottom: 5px; padding-top: 5px; border-bottom: 1px solid #E0E0E0;\"><a style=\"margin-top: 10px;\" href=\"{{var review_page_url}}&amp;rtng=4\"><img style=\"width: 125px; height: 25px; vertical-align: middle;\" src=\"{{config path=\"web/unsecure/base_url\"}}/skin/frontend/base/default/images/reviewplus_email_template/4-stars.jpg\" alt=\"Satisfied\" />&nbsp;<span>( 4 / 5 )</span></a></li>
	<li style=\"list-style: none; padding-bottom: 5px; padding-top: 5px; border-bottom: 1px solid #E0E0E0;\"><a href=\"{{var review_page_url}}&amp;rtng=3\"><img style=\"width: 125px; height: 25px; vertical-align: middle;\" src=\"{{config path=\"web/unsecure/base_url\"}}/skin/frontend/base/default/images/reviewplus_email_template/3-stars.jpg\" alt=\"Neutral\" />&nbsp;<span>( 3 / 5 )</span></a></li>
	<li style=\"list-style: none; padding-bottom: 5px; padding-top: 5px; border-bottom: 1px solid #E0E0E0;\"><a href=\"{{var review_page_url}}&amp;rtng=2\"><img style=\"width: 125px; height: 25px; vertical-align: middle;\" src=\"{{config path=\"web/unsecure/base_url\"}}/skin/frontend/base/default/images/reviewplus_email_template/2-stars.jpg\" alt=\"Not satisfied\" />&nbsp;<span>( 2 / 5 )</span></a></li>
	<li style=\"list-style: none; padding-bottom: 5px; padding-top: 5px; border-bottom: 1px solid #E0E0E0;\"><a href=\"{{var review_page_url}}&amp;rtng=1\"><img style=\"width: 125px; height: 25px; vertical-align: middle;\" src=\"{{config path=\"web/unsecure/base_url\"}}/skin/frontend/base/default/images/reviewplus_email_template/1-star.jpg\" alt=\"Very unsatisfied\" />&nbsp;<span>( 1 / 5 )</span></a></li>
	</ul>
	</td>
	</tr>
	<tr>
	<td style=\"background: #EAEAEA; text-align: center;\" align=\"center\" bgcolor=\"#EAEAEA\"><center>
	<p style=\"font-size: 12px; margin: 0;\"><strong>Thank you and have a nice day!</p>
	</center></td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>
	</div>',
    2,
    'Order {{var order_increment_id}}, {{var namevar}}'
);
");
$installer->endSetup();
?>