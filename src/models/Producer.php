<?php

namespace choate\xsexpress\models;

use yii\db\ActiveRecord;

class Producer extends ActiveRecord
{
    public static function tableName() {
        return '{{%producer}}';
    }

    private static $db;

    public static function setDb($db) {
        static::$db = $db;
    }

    public static function getDb() {
        if (is_null(static::$db)) {
            static::$db = parent::getDb();
        }

        return static::$db;
    }

}