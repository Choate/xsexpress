<?php

namespace choate\xsexpress\models;


class Goods
{
    private $message;

    private $uuid;

    private $createdAt;

    /**
     * @return mixed
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function __construct($uuid, $message, $createdAt) {
        $this->setMessage($message);
        $this->setUuid($uuid);
        $this->setCreatedAt($createdAt);
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }


}