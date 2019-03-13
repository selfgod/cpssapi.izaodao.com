<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Validation
{
    protected $class;

    public function validate($action, $data = array())
    {
        if (empty($this->class)) {
            $file = __DIR__ . DIRECTORY_SEPARATOR . 'validates' .
                DIRECTORY_SEPARATOR . 'Business_validation.php';
            if (file_exists($file)) {
                require_once($file);
            } else {
                throw new \Exception("找不到validation文件");
            }
            $this->class = new Business_validation();
        }

        $result = FALSE;
        $validationTasks = $this->class->validationTasks();
        if (isset($validationTasks[$action])) {
            $this->class->prepareData($data);
            $prepareFunc = 'prepare_'.$action;
            if (method_exists($this->class, $prepareFunc)) {
                $this->class->$prepareFunc($data);
            }

            foreach ($validationTasks[$action] as $task) {
                if (method_exists($this->class, $task)) {
                    $code = $this->class->{$task}();
                    if ($code !== 200) {
                        return $this->class->resultMsg($code);
                    }
                } else {
                    //throw exception
                }
            }
            return TRUE;
        }

        return $result;
    }
}