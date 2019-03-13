<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Layout
{
    public $CI;
    protected $pageData = array();
    protected $module;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function load($layout, $module, $data = array(), $meta = array())
    {
        $this->pageData = $data;
        $this->module = $module;
        return $this->loadLayout($layout, $module, $meta);
    }

    /**
     * 加载layout
     * @param $name
     * @param string $module
     * @param array $meta
     * @return mixed
     */
    protected function loadLayout($name, $module = 'public', $meta = array())
    {
        $content = '';
        $meta = array_merge_recursive($this->requireMeta($module, 'layout', $name), $meta);
        if (!isset($meta['name'])) {
            $meta['name'] = $name;
        }
        if (!empty($meta)) {
            $components = isset($meta['components']) ? $meta['components'] : array();
            $template = isset($meta['template']) ? $meta['template'] : $name;
            if ($template !== 'none') {
                if (!$this->isRequireCustom($this->module, $template, null)) {
                    $template = 'public/' . $template;
                } else {
                    $template = $this->module . '/' . $template;
                }
            }

            foreach ($components as $chileName => $attr) {
                if (isset($attr['module'])) {
                    $module = $attr['module'];
                }
                if ($attr['type'] === 'layout') {
                    $content .= $this->loadLayout($chileName, $module, $attr);
                } elseif ($attr['type'] === 'view') {
                    $curMeta = $attr;
                    $content .= $this->loadView($chileName, $module, $this->pageData, $curMeta);
                }
            }
            $layoutData['model'] = $this->pageData;
            $layoutData['content'] = $content;
            $layoutData['meta'] = $meta;
            if ($template === 'none') {
                return $content;
            } else {
                return $this->CI->load->view($template, $layoutData, true);
            }
        } else {
            //error
            show_error('error', 500);
        }
    }

    /**
     * 加载view
     * @param $name
     * @param string $module
     * @param array $data
     * @return mixed
     */
    public function loadView($name, $module = 'public', $data = array(), $curMeta = array())
    {
        $viewPath = $module . '/' . $name;
        $viewData['meta']['name'] = $name;
        $viewData['model'] = $data;
        $meta = $this->requireMeta($module, 'view', $name);
        $viewData['meta'] = array_merge($viewData['meta'], $meta, $curMeta);
        if (isset($viewData['meta']['template'])) {
            if (!$this->isRequireCustom($module, $viewData['meta']['template'], 'view')) {
                $viewPath = 'public/' . $viewData['meta']['template'];
            } else {
                $viewPath = $module . '/' . $viewData['meta']['template'];
            }
        } elseif (isset($viewData['meta']['file'])) {
            if (!file_exists($viewData['meta']['file'])) {
                show_error('FILE: ' . $viewData['meta']['file'] . ' not exist!');
            }
            ob_start();
            require($viewData['meta']['file']);
            return ob_get_clean();
        }

        return $this->CI->load->view($viewPath, $viewData, true);
    }

    /**
     * 加载布局配置文件
     * @param $module
     * @param $type
     * @param $name
     * @return bool|array
     */
    protected function requireMeta($module, $type, $name)
    {
        $path = array(
            'modules',
            $module,
            'meta',
            $type . 's',
            $name . '.php'
        );
        $file = APPPATH . implode(DIRECTORY_SEPARATOR, $path);
        if (file_exists($file)) {
            $meta = require_once($file);
            return $meta;
        } else {
            if ($module !== 'public') {
                return $this->requireMeta('public', $type, $name);
            }
            return array();
        }
    }

    /**
     * 当没有设置module参数的时候是选择当前模块还是public模块
     * @param $name
     * @param $type
     * @return bool true 当前模块|false pulic
     */
    protected function isRequireCustom($module, $name, $type)
    {
        if ($type === 'layout') {
            $path = array(
                'modules',
                $module,
                'meta',
                'layouts',
                $name . '.php'
            );
        } else {
            $path = array(
                'modules',
                $module,
                'views',
                $name . '.php'
            );
        }

        if (file_exists(APPPATH . implode(DIRECTORY_SEPARATOR, $path))) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
