<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

class Shipments extends UFModel {

    protected static $_table_id = "shipments";

    public function __construct($properties = []) {
        parent::__construct($properties);
    }



}
