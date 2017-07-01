<?php

namespace choate\xsexpress\controllers;

use choate\xsexpress\XSExpress;
use yii\console\Controller;
use Yii;

class ExpressController extends Controller
{
    public $serviceName = 'XSExpress';

    public $lockName = 'xsexpress';

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['serviceName', 'lockName'] // global for all actions
        );
    }

    public function actionRecycle($sleep = 5) {
        /* @var XSExpress $service */
        /* @var \yii\mutex\Mutex $mutex */
        $service = Yii::$app->get($this->serviceName);
        $mutex = Yii::$app->get('mutex');
        $lockName = $this->lockName;
        if (!$mutex->acquire($lockName)) {
            return ;
        }
        try {
            while (true) {
                $service->recycle();
                $service->retry();

                sleep($sleep);
            }
        } catch (\Exception $e) {
            $this->stderr($e->getMessage());
            $this->stderr($e->getTraceAsString());
            Yii::error($e->getMessage());
        } finally {
            $mutex->release($lockName);
        }
    }
}