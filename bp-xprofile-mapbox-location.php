<?php
/**
 * Plugin Name:     BP xProfile Location (Mapbox) Field.
 * Description:     Allow users to confirm that they have read a post
 * Author:          Human Made Limited
 * Author URI:      hmn.md
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         hm_bp_xprofile_mapbox_location
 */

namespace HM\BPxProfileMapboxLocation;

if ( defined( 'BP_XPROFILE_MAPBOX_LOCATION_ACCESS_TOKEN' ) ) {
	add_action( 'init', __NAMESPACE__ . '\\action_init' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );
	add_filter( 'bp_xprofile_get_field_types', __NAMESPACE__ . '\\filter_bp_xprofile_get_field_types' );
} else {
	wp_die( 'You must define the Mapbox access token. bp_xprofile_mapbox_location_access_token' );
}

/**
 * Setup.
 *
 * @return void
 */
function action_init() {
	require_once __DIR__ . '/class-bp-xprofile-field-type-location.php';
}

function enqueue_scripts() {
	// die('wat');
	wp_register_script( 'mapbox', 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.37.0/mapbox-gl.js', [], '0.36.0' );
	wp_register_script( 'mapbox-geocoder', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.1.0/mapbox-gl-geocoder.min.js', [ 'mapbox' ], '2.1.0' );
	wp_register_style( 'mapbox', 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.37.0/mapbox-gl.css', [], '0.37.0' );
	wp_register_style( 'mapbox-geocoder', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.1.0/mapbox-gl-geocoder.css', [ 'mapbox' ], '2.1.0' );

}

function filter_bp_xprofile_get_field_types( $types ) {
	$types['hm-xprofile-location'] = __NAMESPACE__ . '\\BP_XProfile_Field_Type_Location';
	return $types;
}
