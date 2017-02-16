<?php
/**
 * Created by PhpStorm.
 * User: Envoy
 * Date: 2/15/17
 * Time: 7:16 PM
 */

namespace weareenvoy\passwordroutes\events;


use yii\base\Event;

class RouteEvent extends Event
{
    public $route;
    public $isNew = false;
}