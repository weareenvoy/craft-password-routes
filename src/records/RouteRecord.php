<?php


namespace weareenvoy\passwordroutes\records;


use craft\db\ActiveRecord;
use craft\records\Site;
use craft\validators\SiteIdValidator;
use yii\db\ActiveQueryInterface;

class RouteRecord extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return "{{%pw_routes}}";
    }
}