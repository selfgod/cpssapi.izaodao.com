<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common_model extends MY_Model
{
    /**
     * 获取通用系统分类
     * @param $key
     * @param $val
     * @return array|bool
     */
    public function getCategoryVal($key, $val = 0)
    {
        if (!($key = addslashes($key))) return FALSE;
        $query = parent::$main_db->select('ssci.name,ssci.order')->from('sty_sys_category_item AS ssci')->join('sty_sys_category AS ssc', 'ssc.id=ssci.category_id', 'LEFT')->where(array(
            'ssc.key' => $key,
            'ssc.is_del' => 0,
            'ssci.is_del' => 0
        ));
        if (intval($val) > 0) {
            $query->where('ssci.order', intval($val));
            $result = $query->limit(1)->get()->row_array();
            if (!empty($result)) {
                return $result['name'];
            }
        } else {
            $result = $query->order_by('ssci.order ASC')->get()->result_array();
            if (!empty($result)) {
                $data = array();
                foreach ($result as $k => $v) {
                    $data[$v['order']] = $v['name'];
                }
                return $data;
            }
        }
        return FALSE;
    }

    /**
     * 获取分类tier
     * @param $key
     * @return array
     */
    public function getCategoryTier($key)
    {
        $result = parent::$main_db->select('ssci.name, ssci.order, ssci.tier')->from('sty_sys_category_item AS ssci')->join('sty_sys_category AS ssc', 'ssc.id=ssci.category_id', 'LEFT')->where(array(
            'ssc.key' => $key,
            'ssc.is_del' => 0,
            'ssci.is_del' => 0
        ))->get()->result_array();
        $data = array();
        if (!empty($result)) {
            foreach ($result as $k => $v) {
                $data[$v['order']] = array('name' => $v['name'], 'tier' => $v['tier']);
            }
        }
        return $data;
    }

    /**
     * 获取组的分类数据
     * @param $group
     * @return mixed
     */
    public function getGroupCategory($group)
    {
        $result = [];
        $group = parent::$main_db->select('ssc.title, ssci.category_id, ssci.name')
            ->from('sty_sys_category_item AS ssci')
            ->join('sty_sys_category AS ssc', 'ssc.id = ssci.category_id', 'LEFT')
            ->where([
                'ssc.group_name' => $group,
                'ssc.is_del' => 0,
                'ssci.is_del' => 0

            ])
            ->order_by('ssci.category_id ASC')
            ->get()->result_array();
        if (!empty($group)) {
            foreach ($group as $item) {
                if (!isset($result[$item['category_id']])) {
                    $result[$item['category_id']]['id'] = $item['category_id'];
                    $result[$item['category_id']]['title'] = $item['title'];
                }
                $result[$item['category_id']]['values'][] = $item;
            }
        }
        return $result;
    }

    /**
     * 获取基本分类
     * @param $key
     * @return array|null
     */
    public function getBaseSetValue($key)
    {
        $item = parent::$minor_db->select('set_val,default_val')->from('zd_base_set')->where('set_key', $key)->get()->row_array();
        if (!empty($item)) {
            $value = $item['set_val'] ? $item['set_val'] : $item['default_val'];
            if ($value) {
                $value = explode("\n", $value);
                if (count($value) > 1) {
                    return array_map(function ($val) {
                        return trim($val);
                    }, $value);
                } else {
                    return $value[0];
                }
            }
        }
        return NULL;
    }

    /**
     * 获取内容分类
     * @param $category_id
     * @return array
     */
    public function getScheduleCategory($category_id)
    {
        $data = array();
        $query = parent::$main_db->select('id,name')
            ->from('sty_schedule_category')
            ->where(array('parent_id' => $category_id, 'is_del' => '0'))
            ->order_by('order', 'ASC')
            ->get()->result_array();
        if (!empty($query)) {
            foreach ($query as $item) {
                $data[$item['id']] = $item['name'];
            }
        }
        return $data;
    }

    /**
     * 获取一周内的时间
     * @param $date
     * @return array
     */
    public function onWeekDate($date)
    {
        $result = [];
        $week_day = get_options('week_day');
        $nowData = date('Y-m-d');
        if (empty($date) && empty(strtotime($date))) {
            $date = $nowData;
        }
        for ($i = 0; $i < 7; $i++) {
            if ($i == 0) {
                $result[$i]['date'] = date('Y-m-d', strtotime($date . ' -1 day'));
            } elseif ($i == 1) {
                $result[$i]['date'] = $date;
            } else {
                $result[$i]['date'] = date('Y-m-d', strtotime($date . ' +' . ($i - 1) . ' day'));;
            }
            $result[$i]['name'] = $this->formatRecentDate($result[$i]['date'], 'm/d');
            $result[$i]['week'] = $week_day[date('w', strtotime($result[$i]['date']))];
        }
        return $result;
    }

    /**
     * 格式化最近的时间
     * @param $date
     * @param string $format
     * @return false|string
     */
    public function formatRecentDate($date, $format = 'm月d日')
    {
        $nowData = date('Y-m-d');
        $formatData = date('Y-m-d', strtotime($date));
        $recently_date = get_options('recently_date');
        if ($formatData == date('Y-m-d', strtotime($nowData . ' -1 day'))) {
            $result = $recently_date[0];
        } elseif ($formatData == $nowData) {
            $result = $recently_date[1];
        } elseif ($formatData == date('Y-m-d', strtotime($nowData . ' +1 day'))) {
            $result = $recently_date[2];
        } elseif ($formatData == date('Y-m-d', strtotime($nowData . ' +2 day'))) {
            $result = $recently_date[3];
        } else {
            $result = date($format, strtotime($formatData));
        }
        return $result;
    }

    /**
     * 获取广告版位
     * @param $id 广告id
     * @return array|mixed
     */
    public function getAdslot($id)
    {
        $adver = [];
        $now_time = date('Y-m-d H:i:s');
        $result = parent::$minor_db->select('title, parameters, starttime, endtime')->from('zd_ad')->where([
            'category_id' => $id,
            'available' => 1,
        ])->order_by('displayorder DESC')->limit(1)->get()->result_array();
        if (!empty($result)) {
            foreach ($result as $item) {
                $imgInfo = $item;
                if (intval($item['starttime']) == 0 && intval($item['endtime']) == 0) break;
                if (intval($item['starttime']) == 0 && $now_time <= date('Y-m-d 23:59:59', $item['endtime'])) break;
                if (intval($item['endtime']) == 0 && $now_time >= date('Y-m-d 00:00:00', $item['starttime'])) break;
                if ($now_time >= date('Y-m-d 00:00:00', $item['starttime']) && $now_time <= date('Y-m-d 23:59:59', $item['endtime'])) break;
            }
        }
        if (!empty($imgInfo) && !empty($imgInfo['parameters'])) {
            $adver = unserialize($imgInfo['parameters']);
            $adver['title'] = !empty($imgInfo['title']) ? $imgInfo['title'] : '';
        }
        return $adver;
    }

    /**
     * 返回数据表数据列表对象
     * @param $table
     * @param array $obj
     * @param array $join
     * @param bool $main_db
     * @return mixed
     */
    public function selectObjData($table, array $obj = [], array $join = [], $main_db = TRUE)
    {
        if ($main_db === TRUE) {
            $query = parent::$main_db;
        } else {
            $query = parent::$minor_db;
        }
        $query->from($table);
        if (isset($join) && !empty($join)) {
            foreach ($join as $joinVal) {
                $joinType = '';
                if (!empty($joinVal['joinType'])) $joinType = $joinVal['joinType'];
                if (!empty($joinVal['joinTable']) && !empty($joinVal['joinCond'])) {
                    $query->join($joinVal['joinTable'], $joinVal['joinCond'], $joinType);
                }
            }
        }
        if (isset($obj['field']) && !empty($obj['field'])) {
            if (is_array($obj['field'])) {
                $field = implode(',', $obj['field']);
            } else {
                $field = $obj['field'];
            }
            $query->select($field);
        }
        if (isset($obj['where']) && !empty($obj['where'])) {
            $where_string = '';
            $where_in = [];
            $where = $obj['where'];
            if (isset($where['_string'])) {
                if (!empty($where['_string'])) {
                    $where_string = $where['_string'];
                }
                unset($where['_string']);
            }
            if (isset($where['where_in'])) {
                if (!empty($where['where_in'])) {
                    $where_in = $where['where_in'];
                }
                unset($where['where_in']);
            }
            if (!empty($where)) {
                $query->where($where);
            }
            if (!empty($where_in)) {
                $query->where_in($where_in[0], $where_in[1]);
            }
            if (!empty($where_string)) {
                $query->where($where_string);
            }
        }
        if (isset($obj['count']) && $obj['count'] === TRUE) {
            return $query->count_all_results();
        }
        if (isset($obj['order']) && !empty($obj['order'])) {
            if (is_array($obj['order'])) {
                $order = implode(',', $obj['order']);
            } else {
                $order = $obj['order'];
            }
            $query->order_by($order);
        }
        $row = FALSE;
        if (isset($obj['limit']) && !empty($obj['limit'])) {
            if (is_array($obj['limit'])) {
                $limit = $obj['limit'];
                if (count($limit) == 1) {
                    $limit_0 = intval($limit[0]);
                    $query->limit($limit_0);
                    if ($limit_0 === 1) {
                        $row = TRUE;
                    }
                } elseif (count($limit) == 2) {
                    $query->limit(intval($limit[1]), intval($limit[0]));
                }
            } else {
                $limit_0 = intval($obj['limit']);
                if ($limit_0 === 1) {
                    $row = TRUE;
                }
                if ($limit_0 > 0) {
                    $query->limit($limit_0);
                }
            }
        }
        if ($row === TRUE) {
            $result = $query->get()->row_array();
        } else {
            $result = $query->get()->result_array();
        }
        return $result;
    }
}