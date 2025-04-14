<?php

namespace Devworx\Utility;

class FileUtility {
  
  const MIME_TYPES = [
    'pdf' => 'application/pdf',
    'txt' => 'text/plain',
    'html' => 'text/html',
    'htm' => 'text/html',
    'js' => 'text/javascript',
    'json' => 'application/json',
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'jpeg' => 'image/jpeg',
    'bmp' => 'image/bitmap'
  ];
  
  const IGNORE = [
    '.',
    '..',
    '.htaccess',
    '.htpasswd'
  ];
  
  public static function getFiles(string $path,string $ext='',bool $absolute=true): array {
    $result = [];
    if( is_dir($path) ){
      $files = scandir($path);
      foreach( $files as $i => $file ){
        if( in_array($file,self::IGNORE) )
          continue;
        
        $fullPath = "{$path}/{$file}";
        if( empty($ext) ){
          $result []= $absolute ? $fullPath : $file;
          continue;
        }
        
        $fileExt = strtolower(pathinfo($fullPath,PATHINFO_EXTENSION));
        if( $ext == $fileExt ){
          $result []= $absolute ? $fullPath : $file;
        }
      }
    }
    return $result;
  }
  
  public static function upload(string $source,string $target,bool $overwrite=true): int {
    $result = 0;
    if( array_key_exists($source,$_FILES) ){
      $files = $_FILES[$source];
      
      $count = count($files['name']);
      for($i=0;$i<$count;$i++){
        $name = basename($files['name'][$i]);
        $tmpName = $files['tmp_name'][$i];
        $size = $files['size'][$i];
        
        $targetFile = "{$target}/{$name}";
        
        if( $overwrite || !file_exists($targetFile) ){
          if( move_uploaded_file($tmpName,$targetFile) )
            $result++;
        }
      }
    }
    return $result;
  }
  
  public static function download(string $fileName, string $downloadFileName){
    $ext = strtolower( pathinfo($fileName,PATHINFO_EXTENSION) );
    $cType = ArrayUtility::key(self::MIME_TYPES,$ext,'text/plain');
    header('Content-Type: ' . $cType);
    
    $downloadFileName = "\"{$downloadFileName}\"";
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $downloadFileName); 
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fileName));
    ob_clean();

    readfile($fileName);
  }
  
  public static function view(string $fileName){
    $ext = strtolower( pathinfo($fileName,PATHINFO_EXTENSION) );
    $cType = ArrayUtility::key(self::MIME_TYPES,$ext,'text/plain');
    header('Content-Type: ' . $cType);
    header('Content-Length: ' . filesize($fileName));
    ob_clean();

    readfile($fileName);
  }
  
  public static function findAll(string $path,bool $filesOnly=false): ?array {
    $files = null;
    if( is_dir($path) ){
      $files = array_filter(scandir($path),fn($row) => !in_array($row,self::IGNORE));
      $files = array_map(fn($row) => "{$path}/{$row}",$files);
      if( $filesOnly )
        return array_filter($files,fn($row) => !is_dir($row));
    }
    return $files;
  }
  
  public static function unlinkAll(string $path): ?array {
    $files = self::findAll($path);
    if( is_null($files) ) return $files;
    $files = array_map(fn($row) => "{$path}/{$row}",scandir($path));
    return array_filter($files,fn($row) => is_file($row) && unlink($row));
  }
}

?>