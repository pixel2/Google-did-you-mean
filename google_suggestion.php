<?php
/*
Plugin Name: Google did-you-mean&trade;
Plugin URI: http://blogg.pixel2.se
Description:  Uses google did-you-mean&trade; to find misspellings and grammatical errors in searches queries, useful on search pages when no result was found. Include <code>google_suggestion();</code> in your code.
Version: 1.2
Author: Emil Janitzek
Author URI: http://pixel2.se
*/

add_action( 'wp_print_scripts', 'init_jquery' );
//add_action('get_search_query', 'proccess');

function init_jquery() {
	if (is_search()) wp_enqueue_script('jquery');
}

function google_suggestion($lang = null) {
	
	if ($lang == "sv")
		$text = "Menade du:";
	else
		$text = "Did you mean:";

	echo '<div class="gdym" style="display: none;"><span class="gdym-text" style="color:red;">'.  $text .'</span> <a href="#" class="gdym-result" style="font-weight: bold;"></a></div>';
	
	echo '<script type="text/javascript">(function($) {
$.ajax({ url: "/wp-content/plugins/google-did-you-mean/GoogleDidYouMean.php", data: {"q": "'. $_GET['s'] .'", "hl": "'. (($lang==null)?'en':$lang) .'", "dataType": "text"}, success: function(data){
if (data.length > 0) {
$(".gdym-result").attr("href","?s="+ encodeURIComponent(data)).text(data);
$(".gdym").show();
}
}}); })(jQuery);</script>';
}
