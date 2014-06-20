<?
header("content-type: text/css");
require_once("../includes.php");
require_once(LIB."smarty/libs/templates_setup.php");

$tpl = Template::getInstance();
$tpl->setTemplateSet('startseite_mobile/');
$tpl->left_delimiter = '/*{';
$tpl->right_delimiter = '}*/';
$tpl->assign('ACTION', $_GET['action']);
$tpl->assign('TIME_CAPTCHA',time());
$tpl->display('style.tpl');

?>