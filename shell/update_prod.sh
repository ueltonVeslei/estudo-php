#!/bin/bash

dump="/tmp/farmadel.$(date +%Y%m%d).sql.gz"
email="srael@onestic.com"
farma_path="/var/www/farmadelivery.com.br"
farma_web="$farma_path/current/web/"
mysql_db="teste2"
mysql_db_prod="teste2"

#echo "Actualizando precios"
#php -f $farma_web/shell/updatestock.php || exit 1

#echo "Reindexando precios"
#php $farma_web/shell/indexer.php --reindex catalog_product_price

#echo "Reindexando stock"
#php $farma_web/shell/indexer.php --reindex cataloginventory_stock

if [ -f $dump ]
then
   echo "Borrando volcado anterior"
   rm -f $dump
fi

echo "Realizando el volcado de la base de datos en preproduccion"
mysqldump $mysql_db \
catalog_category_anc_categs_index_idx \
catalog_category_anc_categs_index_tmp \
catalog_category_anc_products_index_idx \
catalog_category_anc_products_index_tmp \
catalog_category_entity \
catalog_category_entity_datetime \
catalog_category_entity_decimal \
catalog_category_entity_int \
catalog_category_entity_text \
catalog_category_entity_varchar \
catalog_category_flat_store_1  \
catalog_category_flat_store_10 \
catalog_category_flat_store_12 \
catalog_category_flat_store_2  \
catalog_category_flat_store_3  \
catalog_category_flat_store_4  \
catalog_category_flat_store_5  \
catalog_category_flat_store_6  \
catalog_category_flat_store_8  \
catalog_category_product \
catalog_category_product_index \
catalog_category_product_index_enbl_idx \
catalog_category_product_index_enbl_tmp \
catalog_category_product_index_idx \
catalog_category_product_index_tmp \
catalog_compare_item \
catalog_eav_attribute \
catalog_product_bundle_option \
catalog_product_bundle_option_value \
catalog_product_bundle_price_index \
catalog_product_bundle_selection \
catalog_product_bundle_selection_price \
catalog_product_bundle_stock_index \
catalog_product_enabled_index \
catalog_product_entity \
catalog_product_entity_datetime \
catalog_product_entity_decimal \
catalog_product_entity_gallery \
catalog_product_entity_group_price \
catalog_product_entity_int \
catalog_product_entity_media_gallery \
catalog_product_entity_media_gallery_value \
catalog_product_entity_text \
catalog_product_entity_tier_price \
catalog_product_entity_varchar \
catalog_product_flat_1  \
catalog_product_flat_10 \
catalog_product_flat_12 \
catalog_product_flat_3  \
catalog_product_flat_4  \
catalog_product_flat_5  \
catalog_product_flat_6  \
catalog_product_flat_8  \
catalog_product_index_eav \
catalog_product_index_eav_decimal \
catalog_product_index_eav_decimal_idx \
catalog_product_index_eav_decimal_tmp \
catalog_product_index_eav_idx \
catalog_product_index_eav_tmp \
catalog_product_index_group_price \
catalog_product_index_price \
catalog_product_index_price_bundle_idx \
catalog_product_index_price_bundle_opt_idx \
catalog_product_index_price_bundle_opt_tmp \
catalog_product_index_price_bundle_sel_idx \
catalog_product_index_price_bundle_sel_tmp \
catalog_product_index_price_bundle_tmp \
catalog_product_index_price_cfg_opt_agr_idx \
catalog_product_index_price_cfg_opt_agr_tmp \
catalog_product_index_price_cfg_opt_idx \
catalog_product_index_price_cfg_opt_tmp \
catalog_product_index_price_downlod_idx \
catalog_product_index_price_downlod_tmp \
catalog_product_index_price_final_idx \
catalog_product_index_price_final_tmp \
catalog_product_index_price_idx \
catalog_product_index_price_opt_agr_idx \
catalog_product_index_price_opt_agr_tmp \
catalog_product_index_price_opt_idx \
catalog_product_index_price_opt_tmp \
catalog_product_index_price_tmp \
catalog_product_index_tier_price \
catalog_product_index_website \
catalog_product_link \
catalog_product_link_attribute \
catalog_product_link_attribute_decimal \
catalog_product_link_attribute_int \
catalog_product_link_attribute_varchar \
catalog_product_link_type \
catalog_product_option \
catalog_product_option_price \
catalog_product_option_title \
catalog_product_option_type_price \
catalog_product_option_type_title \
catalog_product_option_type_value \
catalog_product_relation \
catalog_product_super_attribute \
catalog_product_super_attribute_label \
catalog_product_super_attribute_pricing \
catalog_product_super_link \
catalog_product_website \
catalogrule \
catalogrule_affected_product \
catalogrule_customer_group \
catalogrule_group_website \
catalogrule_product \
catalogrule_product_price \
catalogrule_website \
catalogsearch_fulltext \
catalogsearch_query \
catalogsearch_result \
\
eav_attribute \
eav_attribute_group \
eav_attribute_label \
eav_attribute_option \
eav_attribute_option_value \
eav_attribute_set \
eav_entity \
eav_entity_attribute \
eav_entity_datetime \
eav_entity_decimal \
eav_entity_int \
eav_entity_text \
eav_entity_type \
eav_entity_varchar \
eav_form_element \
eav_form_fieldset \
eav_form_fieldset_label \
eav_form_type \
eav_form_type_entity \
\
custom_options_group \
custom_options_group_store \
custom_options_option_description \
custom_options_relation \
\
cms_block \
cms_block_store \
cms_page \
cms_page_store \
\
core_url_rewrite \
| sed 's%/farma.catalog.farmadelivery.com.br/%/www.farmadelivery.com.br/%g' \
| sed 's%/genericos.catalog.farmadelivery.com.br/%/www.genericosdelivery.com.br/%g' \
| sed 's%/accuchek.catalog.farmadelivery.com.br/%/www.accuchekdelivery.com.br/%g' \
| sed 's%/descontos.catalog.farmadelivery.com.br/%/clubededescontos.farmadelivery.com.br/%g' \
| sed 's%/catalog.farmadelivery.com.br/%/farmadelivery.com.br/%g' \
| gzip > $dump

