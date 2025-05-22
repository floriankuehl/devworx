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
  
  /**
   * Retrieves a file list from a given path
   *
   * @param string $path The folder to search
   * @param string $ext The file extension for the files
   * @param bool $absolute Flag for returning the absolute file path
   * @return array
   */
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
  
  /**
   * Uploads files to a target directory
   *
   * @param string $source The field name in $_FILES
   * @param string $target The target directory
   * @param bool $overwrite Flag for overwriting existing files
   * @return int The amount of uploaded files
   */
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
  
  /**
   * Downloads a file for use in the browser
   *
   * @param string $fileName The original file name
   * @param string $downloadFileName The download file name
   * @return void
   */
  public static function download(string $fileName, string $downloadFileName): void {
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
  
  /**
   * Uploads a file
   *
   * @param string $tmpName The temporary file name
   * @param string $targetName The target file name
   * @return bool
   */
  public static function upload(string $tmpName,string $targetName,bool $checkFolder=false): bool {
	  if( $checkFolder ){
		  $dir = dirname($targetName);
		  if( !is_dir($dir) )
			  mkdir($dir,0x777,true);
	  }
	  return move_uploaded_file($tmpName, $targetName);
  }
  
  /**
   * Uploads an array of files and returns the successfull uploaded files
   *
   * @param string $tmpName The temporary file name
   * @param string $targetName The target file name
   * @return array
   */
  public static function uploadAll(array $files,string $targetFolder,bool $checkFolder=false): array {
	  $result = [];
	  if( $checkFolder ){
		  if( !is_dir($targetFolder) )
			  mkdir($targetFolder,0x777,true);
	  }
	  $targetFolder = rtrim($targetFolder,DIRECTORY_SEPARATOR);
	  foreach( $files['tmp_name'] as $i => $tmpName ){
		  $targetName = $targetFolder . DIRECTORY_SEPARATOR . $files['name'][$i];
		  if( self::upload($tmpName,$targetName,$checkFolder) )
			  $result []= $targetName;
	  }
	  return $result;
  }
  
  /**
   * Views file content in the browser
   *
   * @param string $fileName The original file name
   * @return void
   */
  public static function view(string $fileName): void {
    $ext = strtolower( pathinfo($fileName,PATHINFO_EXTENSION) );
    $cType = ArrayUtility::key(self::MIME_TYPES,$ext,'text/plain');
    header('Content-Type: ' . $cType);
    header('Content-Length: ' . filesize($fileName));
    ob_clean();

    readfile($fileName);
  }
  
  /**
   * Retrieves all files and folders from a path
   *
   * @param string $path The folder to search
   * @param bool $filesOnly Flag for returning only files and ignore folders
   * @return array|null
   */
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
  
  /**
   * Deletes all files from a path
   *
   * @param string $path The folder to search
   * @return array|null The unlinked files
   */
  public static function unlinkAll(string $path): ?array {
    $files = self::findAll($path);
    if( is_null($files) ) return $files;
    $files = array_map(fn($row) => "{$path}/{$row}",scandir($path));
    return array_filter($files,fn($row) => is_file($row) && unlink($row));
  }
  
  /**
   * Deletes all files and folders from a path, including the path itself
   *
   * @param string $path The folder to search
   * @return void
   */
  public static function unlinkRecursive(string $path): void { 
		if (is_dir($path)) { 
			$list = scandir($path);
			foreach ($list as $file) { 
				if ($file != "." && $file != "..") { 
					$full = $path . DIRECTORY_SEPARATOR . $file;
					if (is_dir($full) && !is_link($full))
						self::unlinkRecursive($full);
					else
						unlink($full); 
				} 
			}
			rmdir($path); 
		}
	}
}

?>
