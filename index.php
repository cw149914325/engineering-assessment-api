<?php

define('HAYPIKINGDOM', 1);
define("ROOT_PATH", dirname(__FILE__));
define('CORE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('LOG_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'kingdomlogs' . DIRECTORY_SEPARATOR);
date_default_timezone_set("Asia/Shanghai");
require CORE_PATH . 'common.php';
init();
//定义常量的文件，所有文件需要用的常量定义到里面
define("PLATFORM", 0);

//默认的动作类型,用以区分推广网站
$defaultAction = 'borrow';
$actiontype = array_key_exists('action', $_GET) ? $_GET['action'] : (array_key_exists('action', $_POST) ? $_POST['action'] : $defaultAction);

if (!$actiontype)
{
    exit;
}


require APP_PATH . 'config/frontendmapping.php';
$controlers = $nomal_mappings;

if (!array_key_exists($actiontype, $controlers))
{
    print_r(json_encode(array("confirm" => -1)));
    exit;
}

unset($_GET["action"]);
unset($_POST["action"]);
$framework = FrameWork::instance();
$framework->registerevents(array('auth_device'), array('output_device'));
$framework->run($controlers[$actiontype], 'action' . $actiontype, array_values($_POST));

quit();
?>