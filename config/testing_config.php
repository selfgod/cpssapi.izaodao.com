<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//DB配置
//zd_netschool数据库配置
defined('M_DB_HOST') OR define('M_DB_HOST', '192.168.1.199');
defined('M_DB_NAME') OR define('M_DB_NAME', 'zd_netschool');
defined('M_DB_USER') OR define('M_DB_USER', 'zaodaotdb');
defined('M_DB_PWD') OR define('M_DB_PWD', 'Izaodaotdb789');
//zd_class数据库配置
defined('MINOR_DB_HOST') OR define('MINOR_DB_HOST', '192.168.1.199');
defined('MINOR_DB_NAME') OR define('MINOR_DB_NAME', 'zd_class');
defined('MINOR_DB_USER') OR define('MINOR_DB_USER', 'zaodaotdb');
defined('MINOR_DB_PWD') OR define('MINOR_DB_PWD', 'Izaodaotdb789');
//zd_uc数据库配置
defined('MUC_DB_HOST') OR define('MUC_DB_HOST', '192.168.1.199');
defined('MUC_DB_NAME') OR define('MUC_DB_NAME', 'zd_uc');
defined('MUC_DB_USER') OR define('MUC_DB_USER', 'zaodaotdb');
defined('MUC_DB_PWD') OR define('MUC_DB_PWD', 'Izaodaotdb789');

defined('M_DSN') OR define('M_DSN', 'mysql:host='.M_DB_HOST.';dbname='.M_DB_NAME);
defined('MINOR_DSN') OR define('MINOR_DSN', 'mysql:host='.MINOR_DB_HOST.';dbname='.MINOR_DB_NAME);
defined('UC_DSN') OR define('UC_DSN', 'mysql:host='.MUC_DB_HOST.';dbname='.MUC_DB_NAME);

defined('PUBLIC_STATIC_DIR') OR define('PUBLIC_STATIC_DIR', __DIR__ . '/../../CommonStatic/');
//Redis配置
defined('REDIS_HOST') OR define('REDIS_HOST', '192.168.1.198');
defined('REDIS_PORT') OR define('REDIS_PORT', 6379);
//基础数据缓存默认开启状态
defined('BASE_DATA_CACHE_OPEN') OR define('BASE_DATA_CACHE_OPEN', FALSE);

//老域名
defined('PUBLIC_STATIC_DIR') OR define('PUBLIC_STATIC_DIR', __DIR__.'/../../CommonStatic/');
require_once PUBLIC_STATIC_DIR . 'config/testing.php';

defined('CPSS_DOMAIN') OR define('CPSS_DOMAIN', LINK_HOST_CPSS_JP);
defined('MASTER_DOMAIN') OR define('MASTER_DOMAIN', LINK_HOST_JP);
defined('KNOW_ZAODAO') OR define('KNOW_ZAODAO', LINK_HOST_KNOW);
defined('SS_DOMAIN') OR define('SS_DOMAIN', RESOURCE_HOST_JPSS);
defined('PASSPORT_DOMAIN') OR define('PASSPORT_DOMAIN', LINK_HOST_PASSPORT);
defined('BASE_URL') OR define('BASE_URL', LINK_HOST_CPSS_JP);