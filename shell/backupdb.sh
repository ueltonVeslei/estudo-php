#!/bin/bash
################################
# Autor: srael
# Project: Farmadelivery
# message: none
################################

function usage {
        echo "usage: backupdb.sh [[-d database] [-p path] | [-h]]"
}

if [ "$1" = "" ]; then
        usage
        exit 1
fi

while [ "$1" != "" ]; do
    case $1 in
        -d | --database )       shift
                                mysql_db="$1"
                                ;;
        -p | --path )           shift
                                dump="$1"
                                ;;
        -h | --help )           usage
                                exit
                                ;;
        * )                     usage
                                exit 1
    esac
    shift
done

if [[ $dump = "" ]]; then
   dump="$HOME/dump.sql.gz"
fi

if [ -f $dump ]; then
   echo "Deleting previous export"
   rm -f $dump
fi

echo "Dumping the database $mysql_db to $dump"
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
catalog_category_flat_store_13 \
catalog_category_flat_store_15 \
catalog_category_flat_store_17 \
catalog_category_flat_store_18 \
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
catalog_product_flat_13 \
catalog_product_flat_15 \
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
| sed 's%/farmatem.catalog.farmadelivery.com.br/%/www.farmatem.com.br/%g' \
| sed 's%/catalog.farmadelivery.com.br/%/farmadelivery.com.br/%g' \
| gzip > $dump
