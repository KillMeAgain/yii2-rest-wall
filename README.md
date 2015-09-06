Yii2-REST-WALL
==============
*Yii2 REST/RBAC Application*

**Global Access Control**

支持正向授权和反向授权

##1 Step##
Add follow code into your module config `components`

```php
'authManager' => [
	    		'class' => 'yii\rbac\PhpManager'
	    	],
```	    	
##2 Step##
Add follow code into your application config

```php
'as access' => [
	'class' => 'kma\restwall\AccessControl',
	//以下两个配置只能配置一个
	'allowActions' => [
		//'v1/*'
		//正向授权
	],
	'denyActions'  => [
		'v1/*'
		//反向授权
	]
],
```