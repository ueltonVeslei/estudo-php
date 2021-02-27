###################################################
# Autor: Anderson Vincoletto
# Project: Farmadelivery
# Description: corretor de busca sem resultados
###################################################
echo "Iniciando correção da busca"
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalog_product_attribute
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalog_product_price
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalog_product_flat
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalog_category_flat
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalog_category_product
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalogsearch_fulltext
echo "Processo finalizado com sucesso!"