<?php

namespace choate\xsexpress\controllers;

use choate\xsexpress\XSExpress;
use yii\console\Controller;
use Yii;

class ExpressController extends Controller
{
    public $serviceName = 'XSExpress';

    public function actionRecycle($sleep = 5) {
        /* @var XSExpress $service */
        $service = Yii::$app->get($this->serviceName);
        while (true) {
            $service->recycle();
            $service->retry();

            sleep($sleep);
        }
    }
}