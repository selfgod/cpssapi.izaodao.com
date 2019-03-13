<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Base_val
{
    protected $ci;
    protected $data;

    public function __construct()
    {
        $this->ci =& get_instance();
    }


    public function resultMsg($code)
    {
        $resultCode = get_options('result_code');
        if (isset($resultCode[$code])) {
            return array(
                'code' => $code,
                'msg' => $resultCode[$code]
            );
        } else {
//            throw new Exception();
            return array();
        }
    }

    public function prepareData($data)
    {
        $this->data = array();
        $this->data = $data;
    }

    abstract public function validationTasks();

}