<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . '/Thrift/Clients/ThriftClient.php';

use ThriftClient\ThriftClient;

class Thrift
{
    protected $CI;

    public function __construct()
    {
        $addrs = explode('|', RPC_HOST);
        $confArr = [];
        foreach ($addrs as $index => $addr) {
            $confArr['Config']['addresses'][] = $addr . ':' . RPC_PORT;
        }
        ThriftClient::config($confArr);
        $confArr = [];
        $services = ThriftClient::instance('Config')->getServices();
        foreach ($services as $name => $port) {
            foreach ($addrs as $addr) {
                $confArr[$name]['addresses'][] = $addr . ':' . $port;
            }
        }

        ThriftClient::config($confArr);
    }

    /**
     * 获取thrift service实例
     * @param $name
     * @return object
     */
    public function service($name)
    {
        return ThriftClient::instance($name);
    }
}