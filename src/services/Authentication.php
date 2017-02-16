<?php

namespace envoy\passwordprotect\services;


use envoy\passwordprotect\PasswordProtect;
use yii\base\Component;

class Authentication extends Component
{
    const ROUTE_SESSION_NAME = 'passwordprotect_session_';

    protected $_routes;
    protected $_matchedRoute;

    public function validateAuthentication()
    {
        //
        if($this->_matchedRoute !== null){
            if($this->_getSession() != $this->_matchedRoute->uid){
                \Craft::$app->getResponse()->redirect('passwordprotect/login/?routeId='. $this->_matchedRoute->id);
                \Craft::$app->end();
            }
        }else{
            if($this->_matchedRoute = $this->_getMatchedRoute()){
                if($this->_getSession() != $this->_matchedRoute->uid){
                    \Craft::$app->getResponse()->redirect('passwordprotect/login/?routeId='. $this->_matchedRoute->id);
                    \Craft::$app->end();
                }
            }
        }
    }

    protected function _getMatchedRoute()
    {
        if($this->_routes === null){
            $this->_routes = PasswordProtect::getInstance()->routes->getAllRoutes();
        }

        foreach ($this->_routes as $route){
            if(preg_match('/'.preg_quote($route->uriPattern,'/').'/',$this->_getUrl(),$matches)){
                return $route;
            }
        }
        return null;
    }

    protected function _getSession()
    {
        return \Craft::$app->session->get($this->_getSessionName());
    }

    protected function _setSession($value)
    {
        \Craft::$app->session->set($this->_getSessionName(),$value);
    }

    /**
     * @return string
     */
    protected function _getSessionName(): string
    {
        return self::ROUTE_SESSION_NAME . md5($this->_getUrl());
    }

    protected function _getUrl(): string
    {
        return \Craft::$app->getRequest()->url;
    }

}