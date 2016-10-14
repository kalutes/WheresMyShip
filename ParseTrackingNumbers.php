<?php
    $dir = new DirectoryIterator(__DIR__."/messages");
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && is_file(__DIR__."/messages/".$fileinfo->getFilename())) {

            parseTrackingNumber(__DIR__."/messages/".$fileinfo->getFilename());
        }
    }


    function parseTrackingNumber($fileName)
    {
        $text = file_get_contents($fileName)
        or die("Unable to get contents of HTML File\n");

        //UPS Case
        $regex = '/(1Z|1z)([a-zA-z0-9]{16})/';
        if(preg_match_all($regex,$text,$matches)){
            $UPSTrackingNumbers= getValidUPSArray($matches[0]);
            print_r($UPSTrackingNumbers);
        }

        //FedEx case
        $regex = '/(?![^0-9])[0-9]{15}(?=[^0-9])/';
        if(preg_match_all($regex,$text,$matches)){
            $FedExTrackingNumbers= getValidFedExArray($matches[0]);
            print_r($FedExTrackingNumbers);
        }
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
            if(!in_array($element,$uniqueArray) && checkUPSDigit($element))array_push($uniqueArray,$element);
        }
        return $uniqueArray;
    }
    function getValidFedExArray($array){
        $uniqueArray=array();
        foreach($array as $element){
            if(!in_array($element,$uniqueArray) && checkFedExDigit($element))array_push($uniqueArray,$element);
        }
        return $uniqueArray;
    }


?>
