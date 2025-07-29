<?php

namespace Devworx\Utility;

use \Devworx\Frontend;

class FileUtility {
  
	const SEPARATOR = '/';
  
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
		return file_exists($path) ? file_get_contents($path) : null;
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

				$fullPath = rtrim($path,self::SEPARATOR) . self::SEPARATOR . $file;
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
			$targetFolder = rtrim($targetFolder,self::SEPARATOR);
			if( !is_dir($targetFolder) )
				mkdir($targetFolder,0x777,true);
		}
		$targetName = StringUtility::cleanupFile(basename($targetName));
		$targetName = $targetFolder . self::SEPARATOR . $targetName;
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
			$targetFolder = rtrim($targetFolder,self::SEPARATOR);
			if( !is_dir($targetFolder) )
			  mkdir($targetFolder,0x777,true);
		}

		foreach( $files['tmp_name'] as $i => $tmpName ){
			$targetFile = self::upload($tmpName,$targetFolder,$files['name'][$i],!$checkFolder);
			if( empty( $targetFile ) ) 
				continue;
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
	public static function findAll(string $path,bool $filesOnly=false,bool $foldersOnly=false): ?array {
		$list = null;
		if( is_dir($path) ){
			$list = self::fileList($path);
			if( $filesOnly )
				return array_filter($list,'file_exists');
			if( $foldersOnly )
				return array_filter($list,'is_dir');
		}
		return $list;
	}
  
	/**
	 * Retrieves all files and folders from a path
	 *
	 * @param string $path The folder to search
	 * @param bool $filesOnly Flag for returning only files and ignore folders
	 * @return array|null
	 */
	public static function findFolders(string $path,bool $recursive=false): ?array {
		$list = self::findAll($path,false,true);
		if( $list === null ) return $list;

		if( $recursive ){
			$tmp = [];
			foreach( $list as $folder ){
				$sub = self::findFolders($folder,$recursive);
				if( $sub === null ) continue;
				$tmp[] = $sub;
			}
			$list = [...$list, ...array_merge(...$tmp)];
		}
		sort($list);
		return $list;
	}
  
	/**
	 * Retrieves all files from a path recursivly
	 *
	 * @param string $path The folder to search
	 * @param bool $recursive calls itself on subfolders
	 * @return array|null
	 */
	public static function findFiles(string $path,bool $recursive=false,array $exclude=[]): ?array {
		if( in_array($path,$exclude,true) ) 
			return null;

		$list = self::findAll($path);
		if( $list === null ) return $list;

		$files = array_filter(
			$list,
			fn($file)=>file_exists($file) && !in_array($file,$exclude)
		);

		if( $recursive ){
			$files = [$files];		
			
			foreach( array_filter($list,'is_dir') as $folder ){
				$sub = self::findFiles($folder,$recursive,$exclude);
				if( $sub === null ) continue;
				$files[] = $sub;
			}
			
			$files = array_merge(...$files);
		}

		sort($files);
		return $files;
	}
  
	/**
	 * Deletes all files from a path, skips folders
	 *
	 * @param string $path The folder to search
	 * @return array|null The unlinked files
	 */
	public static function unlinkAll(string $path): ?array {
		$files = self::findAll($path,true);
		return array_filter($files,fn($row) => is_file($row) && unlink($row));
	}
  
	/**
	 * Deletes all files and folders from a path, including the path itself
	 * checks recursivly, skips symlinks
	 *
	 * @param string $path The folder to search
	 * @return void
	 */
	public static function unlinkRecursive(string $path): void { 
		if (is_dir($path) && !empty($path)) { 
			$list = self::fileList($path);
			foreach ($list as $file) { 
				if (is_dir($file) && !is_link($file))
					self::unlinkRecursive($file);
				else
					unlink($file); 
			}
			rmdir($path);
		}
	}
	
	/**
     * Returns file information about the file
	 * the relative path is stored in $file['path']
     *
     * @param string $file The file to analyse
	 * @param bool $public defines the root of the relative path, public or private
	 *
     * @return array|null $list a list of file information
     */
	public static function analyseFile(string $file,bool $public=true): ?array {
		
		if( !file_exists($file) )
			return null;
		
		return [
			'file' => $file,
			'base' => basename($file),
			'ext' => pathinfo($file,PATHINFO_EXTENSION),
			'folder' => dirname($file),
			'path' => PathUtility::between( 
				$public ? PathUtility::public() : PathUtility::private(), 
				$file 
			),
			'size' => filesize($file),
			'create' => filectime($file),
			'modify' => filemtime($file),
			'access' => fileatime($file),
			'type' => filetype($file),
			'mime' => mime_content_type($file),
			'owner' => fileowner($file),
		];
	}
	
	/**
     * scans recursively for files and returns a folder grouped list of file informations
	 * the relative path is stored in $file['path']
     *
     * @param string $path The folder to search
	 * @param array $exclude a list of folders to exclude
	 * @param bool $public defines the root of the relative path, public or private
	 *
     * @return array|null $list an index of file information grouped by folders
     */
	public static function analyseFolder(string $folder,array $exclude=[],bool $public=true): ?array {
		
		$list = self::findFiles($folder,true,$exclude);
		if( $list === null ) return null;
		
		$list = array_filter( 
			$list,
			fn($file) => file_exists($file) && !is_dir($file)
		);
		
		$list = array_map(
			fn($file)=> self::analyseFile($file,$public),
			$list
		);
		
		return ArrayUtility::index($list,'folder',true);
	}
	
	/**
	 * creates a FilesystemIterator for fetching files
	 * 
	 * @param string $folder the folder name to scan
	 * @return \FilesystemIterator $result the fsi instance
	 */
	public static function iterator(string $folder, int $flags=0): \FilesystemIterator {
		return new \FilesystemIterator($folder, \FilesystemIterator::SKIP_DOTS | $flags);
	}
	
	/**
	 * counts an FilesystemIterator via iterator_count
	 * 
	 * @param string $folder the folder name to scan
	 * @return int $result the amount of entries in the folder
	 */
	public static function fileCount(string $folder, int $flags=0): int {
		return iterator_count( self::iterator($folder) );
	}
	
	/**
	 * Gets a list of file names by plotting a FilesystemIterator via iterator_to_array
	 * 
	 * @param string $folder the folder name to scan
	 * @param int $flags more flags for the fsi
	 * @return int $result the amount of entries in the folder
	 */
	public static function fileList(string $folder, int $flags=0): array {
		return iterator_to_array( self::iterator($folder,$flags) );
	}
}

?>
