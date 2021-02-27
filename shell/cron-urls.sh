###################################################
# Autor: Anderson Vincoletto
# Project: Farmadelivery
# Description: corretor de índices de URLs
###################################################
echo "Iniciando atualização de índices de URLs"
/usr/bin/php /var/www/farmadelivery.com.br/web/shell/indexer.php --reindex catalog_url
echo "Processo finalizado com sucesso!"