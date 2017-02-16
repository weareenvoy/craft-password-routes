<?php
namespace weareenvoy\passwordroutes\services;


use craft\base\Component;
use weareenvoy\passwordroutes\events\RouteEvent;
use weareenvoy\passwordroutes\errors\RouteNotFoundException;
use weareenvoy\passwordroutes\models\Route;
use weareenvoy\passwordroutes\records\RouteRecord;

class Routes extends Component
{
    const EVENT_BEFORE_SAVE_ROUTE = 'beforeSavePwRoute';
    const EVENT_AFTER_SAVE_ROUTE = 'afterSavePwRoute';
    const EVENT_BEFORE_DELETE_ROUTE = 'beforeDeletePwRoute';
    const EVENT_AFTER_DELETE_ROUTE = 'afterSavePwRoute';

    protected $_allRoutesById;
    protected $_fetchedAllRoutes = false;


    /**
     * @param Route $route
     * @param bool $runValidation
     * @return bool
     * @throws RouteNotFoundException
     * @throws \Exception
     */
    public function saveRoute(Route $route, bool $runValidation = true) : bool
    {
        if ($runValidation && !$route->validate()) {
            \Craft::info('Route not saved due to validation error.', __METHOD__);

            return false;
        }

        $isNewRoute = !$route->id;

        // Fire a 'beforeSavePwRoute' event
        $this->trigger(self::EVENT_BEFORE_SAVE_ROUTE, new RouteEvent([
            'route' => $route,
            'isNew' => $isNewRoute,
        ]));

        if (!$isNewRoute) {
            $routeRecord = RouteRecord::findOne($route->id);

            if (!$routeRecord) {
                throw new RouteNotFoundException("No routes exists with the ID '{$route->id}'");
            }
        } else {
            $routeRecord = new RouteRecord();
        }

        //Populate Record
        $routeRecord->uriPattern = $route->uriPattern;
        $routeRecord->username = $route->username;
        if ((!$isNewRoute && !empty($route->password)) || $isNewRoute) {
            $routeRecord->password = md5($route->password);
        }

        //Save Record
        $transaction = \Craft::$app->getDb()->beginTransaction();
        try{
            $routeRecord->save(false);

            if(!$route->id){
                $route->id = $routeRecord->id;
            }

            $transaction->commit();
        } catch (\Exception $e){
            $transaction->rollBack();
            throw $e;
        }

        // Fire an 'afterSavePwRoute' event
        $this->trigger(self::EVENT_AFTER_SAVE_ROUTE, new RouteEvent([
            'route' => $route,
            'isNew' => $isNewRoute,
        ]));

        return true;
    }

    /**
     * @param int $routeId
     * @return bool
     * @throws \Exception
     */
    public function deleteRouteById(int $routeId) : bool
    {
        if (!$routeId) {
            return false;
        }

        $route = $this->getRouteById($routeId);

        if (!$route) {
            return false;
        }

        // Fire a 'beforeDeletePwRoute' event
        $this->trigger(self::EVENT_BEFORE_DELETE_ROUTE, new RouteEvent([
            'route' => $route,
        ]));

        $transaction = \Craft::$app->getDb()->beginTransaction();
        try {
            RouteRecord::findOne(['id' => $route->id])->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Fire an 'afterSavePwRoute' event
        $this->trigger(self::EVENT_AFTER_DELETE_ROUTE, new RouteEvent([
            'route' => $route,
        ]));

        return true;
    }

    /**
     * @param int $routeId
     * @return null|Route
     */
    public function getRouteById(int $routeId)
    {
        if ($this->_allRoutesById !== null && array_key_exists($routeId, $this->_allRoutesById)) {
            return $this->_allRoutesById[$routeId];
        }

        if ($this->_fetchedAllRoutes) {
            return null;
        }

        if (($routeRecord = RouteRecord::findOne($routeId)) === null) {
            return $this->_allRoutesById[$routeId] = null;
        }

        return $this->_allRoutesById[$routeId] = new Route($routeRecord->toArray([
            'id',
            'uriPattern',
            'username',
            'password',
            'uid'
        ]));
    }

    /**
     * @return array
     */
    public function getAllRoutes() : array
    {
        if (!$this->_fetchedAllRoutes) {
            $this->_allRoutesById = RouteRecord::find()
                ->indexBy('id')
                ->all();

            foreach ($this->_allRoutesById as $key => $value) {
                $this->_allRoutesById[$key] = new Route($value->toArray([
                    'id',
                    'uriPattern',
                    'username',
                    'password',
                    'uid'
                ]));
            }

            $this->_fetchedAllRoutes = true;
        }

        return array_values($this->_allRoutesById);
    }

}