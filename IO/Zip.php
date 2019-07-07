<?php

if (is_readable('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    require_once('IO/Bit.php');
}

// http://www.pkware.com/documents/casestudies/APPNOTE.TXT
// MS-Dos Date Time http://www.vsft.com/hal/dostime.htm

class IO_Zip {
    var $chunkList = null;
    static function signatureName($sig) {
        static $signatureTable = [
            "PK\x03\x04" => "Local file header",
            "PK\x06\x08" => "Archive extra data record",
            "PK\x01\x02" => "Central directory structure",
            "PK\x05\x05" => "Digital signature",
            "PK\x05\x06" => "End of central directory record",
            "PK\x06\x06" => "Zip64 end of central directory record",
            "PK\x06\x07" => "Zip64 end of central directory locator",
        ];
        if (isset($signatureTable[$sig])) {
            return $signatureTable[$sig];
        }
        return "Unknonw Signature";
    }
    function parse($zipdata, $offset = 0) {
        $reader = new IO_Bit();
        $reader->input($zipdata);
        $reader->setOffset($offset, 0);
        /*
         * zip header info
         */
        $this->chunkList = array();
        $done = false;
        while (true) {
            $signature = $reader->getData(4);
            $chunk = ['Signature' => $signature];
            switch ($signature) {
              case "PK\x03\x04": // A. Local file header
                $chunk['VersionNeeded'] = $reader->getUI16LE();
                $chunk['GeneralFlag'] = $reader->getUI16LE();
                $chunk['CompressionMethod'] = $reader->getUI16LE();
                $chunk['LastModTime'] = $this->getDosTime($reader);
                $chunk['LastModDate'] = $this->getDosDate($reader);
                $chunk['CRC32'] = $reader->getUI32LE();
                $chunk['CompressSize'] = $reader->getUI32LE();
                $chunk['UncompressSize'] = $reader->getUI32LE();
                $chunk['FileNameLen'] = $reader->getUI16LE();
                $chunk['ExtraLen'] = $reader->getUI16LE();
                if ($chunk['FileNameLen']) {
                    $chunk['FileName'] = $reader->getData($chunk['FileNameLen']);
                }
                if ($chunk['ExtraLen']) {
                    $chunk['Extra'] = $reader->getData($chunk['ExtraLen']);
                }
                if ($chunk['CompressSize']) {
                    // B. File data
                    // $this->chunkList []= $reader->getData($chunk['CompressSize']);
                    $reader->incrementOffset($chunk['CompressSize'], 0);
                }
                break;
              case "PK\x06\x08": // E. Archive extra data record
                  $chunk['ExtraFieldLength'] = $reader->getUI32LE();
                  if ($chunk['ExtraFieldLength']) {
                    $chunk['ExtraFieldData'] = $reader->getData($chunk['ExtraFieldLength']);
                }
                break;
              case "PK\x01\x02": // F. Central directory structure
                $chunk['VersionMadeBy'] = $reader->getUI16LE();
                $chunk['VersionNeeded'] = $reader->getUI16LE();
                $chunk['GeneralFlag'] = $reader->getUI16LE();
                $chunk['CompressionMethod'] = $reader->getUI16LE();
                $chunk['LastModTime'] = $this->getDosTime($reader);
                $chunk['LastModDate'] = $this->getDosDate($reader);
                $chunk['CRC32'] = $reader->getUI32LE();
                $chunk['CompressSize'] = $reader->getUI32LE();
                $chunk['UncompressSize'] = $reader->getUI32LE();
                $chunk['FileNameLen'] = $reader->getUI16LE();
                $chunk['ExtraLen'] = $reader->getUI16LE();
                $chunk['FileCommentLen'] = $reader->getUI16LE();
                $chunk['DiskNumberStart'] = $reader->getUI16LE();
                $chunk['InternalFileAttr'] = $reader->getUI16LE();
                $chunk['ExternalFileAttr'] = $reader->getUI32LE();
                $chunk['RelativeOffsetOfLocalHeader'] = $reader->getUI32LE();
                if ($chunk['FileNameLen']) {
                    $chunk['FileName'] = $reader->getData($chunk['FileNameLen']);
                }
                if ($chunk['ExtraLen']) {
                    $chunk['Extra'] = $reader->getData($chunk['ExtraLen']);
                }
                if ($chunk['FileCommentLen']) {
                    $chunk['FileComment'] = $reader->getData($chunk['FileCommentLen']);
                }
                if ($chunk['CompressSize']) {
                    // nothing to do
                }
                break;
              case "PK\x05\x05": // Digital signature
                $chunk['SizeOfData'] = $reader->getUI16LE();
                if ($chunk['SizeOfData']) {
                    $chunk['SignatureData'] = $reader->getData();
                }
                break;
            case "PK\x05\x06": // I. End of central directory record
                $chunk['NumOfDisk'] = $reader->getUI16LE();
                $chunk['NumOfDiskWithStartCentralDirectory'] = $reader->getUI16LE();
                $chunk['TotalNumOfEntriesOnDisk'] = $reader->getUI16LE();
                $chunk['TotalNumOfEntries'] = $reader->getUI16LE();
                $chunk['SizeOfCentralDirectory'] = $reader->getUI32LE();
                $chunk['StartOffsetCentralDirectory'] = $reader->getUI32LE();
                $chunk['ZIPCommentLen'] = $reader->getUI16LE();
                if ($chunk['ZIPCommentLen']) {
                    $chunk['ZIPComment'] = $reader->getData($chunk['ZipCommentLen']);
                }
                $done = true;
                break ;
                
            case "PK\x06\x06": // G. Zip64 end of central directory record
            case "PK\x06\x07": // H. Zip64 end of central directory locator
            default:
                echo "Unknown: ".bin2hex($signature)." :$signature\n";
                $done = true;
                break ;
            }
            $this->chunkList []= $chunk;
            if ($done) {
                break;
            }
        }
    }
    function getDosTime(&$reader) {
        $dostime = $reader->getUI16LE();
        $hour = $dostime >> (16 - 5);
        $minute = ($dostime >> (16 - 5 - 6)) & 0x3f;
        $second = ($dostime & 0x1f) * 2;
        return array('Hour' => $hour, 'Minute' => $minute, 'Second' => $second);
    }
    function getDosDate(&$reader) {
        $dosdate = $reader->getUI16LE();
        $year  = ($dosdate >> (16 - 7)) + 1980;
        $month = ($dosdate >> (16 - 7 - 4)) & 0x0f;
        $day   =  $dosdate & 0x1f;
        return array('Year' => $year, 'Month' => $month, 'Day' => $day);
    }
    function dump($opt = array()) {
        foreach ($this->chunkList as $chunk) {
            $sig = $chunk["Signature"];
            echo self::signatureName($sig).PHP_EOL;
            foreach ($chunk as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                echo "  $key: $value".PHP_EOL;
            }
        }
    }
}
