<?php
require dirname(__FILE__).'/secure.php';
return array(
	'DB_TYPE'              => DB_TYPE,
	'DB_HOST'              => DB_HOST,
	'DB_NAME'              => DB_NAME,
	'DB_USER'              => DB_USER,
	'DB_PWD'               => DB_PWD,
	'DB_PORT'              => DB_PORT,
	'DB_PREFIX'            => 'tw_',
	'ACTION_SUFFIX'        => '',
	'MULTI_MODULE'         => true,
	'MODULE_DENY_LIST'     => array('Common', 'Runtime'),
	'MODULE_ALLOW_LIST'    => array('Home', 'Admin', 'Mobile', 'Support','Agent', 'lws'),
	'URL_MODULE_MAP' => array('lws'=>'Home'),
	'DEFAULT_MODULE'       => WHERECOME,
	'AUTO_KEY'       => "aHR0cDovL2NvZGUuc2NybHB0LmNvbS9kb2xvZ2luLnBocA==", 
	'URL_CASE_INSENSITIVE' => false,
	'URL_MODEL'            => 2,
	'URL_HTML_SUFFIX'      => '',
	'LANG_SWITCH_ON'       => true, //开启多语言支持开关
    'COOKIE_EXPIRE'         =>  864000*7,    // Cookie有效期
	'LANG_AUTO_DETECT'     => true, // 自动侦测语言
	'DEFAULT_LANG'         => 'en-us', // 默认语言
	'LANG_LIST'     	   => 'yn-yn,zh-cn,en-us,fr-fr,de-de,it-it,ja-jp,ko-kr,tr-tr,pt-pt,es-es',
	'VAR_LANGUAGE'         => 'Lang', //默认语言切换变量
    'NATION'     =>array('zh_CN'=>'中国','en_US'=>'美国',),
    'PUBLICCONTRACT'              =>  'TFxnxEBbCiMCJGYtYcALevD3rZ9W6s7zAL', //公共合约地址
    'APP_DEBUG'              =>  true,
	'TMPL_ACTION_ERROR' => './Public/error.html', //默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS' => './Public/success.html', //默认成功跳转对应的模板文件
    'csjifen'=>100,//注册赠送积分
	);
?>