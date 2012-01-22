<?php

require_once('IO/Bit.php');

class IO_Zip {
    var $headerList = null;
    function parse($zipdata, $offset = 0) {
                $reader = new IO_Bit();
        $reader->input($zipdata);
        $reader->setOffset($offset, 0);
        /*
         * zip header info
         */
        $this->chunkList = array();
        while (true) {
            $signature = $reader->getData(4);
            switch ($signature) {
	      case "PK\x03\x04":
                $header = array();
                $header['Signature'] = $signature;
                $header['VersionNeeded'] = $reader->getUI16LE();
                $header['GeneralFlag'] = $reader->getUI16LE();
                $header['CompressionMethod'] = $reader->getUI16LE();
                $header['LastModDate'] = $this->getDosDate($reader);
                $header['LastModTime'] = $this->getDosTime($reader);
                $header['CRC32'] = $reader->getUI32LE();
                $header['CompSize'] = $reader->getUI32LE();
                $header['UnCompSize'] = $reader->getUI32LE();
                $header['FNameLen'] = $reader->getUI16LE();
                $header['ExtrLen'] = $reader->getUI16LE();
                if ($header['FNameLen']) {
                    $header['FName'] = $reader->getData($header['FNameLen']);
                }
                if ($header['ExtrLen']) {
                    $header['Extra'] = $reader->getData($header['ExtrLen']);
                }
                $this->chunkList []= $header;
                if ($header['CompSize']) {
                    $this->chunkList []= $reader->getData($header['CompSize']);
                }
                var_dump($header);
                break;
//	      case "PK\x01\x02":
//                break;
              default:
//                echo "OK: ".bin2hex($signature)."\n";
                echo "OK: ".bin2hex($signature)." :$signature\n";
                break 2;
            }
        }
    }
    function getDosDate($reader) {
        $year  = $reader->getUIBits(7) + 1980;
        $month = $reader->getUIBits(4);
        $day   = $reader->getUIBits(5);
        return array('Year' => $year, 'Month' => $month, 'Day' => $day);
    }
    function getDosTime($reader) {
        $hour   = $reader->getUIBits(5);
        $minute = $reader->getUIBits(6);
        $second = $reader->getUIBits(5) * 2;
        return array('Hour' => $hour, 'Minute' => $minute, 'Second' => $second);
    }
}

$zipdata = file_get_contents($argv[1]);

$zip = new IO_Zip();
$zip->parse($zipdata);

