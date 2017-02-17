<?php

namespace weareenvoy\passwordroutes\services;


use weareenvoy\passwordroutes\PasswordRoutes;
use yii\base\Component;

class Authentication extends Component
{
    const ROUTE_SESSION_NAME = 'passwordprotect_session_';

    protected $_routes;
    protected $_matchedRoute;

    public function isLoginRequired()
    {
        //
        if($this->_matchedRoute !== null){
            if($this->_getSessionValue($this->_matchedRoute->id) != $this->_matchedRoute->uid){
               return true;
            }
        }else{
            if($this->_matchedRoute = $this->_getMatchedRoute()){
                if($this->_getSessionValue($this->_matchedRoute->id) != $this->_matchedRoute->uid){
                    return true;
                }
            }
        }
        return false;
    }

    public function login(string $username, string $password, int $routeId)
    {
        if(!$route = PasswordRoutes::getInstance()->routes->getRouteById($routeId)){
            return false;
        }

        if($route->username == $username && $route->password == md5($password)){
            $this->_setSession($route->id,$route->uid);
            return true;
        }

        return false;
    }

    public function getMatchedRoute()
    {
        return $this->_matchedRoute;
    }

    protected function _getMatchedRoute()
    {
        if($this->_routes === null){
            $this->_routes = PasswordRoutes::getInstance()->routes->getAllRoutes();
        }

        foreach ($this->_routes as $route){
            if($route->uri == $this->_getUri()){
                return $route;
            }
        }
        return null;
    }

    protected function _getSessionValue($key)
    {
        return \Craft::$app->session->get($this->_getSessionName($key));
    }

    protected function _setSession(string $key, string $value)
    {
        \Craft::$app->session->set($this->_getSessionName($key),$value);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function _getSessionName(string $key): string
    {
        return self::ROUTE_SESSION_NAME . md5($key);
    }

    protected function _getUri(): string
    {
        return strtolower('/'.\Craft::$app->getRequest()->getFullPath());
    }

}