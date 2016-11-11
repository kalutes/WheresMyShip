<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

class Shipments extends UFModel {

    protected static $_table_id = "shipments";

    public function trackingNumbers(){
        $link_table = Database::getSchemaTable('shipments')->trackingNumber;
        return $link_table;
    }

}
