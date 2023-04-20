<?php

namespace Creditable;


class CreditableResponse {

    private $paid = false;
    private $uid = "";

    function __construct($statusCode, $data) {
        $this->paid = $statusCode === 200;
        $this->uid = $data['uid'];
    }

    public function isPaid() {
        return $this->paid;
    }

    public function getUid() {
        return $this->uid;
    }
}