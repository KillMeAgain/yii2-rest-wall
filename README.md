Yii2-REST-WALL
==============
*Yii2 REST/RBAC Application*

**Global Access Control**

支持正向授权和反向授权

1 Step
Add follow line into your module config `components`

```php
'authManager' => [
	    		'class' => 'yii\rbac\PhpManager'
	    	],
```	    	
