<?php

namespace Creditable;


class CreditableResponse {

    private $paid = false;
    private $uid = "";

    function __construct($data) {
        $this->paid = $data['paid'];
        $this->uid = $data['uid'];
        $this->expiresAt = $data['expires_at'];
    }

    public function isPaid() {
        return $this->paid;
    }

    public function getUid() {
        return $this->uid;
    }
}