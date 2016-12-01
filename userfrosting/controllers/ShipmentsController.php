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
    /*private function upsTrack($trackingNumber) {
        define('API_KEY', __DIR__.'/../../scripts/UPS_API/API_Key.txt');
        define('USER_ID', __DIR__.'/../../scripts/UPS_API/User_ID.txt');
        define('PASSWORD', __DIR__.'/../../scripts/UPS_API/Password.txt');
        $data ="<?xml version=\"1.0\"?>
        <AccessRequest xml:lang='en-US'>
        <AccessLicenseNumber>".file_get_contents(API_KEY)."</AccessLicenseNumber>
        <UserId>".file_get_contents(USER_ID)."</UserId>
        <Password>".file_get_contents(PASSWORD)."</Password>
        </AccessRequest>
        <?xml version=\"1.0\"?>
        <TrackRequest>
        <Request>
        <TransactionReference>
        <CustomerContext>
        <InternalKey>blah</InternalKey>
        </CustomerContext>
        <XpciVersion>1.0</XpciVersion>
        </TransactionReference>
        <RequestAction>Track</RequestAction>
        </Request>
        <TrackingNumber>$trackingNumber</TrackingNumber>
        </TrackRequest>";
        $ch = curl_init("https://www.ups.com/ups.app/xml/Track");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT, 60);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $result=curl_exec ($ch);
        // echo '<!-- '. $result. ' -->';
        $data = strstr($result, '<?');
        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $data, $vals, $index);
        xml_parser_free($xml_parser);
        $params = array();
        $level = array();
        foreach ($vals as $xml_elem) {
            if ($xml_elem['type'] == 'open') {
                if (array_key_exists('attributes',$xml_elem)) {
                    list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
                } else {
                    $level[$xml_elem['level']] = $xml_elem['tag'];
                }
            }
            if ($xml_elem['type'] == 'complete') {
                $start_level = 1;
                $php_stmt = '$params';
                while($start_level < $xml_elem['level']) {
                    $php_stmt .= '[$level['.$start_level.']]';
                    $start_level++;
                }
                $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
                eval($php_stmt);
            }
        }
        curl_close($ch);
        return $params;
    }
    private function getData($trackingNumber){
        include(__DIR__."/../../scripts/UPS_API/UPSTrack.php");
        $result = upsTrack($trackingNumber);
        return $result['TRACKRESPONSE']['SHIPMENT'];
    }
*/
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

    public function postTrackingNumber($userid){
        $post = $this->_app->request->post();
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/shipments.json");

          // Get the alert message stream
          $ms = $this->_app->alerts;

          // Set up Fortress to process the request
          $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

          // Sanitize
          $rf->sanitize();

          // Validate, and halt on validation errors.
          if (!$rf->validate()) {
              $this->_app->halt(400);
          }

          // Get the filtered data
          $data = $rf->data();
          $shipment = new Shipments;
          $shipment->userid = $userid;
          $shipment->trackingNumber = $post['trackingNumber'];
        //   $shipInfo=$this->getData($post['trackingNumber']);
        //   $shipment->shipDate= $shipInfo['PICKUPDATE'];
        //   $shipment->origin= $shipInfo['SHIPPER']['ADDRESS'];
        //   $shipment->destination=$shipInfo['SHIPTO']['ADDRESS'];
        //   $shipment->currentLocation=$shipInfo['PACKAGE']['ACTIVITY']['ACTIVITYLOCATION']['ADDRESS'];
        //   $ETA = $shipInfo['SCHEDULEDDELIVERYDATE'];
  // 		if ($this->ETA == 0) {
        //     $ETA = $shipmentInfo['PACKAGE']['RESCHEDULEDDELIVERYDATE'];
		// }
        //   $shipment->eta=$eta;
          $shipment->save();
    }
}
