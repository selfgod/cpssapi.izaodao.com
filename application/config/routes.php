<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

//学习系统左侧菜单选择
$route['learningsystem/schedule/latest'] = 'learning_system/myschedule/updateSelectedSchedule';
$route['learningsystem/schedule/calendar'] = 'learning_system/myschedule/monthlyCalendar';
$route['learningsystem/schedule/delete'] = 'learning_system/myschedule/delete';
//报到
$route['learningsystem/checkin'] = 'learning_system/myschedule/checkIn';
//下载录像
$route['learningsystem/download/(:num)'] = 'learning_system/download/index/$1';
$route['learningsystem/download_attach'] = 'learning_system/download/attachment';
//录播回顾
$route['learningsystem/review/(:num)'] = 'learning_system/review/reviewPage/$1';
//排行榜
$route['learning/rank'] = 'public/rank/rankPanel';
$route['learning/rankCategory/(:any)'] = 'public/rank/rankPanelBody/$1';
$route['learning/rankList'] = 'public/rank/rankList';

//商品激活
$route['api/goods/activate'] = 'api/goods/activate';
$route['goods/activate'] = 'learning_center/mygoods/activateGoods';
//一课一练，单元测试完成
$route['api/exercise/done/(:any)'] = 'api/exercise/done/$1';
//已购产品
$route['purchased'] = 'learning_center/mygoods/purchased';
$route['purchased/summary'] = 'learning_center/mygoods/purchasedSummary';
$route['purchased/list'] = 'learning_center/mygoods/purchasedList';
$route['purchased/curricular/(:any)'] = 'learning_center/mygoods/purchasedCurricular/$1';
$route['purchased/curricular/(:any)/audit'] = 'learning_center/mygoods/curricularAudit/$1';
//用户信息
$route['api/user/info'] = 'api/user/baseInfo';
$route['api/user/learn_info'] = 'api/user/learnInfo';
//////////////////////////////
//学习中心
$route['learn/(:any)'] = 'learning_center/learn/$1';
$route['privilege'] = 'learning_center/privilege';
$route['teaching/(:any)'] = 'teaching_center/holiday/$1';
//续费升级
$route['upgrade'] = 'upgrade/upgrade/index';
$route['upgrade/accounting'] = 'upgrade/upgrade/accounting';
$route['upgrade/goods/(:num)'] = 'upgrade/upgrade/goods/$1';
$route['upgrade/intention'] = 'upgrade/upgrade/intention';
$route['zdtalk/lessonClassroom'] = 'learning_system/zdtalk/lessonClassroom';
//zdtalk网启测试页
$route['zdtalk/classroom'] = 'learning_system/zdtalk/classroomUrl';
$route['zdtalk/updatePwd'] = 'learning_system/zdtalk/updatePwd';
$route['learning_system/zdtalk/(:any)'] = 'learning_system/zdtalk/main/$1';
//单元测试
$route['exam/unitTest/prepare/(:num)'] = 'exam/unitTest/prepare/$1';
$route['exam/unitTest/(:num)'] = 'exam/unitTest/index/$1';

//等级测试
$route['grade/(:any)'] = 'level_test/grade/$1';

$route['default_controller'] = 'learning_center/learn/main';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
