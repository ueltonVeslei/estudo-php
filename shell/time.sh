#!/bin/sh

inicio_ns2=`date +%s%N`
inicio2=`date +%s`

inicio_ns=`date +%s%N`
inicio=`date +%s`
/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalog_product_attribute
fin_ns=`date +%s%N`
fin=`date +%s`
let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo "catalog_product_attribute: -$total_ns- nanosegundos, -$total- segundos"

inicio_ns=`date +%s%N`
inicio=`date +%s`
/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalog_product_price 
fin_ns=`date +%s%N`
fin=`date +%s`
let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo "catalog_product_price: -$total_ns- nanosegundos, -$total- segundos"

#inicio_ns=`date +%s%N`
#inicio=`date +%s`
#/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalog_url 
#fin_ns=`date +%s%N`
#fin=`date +%s`
#let total_ns=$fin_ns-$inicio_ns
#let total=$fin-$inicio
#echo "catalog_url: -$total_ns- nanosegundos, -$total- segundos"

#inicio_ns=`date +%s%N`
#inicio=`date +%s`
#/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalog_product_flat  
#fin_ns=`date +%s%N`
#fin=`date +%s`
#let total_ns=$fin_ns-$inicio_ns
#let total=$fin-$inicio
#echo "catalog_product_flat: -$total_ns- nanosegundos, -$total- segundos"

#inicio_ns=`date +%s%N`
#inicio=`date +%s`
#/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalog_category_flat 
#fin_ns=`date +%s%N`
#fin=`date +%s`
#let total_ns=$fin_ns-$inicio_ns
#let total=$fin-$inicio
#echo "catalog_category_flat: -$total_ns- nanosegundos, -$total- segundos"

inicio_ns=`date +%s%N`
inicio=`date +%s`
/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalog_category_product 
fin_ns=`date +%s%N`
fin=`date +%s`
let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo "catalog_category_product: -$total_ns- nanosegundos, -$total- segundos"

inicio_ns=`date +%s%N`
inicio=`date +%s`
/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex catalogsearch_fulltext
fin_ns=`date +%s%N`
fin=`date +%s`
let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo "catalogsearch_fulltext: -$total_ns- nanosegundos, -$total- segundos"

inicio_ns=`date +%s%N`
inicio=`date +%s`
/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex cataloginventory_stock 
fin_ns=`date +%s%N`
fin=`date +%s`
let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo "cataloginventory_stock: -$total_ns- nanosegundos, -$total- segundos"

inicio_ns=`date +%s%N`
inicio=`date +%s`
/usr/bin/php /var/www/farmadelivery.com.br/current/web/shell/indexer.php --reindex tag_summary    
fin_ns=`date +%s%N`
fin=`date +%s`
let total_ns=$fin_ns-$inicio_ns
let total=$fin-$inicio
echo "tag_summary: -$total_ns- nanosegundos, -$total- segundos"

fin_ns2=`date +%s%N`
fin2=`date +%s`
let total_ns2=$fin_ns2-$inicio_ns2
let total=$fin2-$inicio2
echo "TIEMPO TOTAL: -$total_ns2- nanosegundos, -$total- segundos"
