<?php

namespace UserFrosting;

/**
 * ShipmentsController Class
 *
 * Controller class for /install/* URLs.  Handles activities for installing UserFrosting.  Not needed after installation is complete.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class ShipmentsController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
    }

    public function trackingNumbers(){
        //  $trackingNumbers = Shipments::get();
         $this->_app->render('shipments/shipments.twig',[
            // "trackingNumber" => $trackingNumber

        ]);
    }



}
