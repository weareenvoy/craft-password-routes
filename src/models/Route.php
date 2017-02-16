<?php

namespace envoy\passwordprotect\models;


use craft\base\Model;
use craft\validators\SiteIdValidator;
use envoy\passwordprotect\records\RouteRecord;
use craft\validators\UniqueValidator;
class Route extends Model
{
    public $id;
    public $siteId;
    public $uriPattern;
    public $username;
    public $password;
    public $uid;


    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['siteId'], SiteIdValidator::class],
            [['uriPattern'], UniqueValidator::class, 'targetClass' => RouteRecord::class],
            [['uriPattern','username'], 'required'],
            [['password'], 'required', 'when' => function($model){
                return $model->id === null;
            }]
        ];
    }

}