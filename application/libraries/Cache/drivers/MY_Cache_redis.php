<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Cache_redis extends CI_Cache_redis
{
    /**
     * 获取存储的字符串值
     * @param string $key
     * @return bool|string
     */
    public function get($key)
    {
        return $this->_redis->get($key);
    }

    /**
     * 批量获取
     * @param $key
     * @param $ids
     * @param $closure
     * @param array $fields
     * @param string $primary
     * @param int $ttl
     * @return array
     */
    public function mGetByIds($key, $ids, $closure, array $fields, $primary = 'id', $ttl = 7200)
    {
        if (empty($ids)) {
            return [];
        }
        $keys = $missKeys = $results = [];
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $id) {
            $keys[] = sprintf(get_options('baseDataCacheKey', $key), $id);
        }

        $values = $this->_redis->mGet($keys);
        foreach ($values as $i => $value) {
            $diff = [];
            if ($value === FALSE) {
                $missKeys[] = $ids[$i];
            } else {
                $value = json_decode($value, TRUE);
                if (!empty($fields)) {//查询字段与得到数据字段差集
                    $diff = array_diff($fields, array_keys($value));
                }
                if (count($diff) > 0) {
                    $missKeys[] = $ids[$i];
                } else {
                    $results[$value[$primary]] = $value;
                }
            }
        }
        if (!empty($missKeys)) {
            if (!empty($fields)) {
                $dbValues = $closure($missKeys, implode(',', $fields));
            } else {
                $dbValues = $closure($missKeys);
            }
            if (!empty($dbValues)) {
                $this->pipeline();
                foreach ($dbValues as $item) {
                    $results[$item[$primary]] = $item;
                    $this->_redis->setEx(sprintf(get_options('baseDataCacheKey', $key), $item[$primary]), $ttl, json_encode($item, JSON_UNESCAPED_UNICODE));
                }
                $this->sync();
            }
        }
        return $results;
    }

    /**
     * 获取格式是json存储的缓存内容
     * @param $category
     * @param $id
     * @return bool|string
     */
    public function jsonGet($category, $id)
    {
        if (is_array($id)) {
            $id = md5(implode(',', $id));
        }
        $key = sprintf(get_options('baseDataCacheKey', $category), $id);
        $value = $this->get($key);
        if (empty($value)) {
            return FALSE;
        } else {
            return json_decode($value, TRUE);
        }
    }

    /**
     * 设置是json格式存储的缓存
     * @param $category
     * @param $id
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function jsonSet($category, $id, $value, $ttl = 0)
    {
        if (!empty($value)) {
            if (is_array($id)) {
                $id = md5(implode(',', $id));
            }
            $key = sprintf(get_options('baseDataCacheKey', $category), $id);
            if ($ttl) {
                return $this->_redis->set($key, json_encode($value, JSON_UNESCAPED_UNICODE), $ttl);
            } else {
                return $this->_redis->set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 设置key的过期时间
     * @param string $key
     * @param int $ttl
     * @return bool
     */
    public function setTimeout($key, $ttl)
    {
        return $this->_redis->expire($key, $ttl);
    }

    /**
     * 向集合中添加数据
     * @param $key
     * @param $score
     * @param $value
     * @return int
     */
    public function zAdd($key, $score, $value)
    {
        return $this->_redis->zAdd($key, $score, $value);
    }

    /**
     * 降序返回指定范围的有序集合内容
     * @param $key
     * @param $start
     * @param $end
     * @param bool
     * @return array
     */
    public function zRevRange($key, $start, $end, $withscores = false)
    {
        return $this->_redis->zRevRange($key, $start, $end, $withscores);
    }

    /**
     * 多个集合求并集
     * @param $keyOutput
     * @param $unionArray
     * @return int
     */
    public function zUnion($keyOutput, $unionArray)
    {
        if (!is_array($unionArray)) {
            $unionArray = array();
        }
        return $this->_redis->zUnion($keyOutput, $unionArray);
    }

    /**
     * 获取指定集合中某个成员的排名
     * 从1开始
     * @param $key
     * @param $member
     * @return int 0 member不存在
     */
    public function zRevRank($key, $member)
    {
        $rank = $this->_redis->zRevRank($key, $member);
        if ($rank !== false) {
            $rank += 1;
        }
        return $rank;
    }

    /**
     * 获取指定集合中某个成员的分数
     * @param $key
     * @param $member
     * @return float
     */
    public function zScore($key, $member)
    {
        return $this->_redis->zScore($key, $member);
    }

    /**
     * 为有序集的某个成员加上增量
     * 如果key或member不存在，
     * 则相当于zAdd
     * @param $key
     * @param $member
     * @param $value
     * @return float
     */
    public function zIncrBy($key, $member, $value)
    {
        return $this->_redis->zIncrBy($key, $value, $member);
    }

    /**
     * 开启管道操作
     * @return Redis
     */
    public function pipeline()
    {
        return $this->_redis->multi(Redis::PIPELINE);
    }

    /**
     * 关闭管道同步数据
     */
    public function sync()
    {
        return $this->_redis->exec();
    }

    /**
     * 获取有序集里面的元素总个数
     * @param $key
     */
    public function zSize($key)
    {
        return $this->_redis->zSize($key);
    }

    /**
     * 获取hash表所有数据
     * @param $key
     * @return array
     */
    public function hGetAll($key)
    {
        return $this->_redis->hGetAll($key);
    }

    /**
     * 设置hash表数据
     * @param $key
     * @param $hashKeys
     * @return bool
     */
    public function hMset($key, $hashKeys)
    {
        return $this->_redis->hMset($key, $hashKeys);
    }

    /**
     *  获取hash表某单元数据
     * @param $key
     * @param $hashKey
     * @return string
     */
    public function hGet($key, $hashKey)
    {
        return $this->_redis->hGet($key, $hashKey);
    }

    /**
     * 设置hash表数据
     * @param $key
     * @param $hashKey
     * @param $value
     * @return int
     */
    public function hSet($key, $hashKey, $value)
    {
        return $this->_redis->hSet($key, $hashKey, $value);
    }

    /**
     * 设置key有效期
     * @param $key
     * @param $ttl
     * @return bool
     */
    public function expire($key, $ttl)
    {
        return $this->_redis->expire($key, $ttl);
    }

    /**
     * @param $key
     * @return bool
     */
    public function sPop($key)
    {
        return $this->_redis->sPop($key);
    }

    /**
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function sIsMember($key, $value)
    {
        return $this->_redis->sIsMember($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return int
     */
    public function sAdd($key, $value)
    {
        return $this->_redis->sAdd($key, $value);
    }
}
