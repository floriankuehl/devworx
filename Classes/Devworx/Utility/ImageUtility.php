<?php

namespace Devworx\Utility;

class ImageUtility {
  
  const IMG_MIME = [
    'image/png' => 'png',
    'image/jpg' => 'jpeg',
    'image/jpeg' => 'jpeg',
    'image/pjpeg' => 'jpeg'
  ];
  
  const IMG_QUALITY = [
    'image/png' => 100,
    'image/jpg' => 100,
    'image/jpeg' => 100,
    'image/pjpeg' => 100
  ];
  
  static function resizeAspect(int $width,int $height,int $newWidth=0,int $newHeight=0){
    $result = [$width,$height];
    
    if( empty($newWidth) && empty($newHeight) )
      return $result;
    
    if( empty($newHeight) ){
      $result[0] = $newWidth;
      $result[1] = floor($height / $width * $newWidth);
    } elseif( empty($newWidth) ){
      $result[0] = floor($width / $height * $newHeight);
      $result[1] = $newHeight;
    } else {
      if( $width > $height ){
        $result[0] = $newWidth;
        $result[1] = floor($height / $width * $newWidth);
      }
      if( $width < $height ){
        $result[0] = floor( $width / $height * $newHeight );
        $result[1] = $newHeight;
      }
      if( $width == $height ){
        $result[0] = $newWidth;
        $result[1] = $newHeight;
      }
    }
    
    $result[0] = (int)$result[0];
    $result[1] = (int)$result[1];
    
    return $result;
  }
  
  static function resize(string $fileName,int $width=300,$height=300){
    $result = null;
    if( file_exists( $fileName ) ){
      $info = getimagesize($fileName);
      if( is_array($info) && array_key_exists( 'mime', $info ) ){
        if( array_key_exists( $info['mime'], self::IMG_MIME ) ){
          $ext = self::IMG_MIME[ $info['mime'] ];
          $constructor = "imagecreatefrom{$ext}";
          
          $ext = 'jpeg';
          $saver = "image{$ext}";
          $quality = self::IMG_QUALITY[ $info['mime'] ];
          
          $source = $constructor($fileName);
          $size = [ imageSX($source), imageSY($source) ];
          $thumb = self::resizeAspect( $size[0], $size[1], $width, $height );
          
          //echo \Devworx\Utility\DebugUtility::var_dump($thumb);
          
          $destination = ImageCreateTrueColor( $thumb[0], $thumb[1] );
          imagecopyresampled(
            $destination,
            $source,
            0,0,
            0,0,$thumb[0],$thumb[1],
            $size[0], $size[1]
          );
          imagedestroy($source);
          $result = $saver($destination,$fileName,$quality);
          imagedestroy($destination);
        }
      }
    }
    return $result;
  }
}

?>
