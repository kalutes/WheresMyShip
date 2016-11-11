<?php
    /*$servername = "localhost";
    $username = "userfrosting_adm";
    $password = "wheresmyship";
    $dbname = "userfrosting";
$id = 1; //must be changed to generic user id
$trackingNumber;
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username. $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $conn->prepare("INSERT INTO shipments (userid, trackingNumber) VALUES (:userid, :trackingNumber)");
		$stmt->bindParam(':userid', $id);
		$stmt->bindParam(':trackingNumber', $trackingNumber);*/

    $dir = new DirectoryIterator(__DIR__."/messages");
    $trackingNums=array();
    // $upsCount = 0;
    // $fedexCount = 0;
    $trackingNums['ups']=array();
    $trackingNums['fedex']=array();
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && is_file(__DIR__."/messages/".$fileinfo->getFilename())) {
            $file = __DIR__."/messages/".$fileinfo->getFilename();
            //search each file for UPS tracking numbers
            $arr = parseTrackingNumber($file,'ups');
            foreach($arr as $e){
                printf($e."\n");

	/*	$trackingNumber = $e;
		$stmt->execute();*/

		array_push($trackingNums['ups'],$e);
                $upsCount++;
            }

            //search for FedEx tracking numbers
            $arr = parseTrackingNumber($file,'fedex');
            foreach($arr as $e){
                printf($e."\n");
                array_push($trackingNums['fedex'],$e);
                $fedexCount++;
            }
        }
    }
print_r($trackingNums);




    function parseTrackingNumber($fileName, $shipper)
    {
        $shipperCaps = strtoupper($shipper);
        $trackingNumbers= array();
        $text = file_get_contents($fileName)
        or die("Unable to get contents of HTML File\n");
        $regex=NULL;
        switch($shipperCaps){
            case 'UPS':
                //UPS Case
                $regex = '/[^a-zA-Z0-9](1Z|1z)([a-zA-z0-9]{16})/';
                if(preg_match_all($regex,$text,$matches)){
                    //var_dump($matches[0]);
                    $trackingNumbers= getValidUPSArray($matches[0]);

                    // print_r($trackingNumbers);
                }
            else $trackNumbers=NULL;
            break;
            case 'FEDEX':
                // FedEx case
                $regex = '/[^a-zA-Z0-9][0-9][0-9]{14}(?=[^0-9])/';
                if(preg_match_all($regex,$text,$matches)){
                    $trackingNumbers= getValidFedExArray($matches[0]);
                    // print_r($trackingNumbers);
                }
                else $trackNumbers=NULL;
                break;
        }
        // printf($shipper);
        print_r($trackingNumbers);
        return $trackingNumbers;
}


    function checkUPSDigit($trackNumString){
        //removes 1z
        $nums = substr($trackNumString,2);
        $length = strlen($nums);
        $count = 2;

        for($i=0;$i<$length;$i++){

            if($nums[$i] >= 'A' && $nums[$i] <= 'Z'){
                    //if uppercase character found
                    //change character to numeric equivalent using chart
                    //A=2... H=9, I=0, J=1...
                    $nums[$i]=2+(ord($nums[$i])-ord('A'))%10;
            }
        }
        $evenSum=0;
        $oddSum=0;
        for($i=0;$i<$length;$i++)
        {
            if($i%2==0){
                $oddSum+=(int)$nums[$i];
            }
            else{
                $evenSum+=(int)$nums[$i];
            }
        }
        $evenSum*=2;

        $remainder = ($evenSum+$oddSum)%10;
        if($remainder == intval($nums[strlen($nums)-1]))return true;
        return false;

    }
    function checkFedExDigit($trackNumString){
        return true;
    }

    function getValidUPSArray($array){
        $uniqueArray=array();
        foreach($array as $element){
            $element = substr($element,1);
            if(!in_array($element,$uniqueArray) && checkUPSDigit($element))array_push($uniqueArray,$element);
        }
        return $uniqueArray;
    }
    function getValidFedExArray($array){
        $uniqueArray=array();
        foreach($array as $element){
            $element = substr($element,1);
            if(!in_array($element,$uniqueArray) && checkFedExDigit($element))array_push($uniqueArray,$element);
        }
        return $uniqueArray;
    }


?>
