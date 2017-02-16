<?php
namespace envoy\passwordprotect\controllers;


use craft\web\Controller;
use envoy\passwordprotect\models\Route;
use envoy\passwordprotect\PasswordProtect;
use envoy\passwordprotect\records\RouteRecord;
use yii\web\NotFoundHttpException;

class RoutesController extends Controller
{
    protected $allowAnonymous = ['login'];

    public function actionIndex()
    {
        $this->requireAdmin();

        $routes = PasswordProtect::getInstance()->routes->getAllRoutes();

        $variables['routes'] = $routes;
        return $this->renderTemplate('passwordprotect/index', $variables);
    }

    public function actionEditRoute(int $routeId = null, Route $route = null)
    {
        $this->requireAdmin();

        if($routeId !== null){
            if($route === null){
                $route = PasswordProtect::getInstance()->routes->getRouteById($routeId);

                if(!$route){
                    throw new NotFoundHttpException('Route not found');
                }
                // Empty out password for updates
                $route->password = '';
            }

        }else{
            if($route === null){
                $route = new Route();
            }

        }

        $variables['route'] = $route;

        return $this->renderTemplate('passwordprotect/_edit',$variables);
    }

    public function actionSaveRoute()
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $route = new Route();
        $route->id = \Craft::$app->getRequest()->getBodyParam('routeId');
        $route->uriPattern = \Craft::$app->getRequest()->getBodyParam('uriPattern');
        $route->username = \Craft::$app->getRequest()->getBodyParam('username');
        $route->password = \Craft::$app->getRequest()->getBodyParam('password');

        if(!PasswordProtect::getInstance()->routes->saveRoute($route)){
            \Craft::$app->getSession()->setError(\Craft::t('app', 'Couldn’t save the route.'));

            // Send the route back to the template
            \Craft::$app->getUrlManager()->setRouteParams([
                'route' => $route
            ]);

            return null;
        }

        \Craft::$app->getSession()->setNotice(\Craft::t('app', 'Route saved.'));

        return $this->redirectToPostedUrl();
    }

    public function actionDeleteRoute()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireAdmin();

        $routeId = \Craft::$app->getRequest()->getRequiredBodyParam('id');

        Route::findOne(['id' => $routeId])->delete();

        return $this->asJson(['success' => true]);
    }

    public function actionLogin()
    {
        $routeId = \Craft::$app->getRequest()->getQueryParam('routeId');

        $variables['routeId'] = $routeId;

        return $this->renderTemplate('passwordprotect/login',$variables);
    }

}