<?php

namespace choate\xsexpress;


use choate\xsexpress\models\Goods;
use choate\xsexpress\models\Producer;
use Ramsey\Uuid\Uuid;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Json;

class XSExpress extends Component
{
    /**
     * 消息队列组件，消费者和生产者需要统一配置
     *
     * @var array|Object|string
     */
    private $httpsqs;

    /**
     * 重发消息时间，主要用于消息重发守护进程
     *
     * @var int
     */
    private $retryTime;

    /**
     * 确认消息队列KEY，消费者和生产者需要统一配置
     *
     * @var string
     */
    private $signatureKey;

    /**
     * 消息队列数据库配置，生产者需要配置
     *
     * @var array|Object|string
     */
    private $db;

    public function init() {
        parent::init();
        $this->httpsqs = Instance::ensure($this->httpsqs);;
        $this->db = Instance::ensure($this->db);
        if (!is_object($this->getHttpsqs())) {
            throw new InvalidConfigException('无效的HTTPSQS配置');
        }
        if ($this->getDb() instanceof Connection) {
            throw new InvalidConfigException('无效的DB配置');
        }
        Producer::setDb($this->db);
    }

    /**
     * @return mixed
     */
    public function getHttpsqs() {
        return $this->httpsqs;
    }

    /**
     * @param $httpsqs
     */
    public function setHttpsqs($httpsqs) {
        $this->httpsqs = $httpsqs;
    }

    /**
     * @return mixed
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * @param mixed $db
     */
    public function setDb($db) {
        $this->db = $db;
    }

    /**
     * @return mixed
     */
    public function getRetryTime() {
        if (is_null($this->retryTime)) {
            $this->retryTime = 60;
        }

        return $this->retryTime;
    }

    /**
     * @param mixed $retryTime
     */
    public function setRetryTime($retryTime) {
        $this->retryTime = $retryTime;
    }

    /**
     * @return mixed
     */
    public function getSignatureKey() {
        if (is_null($this->signatureKey)) {
            $this->signatureKey = 'xs_express_signature';
        }

        return $this->signatureKey;
    }

    /**
     * @param mixed $signatureKey
     */
    public function setSignatureKey($signatureKey) {
        $this->signatureKey = $signatureKey;
    }

    /**
     * 收货
     *
     * @param string $channel 收货人频道
     *
     * @return bool|\choate\xsexpress\models\Goods
     * @throws \yii\base\InvalidParamException
     */
    public function receipt($channel) {
        $dataJson = $this->getHttpsqs()->get($channel);
        if ($dataJson) {
            $data = Json::decode($dataJson);

            return new Goods($data['uuid'], $data['message'], $data['created_at']);
        }

        return false;
    }

    /**
     * 销毁已经签名的快递单
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function recycle() {
        $items = $this->getSignatureItems();
        if (!empty($items)) {
            Producer::deleteAll(['uuid' => $items]);
        }

        return true;
    }

    /**
     * 重新联系收货人
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function retry() {
        $items = Producer::find()->where(['<=', 'created_at', time() - $this->getRetryTime()])->all();
        foreach ($items as $item) {
            $data = ['uuid' => $item->uuid, 'message' => Json::decode($item->message), 'created_at' => $item->created_at];
            $this->getHttpsqs()->put($item->topic, Json::encode($data));
        }
    }

    /**
     * 发送快递
     *
     * @param string $topic 快递类型
     * @param array $goods 商品
     *
     * @return mixed
     * @throws \Exception
     * @throws \yii\base\InvalidParamException
     */
    public function ship($topic, array $goods) {
        $producer = new Producer();
        $time = time();
        $producer->setAttributes(['uuid' => Uuid::uuid4(), 'message' => Json::encode($goods), 'topic' => $topic, 'created_at' => $time], false);
        $producer->insert(false);
        $data = [
            'uuid'    => $producer->uuid,
            'created_at' => $time,
            'message' => $goods,
        ];

        return $this->getHttpsqs()->put($topic, Json::encode($data));
    }

    /**
     * 确认签名
     *
     * @param \choate\xsexpress\models\Goods $goods
     *
     * @return mixed
     */
    public function signature(Goods $goods) {
        return $this->getHttpsqs()->put($this->getSignatureKey(), $goods->getUuid());
    }

    /**
     * 获取签名列表
     *
     * @return array
     */
    private function getSignatureItems() {
        $result = [];
        while ($data = $this->getHttpsqs()->get($this->getSignatureKey())) {
            $result[] = $data;
        }

        return $result;
    }
}