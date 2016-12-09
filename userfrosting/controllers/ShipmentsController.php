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

    public function trackingNumbers($userid){
         $trackingNumbers = Shipments::where('userid',$userid)->get();
         $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/shipments.json");
           $this->_app->jsValidator->setSchema($schema);

         $this->_app->render('shipments/shipments.twig',[
            "trackingNumbers" => $trackingNumbers,
            "validators" => $this->_app->jsValidator->rules()
        ]);
    }
    public function createMessage($trackingNumber,$userid){
        $myfile = fopen("/home/kalutes/WheresMyShip/scripts/messages/".$userid."/test.html", "w");
        fwrite($myfile,$trackingNUmber);
    }
    public function postTrackingNumber($userid){
        $post = $this->_app->request->post();
        // $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/shipments.json");
        //
        //   // Get the alert message stream
        //   $ms = $this->_app->alerts;
        //
        //   // Set up Fortress to process the request
        //   $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);
        //
        //   // Sanitize
        //   $rf->sanitize();
        //
        //   // Validate, and halt on validation errors.
        //   if (!$rf->validate()) {
        //       $this->_app->halt(400);
        //   }
        //
        //   // Get the filtered data
        //   $data = $rf->data();
          $this->createMessage($post['trackingNumber'],$userid);
        //   $shipment = new Shipments;
        //   $shipment->userid = $userid;
        //   $shipment->trackingNumber = $post['trackingNumber'];
          //
        //   $shipment->save();
    }

}
