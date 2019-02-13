<?php
if(!defined('HOST'))die('not access');
//系统配置文件		
return array(
	'url'		=> 'http://oakaoqin.com/',		//系统URL
	'localurl'	=> '',			//本地系统URL，用于服务器上浏览地址
	'title'		=> 'william办公系统',	//系统默认标题
	'apptitle'	=> 'william测试服OA',			//APP上或PC客户端上的标题
	'db_host'	=> 'rm-2zer3x3898tm25pq4zo.mysql.rds.aliyuncs.com',		//数据库地址
	'db_user'	=> 'william_oa',		//数据库用户名
	'db_pass'	=> 'William_oa2019',		//数据库密码
	'db_base'	=> 'william_oa2019',		//数据库名称
	'perfix'	=> 'xinhu_',	//数据库表名前缀
	'qom'		=> 'xinhu_',		//session、cookie前缀
	'highpass'	=> '',			//超级管理员密码，可用于登录任何帐号
	'db_drive'	=> 'mysqli',	//操作数据库驱动有mysql,mysqli,pdo三种
	'randkey'	=> 'xmbtqvcysjlnkfirwogeupzdha',		//系统随机字符串密钥
	'asynkey'	=> '4479348e5626b53c42cc19928f6be6ae',	//这是异步任务key
	'openkey'	=> '9375dc6a7479f221043c13eb96e88a5d',	//对外接口openkey
	'updir'		=> 'upload',	//默认上传目录
	'sqllog'	=> false,		//是否记录sql日志保存upload/sqllog下
	'asynsend'	=> false,		//是否异步发送提醒消息，为true需开启服务端
	'install'	=> true			//已安装，不要去掉啊
);