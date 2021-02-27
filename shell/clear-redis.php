<?php

error_reporting(E_ALL ^ E_NOTICE);

function clear_redis($host, $db, $port = 6379)
{
   $redis = new Redis();
   $redis->connect($host, $port);

   if (!$redis->ping()) {
      die("Cannot connect to redis server.\n");
   }

   $redis->select($db);
   $redis->flushdb();
}

$xml = simplexml_load_file(dirname(__DIR__) . '/../../../shared/web/app/etc/local.xml', NULL, LIBXML_NOCDATA);

$cache['type'] = (string)$xml->global->cache->backend;
$cache['host'] = (string)$xml->global->cache->backend_options->server;
$cache['db']   = (string)$xml->global->cache->backend_options->database;
$cache['port'] = (string)$xml->global->cache->backend_options->port;

if (($cache['type'] == 'Cm_Cache_Backend_Redis') && isset($cache['host']) && $cache['host']) {
    clear_redis($cache['host'], $cache['db'], $cache['port']);

    echo "The redis cache was flushed.\n";
}

/* Limpieza en el caso de ser un Enterprise con FPC en Redis */
$fpc['type'] = (string)$xml->global->full_page_cache->backend;
$fpc['host'] = (string)$xml->global->full_page_cache->backend_options->server;
$fpc['db']   = (string)$xml->global->full_page_cache->backend_options->database;
$fpc['port'] = (string)$xml->global->full_page_cache->backend_options->port;

if (($fpc['type'] == 'Cm_Cache_Backend_Redis') && isset($fpc['host']) && $fpc['host']) {
    clear_redis($fpc['host'], $fpc['db'], $fpc['port']);
    echo "The redis full page cache was flushed.\n";
}
