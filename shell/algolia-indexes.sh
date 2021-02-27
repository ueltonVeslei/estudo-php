###################################################
# Autor: Anderson Vincoletto
# Project: Farmadelivery
# Description: corretor de índices Algolia
###################################################
echo "Iniciando atualização de índices do Algolia"
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex algolia_search_indexer
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex algolia_search_indexer_cat
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex algolia_search_indexer_pages
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex search_indexer_suggest
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex search_indexer_addsections
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex algolia_delete_products
echo "Processo Algolia finalizado com sucesso!"