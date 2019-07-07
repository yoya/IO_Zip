# Usage

```
% php sample/zipdump.php -f zbsm.zip  | less
Local file header #1
  Signature: PK^C^D
  VersionNeeded: 20
  GeneralFlag: 0
  CompressionMethod: 8
  LastModTime: 13,37,0
  LastModDate: 1982,10,8
  CRC32: 3897710843
  CompressSize: 30357
  UncompressSize: 21849182
  FileNameLen: 1
  ExtraLen: 0
  FileName: 0
Central directory structure #1
   (omit...)
Central directory structure #250
  Signature: PK^A^B
  VersionMadeBy: 20
  VersionNeeded: 20
  GeneralFlag: 0
  CompressionMethod: 8
  LastModTime: 13,37,0
  LastModDate: 1982,10,8
  CRC32: 391821905
  CompressSize: 21179
  UncompressSize: 21841249
  FileNameLen: 2
  ExtraLen: 0
  FileCommentLen: 0
  DiskNumberStart: 0
  InternalFileAttr: 0
  ExternalFileAttr: 0
  RelativeOffsetOfLocalHeader: 9177
  FileName: 5X
End of central directory record #1
  Signature: PK^E^F
  NumOfDisk: 0
  NumOfDiskWithStartCentralDirectory: 0
  TotalNumOfEntriesOnDisk: 250
  TotalNumOfEntries: 250
  SizeOfCentralDirectory: 11964
  StartOffsetCentralDirectory: 30388
  ZIPCommentLen: 0
```