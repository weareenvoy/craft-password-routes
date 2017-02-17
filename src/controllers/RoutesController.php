<?php
namespace weareenvoy\passwordroutes\controllers;


use craft\web\Controller;
use craft\web\View;
use weareenvoy\passwordroutes\models\Route;
use weareenvoy\passwordroutes\PasswordRoutes;
use weareenvoy\passwordroutes\records\RouteRecord;
use yii\web\NotFoundHttpException;

class RoutesController extends Controller
{
    protected $allowAnonymous = ['login', 'login-post'];

    public function actionIndex()
    {
        $this->requireAdmin();

        $routes = PasswordRoutes::getInstance()->routes->getAllRoutes();

        $variables['routes'] = $routes;
        return $this->renderTemplate('passwordroutes/index', $variables);
    }

    public function actionEditRoute(int $routeId = null, Route $route = null)
    {
        $this->requireAdmin();

        if($routeId !== null){
            if($route === null){
                $route = PasswordRoutes::getInstance()->routes->getRouteById($routeId);

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

        return $this->renderTemplate('passwordroutes/_edit',$variables);
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

        if(!PasswordRoutes::getInstance()->routes->saveRoute($route)){
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
        return $this->asJson(['success' => PasswordRoutes::getInstance()->routes->deleteRouteById($routeId)]);
    }

    public function actionLogin()
    {
        $routeId = \Craft::$app->getRequest()->getQueryParam('routeId');
        $redirectTo = \Craft::$app->getRequest()->getQueryParam('redirectTo');

        $variables['routeId'] = $routeId;
        $variables['redirectTo'] = $redirectTo ? $redirectTo : '';
        // Hack to load plugin template in front facing side of the site.
        \Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = $this->renderTemplate('passwordroutes/login',$variables);
        \Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);

        return $html;
    }

    public function actionLoginPost()
    {
        $this->requirePostRequest();

        $passwordRoutes = PasswordRoutes::getInstance();
        $routeId = \Craft::$app->getRequest()->getRequiredBodyParam('routeId');
        $username = \Craft::$app->getRequest()->getRequiredBodyParam('username');
        $password = \Craft::$app->getRequest()->getRequiredBodyParam('password');
        $redirect = \Craft::$app->getRequest()->getRequiredBodyParam('redirect');

        //Check if its a successful login
        if($passwordRoutes->authentication->login($username,$password,$routeId)){
            return $this->redirectToPostedUrl();
        }else{
            \Craft::$app->getSession()->setError(\Craft::t('app', 'Incorrect Username and Password.'));

            $variables['routeId'] = $routeId;
            $variables['redirectTo'] = $redirect;
            \Craft::$app->urlManager->setRouteParams($variables);
        }
    }
}