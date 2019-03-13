<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Response
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function json($data)
    {
        switch (gettype($data)) {
            case 'boolean':
                $ret = $data ? 'true' : 'false';
                break;
            case 'NULL':
                $ret = 'null';
                break;
            case 'integer':
                $ret = (int)$data;
                break;
            case 'double':
            case 'float':
                $ret = str_replace(',', '.', (float)$data); // locale-independent representation
                break;
            case 'string':
            default:
                $ret = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $this->CI->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output($ret)
            ->_display();
        exit;
    }

    public function jsonp($data)
    {
        $handler = $this->CI->input->get('callback');
        $ret = $handler.'('.json_encode($data,  JSON_UNESCAPED_UNICODE).');';
        $this->CI->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output($ret)
            ->_display();
        exit;
    }

    /**
     * 返回格式化后的jsonp
     * @param $code
     * @param array $data
     * @param string $msg
     */
    public function formatJsonp($code, $data = [], $msg = '')
    {
        $code = intval($code);
        $this->jsonp(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 返回格式化后的json
     * @param $code
     * @param array $data
     * @param string $msg
     */
    public function formatJson($code, $data = [], $msg = '')
    {
        $code = intval($code);
        $this->json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 原始输出
     * @param $data
     */
    public function raw($data)
    {
        echo $data;
    }

    public function html($data)
    {
        echo $data;
    }
}