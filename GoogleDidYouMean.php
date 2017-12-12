<?php
/*
Author:    Emil Janitzek
Autor URI: http://pixel2.se
License:   You may redistribute it and/or modify it under the terms of the GNU 
           General Public License as published by the Free Software Foundation, 
           either version 3, (at your option) or any later version.
*/
class GoogleDidYouMean {
  
  private function curl_fetch($url) {
    $options = array( 
            CURLOPT_RETURNTRANSFER => true,     // return web page 
            CURLOPT_HEADER         => false,     // return headers 
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects 
            CURLOPT_ENCODING       => "uft-8",       // handle all encodings 
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Linux; U; Android 2.1-update1; en-gb; HTC Desire Build/ERE27) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17", // who am i 
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect 
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect 
            CURLOPT_TIMEOUT        => 120,      // timeout on response 
            CURLOPT_MAXREDIRS      => 4,        // stop after 4 redirects 
            ); 

    $ch      = curl_init( $url ); 
    curl_setopt_array( $ch, $options ); 
    $content = curl_exec( $ch ); 
    $err     = curl_errno( $ch ); 
    $errmsg  = curl_error( $ch ); 
    $header  = curl_getinfo( $ch ); 
    curl_close( $ch );
    
    return array($content,$header,$err,$errmsg);
    
  }
  
  public function doSpellingSuggestion($phrase, $lang = null) {
    
    if ($lang == "sv")
      $url = "http://www.google.se/m?dc=gorganic&source=mobileproducts&q=";
    else
      $url = "http://www.google.com/m?dc=gorganic&source=mobileproducts&q=";
    
    list($content,$header,$err,$errmsg) = self::curl_fetch($url . $phrase);
    
    /* No website at this location */
    if (!$content || $header['http_code'] != 200) {
      return '';
    }
    
    /* Match against the spell suggestion box and get the suggested word if exists */
    //$pattern = '/<div[^>]*spelling_onebox_result[^>]*>.*?<b><i>(.+?)<\/i><\/b>.*<\/div>/';
    $pattern = '/<span[^>]*>[a-zA-Z\s]+:<\/span> <a[^>]+><b><i>(.+?)<\/i><\/b><\/a>/';
    preg_match($pattern, $content, $matches);
    if (count($matches) > 0) {
      return $matches[1];
    } else {
      $search = '<div>Showing results for <a href="/search?q=';
      $pos = strpos($content, $search);
      if ($pos === false) {
        return '';
      } else {
        $pos += strlen($search);
        $pos2 = strpos($content, '&', $pos);
        if ($pos2 === false) {
          return '';
        } else {
          return substr($content, $pos, $pos2 - $pos);
        }
      }
    }
    
  }
  
}

if (!empty($_GET["q"])) {
  
  $phrase   = trim($_GET["q"]);
  $lang     = trim($_GET['hl']);
  $dataType = trim($_GET['dataType']);
  
  $google = new GoogleDidYouMean();
  
  $result = $google->doSpellingSuggestion(urlencode($phrase), $lang);
  
  if ($dataType == "text") {
    echo $result;
  } else if(!empty($result)) {
    echo json_encode(array('google' => array('suggestion' => $result)));
  } else {
    echo json_encode(array('google' => null));
  }
  
}