<?php
/*
Plugin Name: RRWD Single Google Map
Plugin URI: http://blog.rrwd.nl/2010/09/07/rrwd-single-google-map-wordpress-plugin
Description: Add a Google map, with address marker to your page or post using the custom files.
Version: 0.2
Author: Rian Rietveld, RRWD web development 
Author URI: http://www.rrwd.nl
License: GPL2 compatible
*/

/*
    Copyright 2010  Rian Rietveld @ RRWD web development  ( email : rian@rrwd.nl )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

//	Hooks					
						 
register_activation_hook( __FILE__,'install_tables' );
add_action( 'wp_head', 'rrwd_get_google_map_js' );
add_action( 'save_post', 'rrwd_save_post' );

// Install tables

function install_tables()
// with thanks to illutic WebDesign http://www.illutic-webdesign.nl

{
	global $wpdb, $wp_roles, $current_user;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	if ( !current_user_can('manage_options') ) return;

	$table = $wpdb->prefix."geo";
	
	if($wpdb->get_var("show tables like '$table'") != $table) // table doesn't exist yet
	{
		// add charset & collate like wp core
		$charset_collate = '';
	
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$sql = "CREATE TABLE IF NOT EXISTS `".$table."` (
			`geo_id` int(11) NOT NULL auto_increment,
			`post_id` int(11) NOT NULL,
			`geo_latitude` varchar(255) NOT NULL,
			`geo_longitude` varchar(255) NOT NULL,
			PRIMARY KEY  (`geo_id`)
			) $charset_collate;";

		dbDelta( $sql );
	}
}


// Functions

function rrwd_get_google_map_js() {
  global $wpd;
  if ( is_page() || is_single() ) rrwd_get_single_google_map_js();
}


function rrwd_get_single_google_map_js() {
  global $wpdb;
  
   $coordinatenArray= array();
   $coordinatenArray = rrwd_get_single_google_map();

   if ( $coordinatenArray[0] ) { 

	  	?>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
      function initialize() {
        var myLatlng = new google.maps.LatLng(<?php echo $coordinatenArray[1].",".$coordinatenArray[0] ?>);
        var myOptions = {
          zoom: 13,
          center: myLatlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    
        var marker = new google.maps.Marker({
            position: myLatlng, 
            map: map,
            title:"<?php echo get_the_title(); ?>"
        });  

        var contentString = '<div id="contentgm">'+
        '<p><?php echo get_the_title(); ?></p>'+
        '</div>';

        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });


        google.maps.event.addListener(marker, 'click', function() {
            infowindow.open(map,marker);
        });
 
      }

  function loadScript() {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = "http://maps.google.com/maps/api/js?sensor=false&callback=initialize";
    document.body.appendChild(script);
  }
  
  window.onload = loadScript;

    </script>
	  	<?php
   } else {
    ?> <script type="text/javascript">function initialize() {} </script> <?php 
   }  
}


function rrwd_get_single_google_map() {
  global $wpdb, $table_prefix, $post;
  $post_id = $post->ID;

 // kijk of er coordinaten zijn in het custom field
  $my_custom_field =  get_post_meta( $post_id, 'address', true );
  if ( $my_custom_field !="" ) {
  // zo ja
     $geo_array[0] = $wpdb->get_var( $wpdb->prepare( "SELECT geo_latitude  FROM ".$table_prefix."geo WHERE post_id='$post_id'" ) );
     $geo_array[1] = $wpdb->get_var( $wpdb->prepare( "SELECT geo_longitude FROM ".$table_prefix."geo WHERE post_id='$post_id'" ) );
    return $geo_array;
  } else {
  // zo nee, kijk in de exerpt
    $this_excerpt =  get_the_excerpt();
    if ( $this_excerpt !="[...]" || $this_excerpt !="" ) {
      $this_excerpt = nl2br( $this_excerpt );
      $excerptArray = split( "<br />", $this_excerpt ); 
      return rrwd_get_coordinates_form_address( $excerptArray[0] );    
    }
  }
}

function rrwd_include_single_google_map( $style=0 ) {
  global $post;
  $post_id = $post->ID;
  $my_custom_field =  get_post_meta( $post_id, 'address', true );
  if ( $my_custom_field !="" ) { 
    // styledefinition, use this if you don't want to use the stylesheet
    // e.g. rrwd_include_google_map( "width:500px; height:400px"; margin:20px 0 20px 0;" )
    unset ($custom_style );
    if ( $style ) $custom_style = "style='$style'";

    // write div for map
    echo "<div id='map_canvas' $custom_style></div>\n";
  }
}

function rrwd_save_post( $post_id ) {
  global $wpdb, $wpd;
  global $table_prefix;
  $coordinatenArray= array();
  unset( $my_custom_field );
  unset( $geo_id );

  // get addresss form custom field
  $my_custom_field =  get_post_meta( $post_id, 'address', true );

  // if found: add or update
  if ( $my_custom_field !="" ) {
   
    // convert address into geo coordinates
    $coordinatenArray = rrwd_get_coordinates_form_address( $my_custom_field );
    $geo_latitude = $coordinatenArray[0];
    $geo_longitude = $coordinatenArray[1];

    // find record for this post in database
    $geo_id = $wpdb->get_var( $wpdb->prepare( "SELECT geo_id FROM ".$table_prefix."geo WHERE post_id='$post_id'" ) );
    if ( $geo_id ) {
      // update geo-coordinates in database
      $result = $wpdb->query( "UPDATE ".$table_prefix."geo SET geo_latitude= '$geo_latitude', geo_longitude= '$geo_longitude' WHERE geo_id='$geo_id'");
    } else {
       // insert geo-coordinates in database
       $result = $wpdb->query( "INSERT INTO ".$table_prefix."geo VALUES (NULL, '$post_id', '$geo_latitude', '$geo_longitude')");
    }
  }  else {    // if not found delete if found
     // find geo-coordinates for this post in database
     $geo_id = $wpdb->get_var( $wpdb->prepare( "SELECT geo_id FROM ".$table_prefix."geo WHERE post_id='$post_id'" ) );
     // delete geo-coordinates in database if found
     if( $geo_id ) $result = $wpdb->query( "DELETE FROM ".$table_prefix."geo WHERE geo_id='$geo_id'" );
  }
}


function rrwd_get_coordinates_form_address( $adres, $timeout=10 ){
  // Roep Google Maps aan met het adres en de soort output
  $adres = urlencode( $adres );
  $url = "http://maps.google.com/maps/geo?q=".$adres."&output=xml";

  // defineer een header
  $parts = parse_url( $url );
  $host = $parts['host'];
  $path = $parts['path'];
  $query = $parts['query'];
  $header = "GET $path"."?"."$query HTTP/1.0\r\n";
  $header .= "Host: $host\r\n";
  $header .= "User-Agent: {$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']} - RRWD\r\n\r\n";

  // open socket naar Google en lees output in
  if ( gethostbyname( $host ) != $host ) {
    $socket = @fsockopen( $host, 80, $errno, $errstr, $timeout );
    if ( $socket ) {
      fwrite( $socket, $header );
      unset( $http_response );
      while ( !feof($socket) ) {
        $http_response .= fread( $socket, 256 );
      }
      fclose( $socket );
      if ( strpos( $http_response, "200 OK" ) ) {
        // mik de header weer weg
        $pos1 = stripos( $http_response, "<?xml" );
        $http_response = substr( "$http_response",$pos1 );
        // lees de xml in en pik de coordinates eruit
        $xml = new SimpleXMLElement( $http_response );
        $coordinatenArray = array();
        $coordinaten =  $xml->Response->Placemark->Point->coordinates;
        $coordinatenArray = explode( ",", $coordinaten );
        return $coordinatenArray;
      }
    }
  }
}

?>