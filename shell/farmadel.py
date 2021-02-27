#!/usr/bin/env python
# -*- coding: utf8 -*-

from update_prod import *

TOKEN = "XdT8GrStBKghGIdhDDbR26Sk"
LOCAL_HOME = "/var/www/farmadelivery.com.br"
REMOTE_HOME = "/var/www/farmadelivery.com.br"
CHANNEL = "#sistemas"
DATABASE = "teste2"
EMAIL = "camila@farmadelivery.com"

farmadel = UpdateProd('back01.farmadel.zen.onestic.com')
farmadel.user = "farmadel"
farmadel.build_inventory()

farmadel.send_message("Starting PRE to PRO on FARMADELIVERY.COM.BR", CHANNEL, notifier="slack", token=TOKEN)

farmadel.set_local()
farmadel.home = LOCAL_HOME
farmadel.db = DATABASE

url_rows = farmadel.mysql_count("core_url_rewrite")
if url_rows < 100000 or url_rows > 700000:
    farmadel.send_message("Cannot proceed, core_url_rewrite has %d rows on DEV FARMADELIVERY.COM.BR" % url_rows,
                          CHANNEL,
                          notifier="slack",
                          token=TOKEN)
    farmadel.send_message("Truncating the table core_url_rewrite", CHANNEL, notifier="slack", token=TOKEN)
    if farmadel.mysql_truncate("core_url_rewrite") is False:
        farmadel.send_message("Problem truncating core_url_rewrite", CHANNEL, notifier="slack", token=TOKEN)
        raise SystemExit(1)

    farmadel.send_message("Reindexing everything", CHANNEL, notifier="slack", token=TOKEN)
    farmadel.reindexer("catalog_product_attribute")
    farmadel.reindexer("catalog_product_price")
    farmadel.reindexer("catalog_url")
    farmadel.reindexer("catalog_product_flat")
    farmadel.reindexer("catalog_category_flat")
    farmadel.reindexer("catalog_category_product")
    farmadel.reindexer("catalogsearch_fulltext")
    farmadel.reindexer("cataloginventory_stock")
    farmadel.reindexer("tag_summary")

    rows_now = farmadel.mysql_count("core_url_rewrite")
    farmadel.send_message("Now core_url_rewrite has %d rows on DEV FARMADELIVERY.COM.BR, please confirm" % rows_now,
                          CHANNEL,
                          notifier="slack",
                          token=TOKEN)
    raise SystemExit(1)

dump = "dump.sql.gz"
sqldump = farmadel.backup(dump)
farmadel.src = sqldump
if farmadel.check_file() is False:
    raise SystemExit(1)

farmadel.set_remote()
farmadel.home = REMOTE_HOME
remote_backup = farmadel.backup("backup.sql.gz")
if farmadel.check_file(remote_backup) is False:
    raise SystemExit(1)

remote_sqldump = farmadel.home + '/' + dump
farmadel.dest = remote_sqldump
if farmadel.copy_file() is False:
    raise SystemExit(1)

farmadel.src = LOCAL_HOME + "/shared/web/media/catalog"
farmadel.dest = REMOTE_HOME + "/shared/web/media/catalog"
if farmadel.sync_dirs("--exclude=product/cache") is False:
    raise SystemExit(1)

farmadel.src = LOCAL_HOME + "/shared/web/media/wysiwyg"
farmadel.dest = REMOTE_HOME + "/shared/web/media/wysiwyg"
if farmadel.sync_dirs("--exclude=.thumbs") is False:
    raise SystemExit(1)

farmadel.src = remote_sqldump
if farmadel.mysql_import() is False:
    raise SystemExit(1)

farmadel.wait(60)
farmadel.check_file(remote_sqldump, delete=True)
farmadel.delete_cache("redis")
farmadel.wait(120)

farmadel.set_local()
farmadel.smart_exec("manageStock.php", options="| mail -s 'Salida de manageStock.php desde farmadelivery.com.br' %s" % EMAIL)
farmadel.check_file(sqldump, delete=True)

farmadel.set_remote()
farmadel.reindexer(index="cataloginventory_stock")
farmadel.reindexer(index="catalog_product_price")

farmadel.send_message("Everything OK on FARMADELIVERY.COM.BR", CHANNEL, notifier="slack", token=TOKEN)