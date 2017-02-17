<?php

namespace weareenvoy\passwordroutes\models;


use craft\base\Model;
use craft\validators\SiteIdValidator;
use weareenvoy\passwordroutes\records\RouteRecord;
use craft\validators\UniqueValidator;
class Route extends Model
{
    public $id;
    public $uri;
    public $username;
    public $password;
    public $uid;


    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['uri'], UniqueValidator::class, 'targetClass' => RouteRecord::class],
            [['uri','username'], 'required'],
            [['password'], 'required', 'when' => function($model){
                return $model->id === null;
            }]
        ];
    }

}