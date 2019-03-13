<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['options_array'] = array(
    'week_day' => array(
        0 => '日',
        1 => '一',
        2 => '二',
        3 => '三',
        4 => '四',
        5 => '五',
        6 => '六',
    ),
    'recently_date' => array(
        0 => '昨天',
        1 => '今天',
        2 => '明天',
        3 => '后天'
    ),
    'result_code' => array(
        200 => 'ok',
        201 => '内部员工',
        202 => '未购买此课程体系的商品',
        203 => '不存在已加入的计划',
        //204 => '不存在计划',
        205 => '未激活商品',
        206 => '计划已加入或已过期',
        //207 => '计划未加入',
        //208 => '不存该计划下的阶段',
        209 => '已解锁该阶段或阶段已过期',
        210 => '不可加入或已加入该阶段课程',
        211 => '阶段课程存在冲突',
        212 => '次数不足',
        213 => '已添加多个同计划阶段下的阶段课程',
        214 => '次数已用光',
        215 => '前一计划阶段未全部完成',
        216 => '此课程体系未激活',
        217 => '不存在此课程体系课件',
        218 => '此课程已预约',
        219 => '此课程预约人数已满',
        220 => '此课程不在预约时间内',
        221 => '商品已过期',
        222 => '无预约次数',
        223 => '此课程未预约',
        224 => '不能预约开课时间超过有效期的课程',
        225 => '无VIP或SVIP权限',
        226 => '不存在未激活的单品课',
        227 => '此阶段课程不存在',
        228 => '上课模式与阶段课程不匹配',
        229 => '阶段课程已结课',
        230 => '此阶段加入人数已满',
        231 => '此课程预约人数不足最低开课人数',
        232 => '正在请假',
        233 => '正在休学',
        234 => '提交参数不正确',
        235 => '请假时间不在15天内',
        236 => '休学请假提交失败',
        237 => '销假失败',
        238 => '销假异常',
        239 => '休学过',
        240 => '不存在已激活未过期的商品',
        241 => '存在已预约未完成的商品',
        242 => '请在商品有效期内开始休学',
        243 => '休学时间不得少于15天，不得大于180天',
        244 => '无口语课权限',
        245 => '口语内容分类不存在',
        246 => '口语测评参数不正确',
        247 => '已申请本次测评',
        248 => '未完成本轮全部口语课，不能申请测评',
        249 => '口语测评该时间段已被预约',
        250 => '用户没有加入该阶段课程',
        251 => '计划，计划阶段，阶段课程不匹配',
        252 => '没有解锁该阶段或阶段已过期',
        253 => '没有加入该计划或计划已过期',
        254 => '阶段课程有效期不足',
        255 => '用户已经报到了',
        256 => '还没到可以报到时间',
        257 => '用户已经完成',
        258 => '还没到做题时间',
        259 => '该商品已过期或次数已用完',
        260 => '课件不存在',
        261 => '单元测试或一课一练与课件不匹配',
        262 => '当前用户正在休学中',
        263 => '商品未激活或已过期',
        264 => '不符合商品升级规则',
        265 => '没有选择升级方案',
        266 => '用户已购买该商品',
        281 => '口语测评期望时间段不正确',
        282 => 'message_user 有效期更新失败',
        283 => '存在未激活商品',
        284 => '请在允许激活时间后激活！',
        285 => '口语测评申请失败',
        286 => '用户行为信息异常',
        289 => '无正式课权限',
        300 => '早题库考场已达到人数上限',
        500 => 'error'
    ),
    'score_type' => array(
        'practice' => 1,
        'unit' => 2,
        'checkin' => 3
    ),
    'reservation_type' => array(
        0 => 1,//主修
        67 => 2,//口语
        68 => 3,//选修
        69 => 4,//专修
        70 => 5//定制
    ),
    //口语测评等级标签
    'oral_level' => array(
        1 => array('A', '初级入门', 1),
        2 => array('A', '初级基础', 2),
        3 => array('A', '初级进阶', 3),
        4 => array('B', '中级口语'),
        5 => array('C', '高级口语'),
        6 => array('商', '商务口语')
    ),
    //有效期单位
    'expire_unit' => array(
        1 => 'day',//天
        2 => 'month',//月
        3 => 'year',//年
    ),
    'expire_unit_zh' => array(
        1 => '天',//天
        2 => '个月',//月
        3 => '年',//年
    ),
    //课程体系
    'curricular_system' => array(
        'major' => 1,
        'oral' => 2,
        'elective' => 3,
        'special' => 4,
        'custom' => 5
    ),
    'curricular_category' => array(
        'major' => 0,
        'oral' => 67,
        'elective' => 68,
        'special' => 69,
        'custom' => 70
    ),
    'curricular_system_zh' => array(
        'major' => '主修课',
        'oral' => '口语课',
        'elective' => '选修课',
        'special' => '技能课',
        'custom' => '定制课'
    ),
    //上课类型 直播 、录播
    'class_schedule_type' => array(
        'live' => 1,
        'record' => 2
    ),
    //课程体系次数使用明细变化原因
    'curricular_audit_desc' => array(
        'MAJOR_ADD' => '未开课前删除课程，返还%d次',
        'MAJOR_REMOVE' => '添加新课程，扣除%d次',
        'NOMAJOR_ADD' => '取消已预约课程，返还%d次',
        'USER_NOT_ENOUGH_ADD' => '人数未满不开课，返还%d次',
        'NOMAJOR_REMOVE' => '预约新课程，扣除%d次'
    ),
    //课程体系次数使用明细操作说明
    'curricular_action_desc' => array(
        'MAJOR_ADD' => '删除',
        'MAJOR_REMOVE' => '添加',
        'NOMAJOR_ADD' => '取消预约',
        'USER_NOT_ENOUGH_ADD' => '系统取消预约',
        'NOMAJOR_REMOVE' => '预约'
    ),
    //教师权限组
    'teacher_groups' => array(
        12, 21, 25, 31, 32, 33, 35
    ),
    'finish_type' => array(
        0 => 'all',
        1 => 'finished',
        2 => 'unfinished'
    ),
    'url_link' => array(
        'get_score' => 'misc.php?mod=faq#id=29&messageid=99',
        'score_record' => 'main.php/User/SpacecpProfile/index/pagem/xfjl',
        'score_rule' => 'misc.php?mod=faq#id=29&messageid=98'
    ),
    //红点通知字段映射
    'dotNoticField' => array(
        'record' => 'report_starttime',
        'test' => 'practice_starttime',
        'unit' => 'exam_starttime'
    ),
    'reservationType' => array(
        'new' => 0,
        'reservation' => 1,
        'end' => 2
    ),
    'myMajorClassType' => array(
        'doing' => '正在进行',
        'done' => '已经结束',
        'tostart' => '即将开始'
    ),
    'reservationOralClassType' => array(
        'done' => '已经结束',
        'reserved' => '即将开始'
    ),
    'reservationClassType' => array(
        'done' => '录播回顾',
        'reserved' => '即将开始'
    ),
    //用户级别标签图
    'user_level_icon' => array(
        2 => img_url('learning_center', 'vip_icon_01.png'),
        3 => img_url('learning_center', 'svip_icon_01.png'),
        4 => img_url('learning_center', 'forever_icon_01.png')
    ),
    'sys_category' => array(
        'upgrade' => 'svip_key7',//升级分类
        'class_time' => 'svip_key10',//上课时间
        'fit_basic' => 'svip_key4',//适合基础
        'label' => 'svip_key12',//适合基础

    ),
    //用户学分记录content内容
    'score_log_content' => array(
        'practice' => '练习正确率80% 完成了',
        'live' => '直播报到 完成了',
        'unit' => '测试正确率75% 完成了'
    ),
    //cpss奖学金方案id
    'scholarship_plan' => 36,
    //yy 房间号
    'yy_rome' => '80121',
    'vip_link' => MASTER_DOMAIN . 'course/Topic/talentCard',
    'svip_link' => MASTER_DOMAIN . 'course/Topic/talentCard',
    //zdtalk下载地址
    'zdtalk_download' => '//service.izaodao.com/download.do?appId=7b198387d5714e75894500a14ec5ac5e&platform=1',
    //redis中存储的sessionid前缀
    'passport_key_prefix' => 'globle_session_',
    //用户中心cookie名
    'passport_cookie_name' => 'ZDSESSIONID',
    //七牛头像前缀
    'qiniu_prefix' => '//ucstat.izaodao.com/',

    //基础数据缓存key
    'baseDataCacheKey' => array(
        'plan' => 'CACHE_CPSS_PLAN_ID:%d',
        'planStage' => 'CACHE_CPSS_PLAN_STAGE_ID:%d',
        'stage' => 'CACHE_CPSS_STAGE_ID:%d',
        'schedule' => 'CACHE_CPSS_SCHEDULE_ID:%d',
        'scheduleLesson' => 'CACHE_CPSS_SCHEDULE_LESSON_ID:%d',
        'exam_paper' => 'CACHE_CPSS_EXAM_PAPER:%d',
        'paper_questions' => 'CACHE_CPSS_PAPER_QUESTIONS:%s',
        'question_opts' => 'CACHE_CPSS_QUESTION_OPTS:%s',
        'zdt_max_num' => 'EXAM_PAPER_TOKENS:',
        'zdt_paper_examing' => 'CACHE_PAPER_EXAMING:'
    )
);
