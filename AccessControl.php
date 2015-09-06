<?php
namespace kma\restwall;

use Yii;
use yii\di\Instance;
use yii\base\Module;
use yii\base\ActionFilter;
use yii\web\User;
/**
 * Access Control Filter (ACF) is a simple authorization method that is best used by applications that only need some simple access control.
 * As its name indicates, ACF is an action filter that can be attached to a controller or a module as a behavior.
 * ACF will check a set of access rules to make sure the current user can access the requested action.
 *
 * To use AccessControl, declare it in the application config as behavior.
 * For example.
 *
 * ~~~
 * 'as access' => [
 *     'class' => 'mdm\admin\classes\AccessControl',
 *     'allowActions' => ['site/login', 'site/error']
 *     or 'denyActions' => ['site/login', 'site/error']
 * ]
 * ~~~
 *
 * @property User $user
 *
 * @author Mr.You D <youjingqiang@gmail.com>
 * @since 1.0
 */
class AccessControl extends ActionFilter
{
	private $_user = 'user';
	
	public $allowActions = [];
	
	public $denyActions  = [];
	
	public function init(){
		parent::init();
		if($this->allowActions && $this->denyActions){
			throw new \yii\base\InvalidConfigException("allowActions and denyActions only one can be set");
		}
	}
	public function getUser() {
		if(!$this->_user instanceof User){
			$this->_user = Instance::ensure($this->_user,User::className());
		}
		return $this->_user;
	}
	
	public function setUser($user) {
		$this->_user = $user;
	}

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $actionId = $action->getUniqueId();
        $user = $this->getUser();
        if ($user->can('/' . $actionId)) {
            return true;
        }
        $obj = $action->controller;
        do {
            if ($user->can('/' . ltrim($obj->getUniqueId() . '/*', '/'))) {
                return true;
            }
            $obj = $obj->module;
        } while ($obj !== null);
        $this->denyAccess($user);
    }
    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param  yii\web\User $user the current user
     * @throws yii\web\ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
    	Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        throw new \yii\web\UnauthorizedHttpException("You are not allowed to perform this action.",49401);
    }
    /**
     * @inheritdoc
     */
    protected function isActive($action)
    {
        $uniqueId = $action->getUniqueId();
        if ($uniqueId === Yii::$app->getErrorHandler()->errorAction) {
            return false;
        }
        $user = $this->getUser();
        if ($user->getIsGuest() && is_array($user->loginUrl) && isset($user->loginUrl[0]) && $uniqueId === trim($user->loginUrl[0], '/')) {
            return false;
        }
        if ($this->owner instanceof Module) {
            // convert action uniqueId into an ID relative to the module
            $mid = $this->owner->getUniqueId();
            $id = $uniqueId;
            if ($mid !== '' && strpos($id, $mid . '/') === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }
        if($this->allowActions){
	        foreach ($this->allowActions as $route) {
	            if (substr($route, -1) === '*') {
	                $route = rtrim($route, "*");
	                if ($route === '' || strpos($id, $route) === 0) {
	                    return false;
	                }
	            } else {
	                if ($id === $route) {
	                    return false;
	                }
	            }
	        }
        }
        if($this->denyActions){
        	foreach ($this->denyActions as $route) {
        		if (substr($route, -1) === '*') {
        			$route = rtrim($route, "*");
        			if ($route === '' || strpos($id, $route) === 0) {
        				return true;
        			}
        		} else {
        			if (strpos($id,$route) === 0 || $id === $route) {
        				return true;
        			}
        		}
        	}
        	return false;
        }
        if ($action->controller->hasMethod('allowAction') && in_array($action->id, $action->controller->allowAction())) {
            return false;
        }
        return true;
    }
}