# Comprobamos si el dump, a pesar de existir, tiene el tamaño suficiente como para
# estar seguros de que realmente hay contenido en él
size=$(du -sb $dump | awk '{ print $1 }')
if [ $size -ge 10000 ]
then
   echo "Obtenido el fichero de volcado de forma correcta"
else
   echo "Deteniendo todo el proceso al ser el volcado más pequeño de lo normal"
   exit 1
fi

echo "Haciendo backup de la base de datos de produccion"
ansible fmdb -m shell -a "sh ~/backupdb.sh" || exit 1

echo "Moviendo las imagenes a los backends"
rsync --exclude="product/cache" -az --delete --chmod=a+rwx $farma_web/media/catalog/ farmadel@back01.farmadel.zen.onestic.com:/export/farmadel/media/catalog
rsync --exclude=".thumbs" -az --chmod=a+rwx $farma_web/media/wysiwyg/ farmadel@back01.farmadel.zen.onestic.com:/export/farmadel/media/wysiwyg

# Volvemos a comprobar que existe el fichero de volcado
if [ -f $dump ]
then
   echo "Actualizando la base de datos de produccion"
   ansible fmdb -m copy -a "src=$dump dest=$farma_path/prod.tgz" || exit 1
   ansible fmdb -m shell -a "zcat $farma_path/prod.tgz | mysql $mysql_db_prod" || exit 1
else
	echo "Deteniendo todo el proceso al no encontrar el volcado"
	exit 1
fi

echo "Esperando 60 segundos para normalizar la carga, paciencia ..."
sleep 60

echo "Borrando la cache de Redis en todos los nodos de produccion"
ansible fmfrontends:fmdb -m shell -a "php -f $farma_web/shell/clear-redis.php" || exit 1

echo "Esperando 120 segundos para normalizar la carga, paciencia ..."
sleep 120

echo "Ejecutando manageStock.php y enviando su salida a $email"
php -f $farma_web/shell/manageStock.php | mail -s 'Salida de manageStock.php desde farmadelivery.com.br' $email

echo "Reindexando Stock en produccion"
ansible fmdb -m shell -a "php $farma_web/shell/indexer.php --reindex cataloginventory_stock"

echo "Reindexando Precios en produccion"
ansible fmdb -m shell -a "php $farma_web/shell/indexer.php --reindex catalog_product_price"

exit 0

