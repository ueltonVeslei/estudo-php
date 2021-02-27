###################################################
# Autor: Anderson Vincoletto
# Project: Farmadelivery
# Description: corretor de índices desatualizados
###################################################
echo "Iniciando atualização de índices"
/usr/local/bin/php indexer.php --reindex catalog_product_attribute
/usr/local/bin/php indexer.php --reindex catalog_product_price
/usr/local/bin/php indexer.php --reindex cataloginventory_stock
#/usr/local/bin/php indexer.php --reindex catalog_category_product
#/usr/local/bin/php indexer.php --reindex catalogsearch_fulltext
echo "Processo finalizado com sucesso!"