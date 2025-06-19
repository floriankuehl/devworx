<?php

namespace Devworx\Utility;

use \Devworx\Frontend;

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
   * Gets file content if file exists
   *
   * @param string $path The folder to search
   * @return string|null $result File content as string if exists
   */  
  public static function get(string $path): ?string {
	  if( file_exists($path) )
		  return file_get_contents($path);
	  return null;
  }
  
  /**
   * Sets file content with optional overwrite check
   *
   * @param string $path The folder to search
   * @param mixed $data The content to put
   * @param bool $overwrite Flag for overwriting the path
   * @return bool
   */
  public static function set(string $path,mixed $data,bool $overwrite=true): bool {
	if( $overwrite || is_null($data) || !file_exists($path) ){
		file_put_contents($path,$data);
		return true;
	  }
	  return false;
  }
  
  /**
   * Gets file content as JSON
   *
   * @param string $path The folder to search
   * @param bool $assoc parse json as associative array
   * @return mixed $result File content as JSON or null
   */
  public static function getJson(string $path,bool $assoc=true): mixed {
	  $content = self::get($path);
	  if( is_null( $content ) ) return $content;
	  return json_decode( $content, $assoc );
  }
  
  /**
   * Sets file content to JSON
   *
   * @param string $path The folder to search
   * @param bool $assoc parse json as associative array
   * @return mixed $result File content as JSON or null
   */
  public static function setJson(
	string $path,
	mixed $data,
	int $options=JSON_PRETTY_PRINT,
	bool $overwrite=true
  ): bool {
	  $content = json_encode($data,$options);
	  if( is_null( $content ) ) return false;
	  return self::set($path,$content,$overwrite);
  }
  
  /**
   * Merges file contents
   *
   * @param array $files the files to load
   * @return string $result The joined file content
   */
  public static function merge(...$files): ?string {
	  $result = array_map(fn($file)=>self::get($file));
	  return implode(PHP_EOL,$result);
  }
  
  /**
   * Merges json file contents
   *
   * @param array $files the files to load
   * @return array $result The combined json objects
   */
  public static function mergeJson(...$files): ?array {
	  $result = array_map(fn($file)=>self::getJson($file));
	  return ArrayUtility::merge(...$result);
  }
  
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
        
        $fullPath = rtrim($path,Frontend::PATHGLUE) . Frontend::PATHGLUE . $file;
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
   * Uploads a file and returns the full file name on success
   *
   * @param string $tmpName The temporary file name
   * @param string $targetFolder The target folder name
   * @param string $targetName The target file name
   * @param bool $checkFolder Checks folder for trailing slash and existence
   * @return string
   */
  public static function upload(string $tmpName,string $targetFolder,string $targetName,bool $checkFolder=false): string {
	  if( $checkFolder ){
		  $targetFolder = rtrim($targetFolder,Frontend::PATHGLUE);
		  if( !is_dir($targetFolder) )
			  mkdir($targetFolder,0x777,true);
	  }
	  $targetName = StringUtility::cleanupFile(basename($targetName));
	  $targetName = $targetFolder . Frontend::PATHGLUE . $targetName;
	  return move_uploaded_file($tmpName, $targetName) ? $targetName : '';
  }
  
  /**
   * Uploads an array of files and returns the successfull uploaded files
   *
   * @param array $files The file array
   * @param string $targetFolder The target folder
   * @param bool $checkFolder Checks folder for trailing slash and existence
   * @return array
   */
  public static function uploadAll(array $files,string $targetFolder,bool $checkFolder=false): array {
	  $result = [];
	  if( $checkFolder ){
		  $targetFolder = rtrim($targetFolder,Frontend::PATHGLUE);
		  if( !is_dir($targetFolder) )
			  mkdir($targetFolder,0x777,true);
	  }
	  
	  foreach( $files['tmp_name'] as $i => $tmpName ){
		  $targetFile = self::upload($tmpName,$targetFolder,$files['name'][$i],!$checkFolder);
		  if( empty( $targetFile ) ) continue;
		  $result []= $targetFile;
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
					$full = $path . Frontend::PATHGLUE . $file;
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
