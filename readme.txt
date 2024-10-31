=== Plugin Name ===
Contributors: rianrietveld
Tags: google maps, google map, address, meta-data, geo coordinates, custom field, rrwd
Requires at least: 1.0
Tested up to: 3.01
Stable tag: 0.2

Show a Google Map with your post or page, simply by adding the address into the custom field ‘address’.

== Description ==
Add a Google map, with marker to a post or page, using the custom files to add the address.
Just add the address in the format 'street name number, postal code, city, country' into a custom field called 'address' and the map will be added to the page or post. 
The map is displayed in a div with the id #map_canvas. This div can be integrated in your theme in the single.php and page.php, wherever you want.
The size and margins of the Google map are adjustable via the function call or via the stylesheet.
For support and discussion see: http://blog.rrwd.nl/2010/09/07/rrwd-single-google-map-wordpress-plugin

== Installation ==

1. Upload `rrwd-single-google-map.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add '<?php rrwd_include_single_google_map( "width: 500px;  height: 400px;" ) ) ?>' to the template files single.php and page.php within the loop.
   Adjust the style to your liking.
   Or leave it empty if you want define the style of #map_canvas in the stylesheet,
   like rrwd_include_google_map();
   in style.php then add e.g: #map_canvas { width: 300px;  height: 300px; padding: 20px 0 20px 0; margin: 0; }

== Screenshots ==
1. Add the address, the place you want the marker be appear, in custom field named address (Extra velden in Dutch)

2. The map must be placed in the loop, size is adjustable by changing the style 

== Frequently Asked Questions ==

= I don't see the map =

Check if you have the style filled out in the function call, like:
'<?php rrwd_include_single_google_map( 'width: 500px;  height: 400px;' ) ?>'
Or check if you have defined the style into your stylesheet like:
#map_canvas {
width: 500px;
height: 400px;
margin:  10px 0 10px 0;
}
No style, no map.

= Can I place the map wherever I want? =

You have put the map inside the loop of single.php and/or page.php, and you have to change "style" in some kind of style definition, like:
<?php rrwd_include_single_google_map(“width: 400px; height: 400px;”) ?>
for more explanation about the loop see: http://codex.wordpress.org/The_Loop and http://codex.wordpress.org/The_Loop_in_Action

= I get a Fatal error: Cannot instantiate non-existent class: simplexmlelement =

If you get this Fatal error: Cannot instantiate non-existent class: simplexmlelement in /.../wp-content/plugins/rrwd-single-google-map/rrwd-single-google-map.php on line 241
The server you are running must be able to execute SimpleXMLElement, so it can read the XML-file from Google to get the latitude and longitude. Your PHP installation needs the module PHP:MXL
See: http://www.php.net/manual/en/book.xml.php
Ask your provider to install it.
  


== Upgrade Notice ==

= 0.1 =
* First release

= 0.2 =
* Bug Fix, please upgrade

== Changelog ==

= 0.1 =
* First release

= 0.2 =
* Bug Fix in storing geo-data in the MySQL database
