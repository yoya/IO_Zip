<?php

require_once('IO/Bit.php');

// http://www.pkware.com/documents/casestudies/APPNOTE.TXT
// MS-Dos Date Time http://www.vsft.com/hal/dostime.htm

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
              case "PK\x03\x04": // A. Local file header
                $header = array();
                $header['Signature'] = $signature;
                $header['VersionNeeded'] = $reader->getUI16LE();
                $header['GeneralFlag'] = $reader->getUI16LE();
                $header['CompressionMethod'] = $reader->getUI16LE();
                $header['LastModTime'] = $this->getDosTime($reader);
                $header['LastModDate'] = $this->getDosDate($reader);
                $header['CRC32'] = $reader->getUI32LE();
                $header['CompressSize'] = $reader->getUI32LE();
                $header['UncompressSize'] = $reader->getUI32LE();
                $header['FileNameLen'] = $reader->getUI16LE();
                $header['ExtraLen'] = $reader->getUI16LE();
                if ($header['FileNameLen']) {
                    $header['FileName'] = $reader->getData($header['FileNameLen']);
                }
                if ($header['ExtraLen']) {
                    $header['Extra'] = $reader->getData($header['ExtraLen']);
                }
                $this->chunkList []= $header;
                if ($header['CompressSize']) {
                    // B. File data
                    $this->chunkList []= $reader->getData($header['CompressSize']);
                }
                var_dump($header);
                break;
              case "PK\x06\x08": // E. Archive extra data record
                $data = array();
                $data['Signature'] = $signature;
                $data['ExtraFieldLength'] = $reader->getUI32LE();
                if ($data['ExtraFieldLength']) {
                    $data['ExtraFieldData'] = $reader->getData($data['ExtraFieldLength']);
                }
                $this->chunkList []= $data;
                var_dump($data);
                break;
              case "PK\x01\x02": // F. Central directory structure
                $header = array();
                $header['Signature'] = $signature;
                $header['VersionMadeBy'] = $reader->getUI16LE();
                $header['VersionNeeded'] = $reader->getUI16LE();
                $header['GeneralFlag'] = $reader->getUI16LE();
                $header['CompressionMethod'] = $reader->getUI16LE();
                $header['LastModTime'] = $this->getDosTime($reader);
                $header['LastModDate'] = $this->getDosDate($reader);
                $header['CRC32'] = $reader->getUI32LE();
                $header['CompressSize'] = $reader->getUI32LE();
                $header['UncompressSize'] = $reader->getUI32LE();
                $header['FileNameLen'] = $reader->getUI16LE();
                $header['ExtraLen'] = $reader->getUI16LE();
                $header['FileCommentLen'] = $reader->getUI16LE();
                $header['DiskNumberStart'] = $reader->getUI16LE();
                $header['InternalFileAttr'] = $reader->getUI16LE();
                $header['ExternalFileAttr'] = $reader->getUI32LE();
                $header['RelativeOffsetOfLocalHeader'] = $reader->getUI32LE();
                if ($header['FileNameLen']) {
                    $header['FileName'] = $reader->getData($header['FileNameLen']);
                }
                if ($header['ExtraLen']) {
                    $header['Extra'] = $reader->getData($header['ExtraLen']);
                }
                if ($header['FileCommentLen']) {
                    $header['FileComment'] = $reader->getData($header['FileCommentLen']);
                }
                $this->chunkList []= $header;
                if ($header['CompressSize']) {
                    $this->chunkList []= $reader->getData($header['CompressSize']);
                }
                var_dump($header);
                break;
              case "PK\x05\x05": // Digital signature
                $header = array();
                $header['Signature'] = $signature;
                $header['SizeOfData'] = $reader->getUI16LE();
                if ($header['SizeOfData']) {
                    $header['SignatureData'] = $reader->getData();
                }
                break;
//            case "PK\x06\x06": // G. Zip64 end of central directory record
//            case "PK\x06\x07": // H. Zip64 end of central directory locator
//            case "PK\x05\x06": // I. End ofcentral directory record
              default:
                echo "Unknown".bin2hex($signature)." :$signature\n";
                break 2;
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
}

$zipdata = file_get_contents($argv[1]);

$zip = new IO_Zip();
$zip->parse($zipdata);

