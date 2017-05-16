<?php

namespace HM\BPxProfileMapboxLocation;

class BP_XProfile_Field_Type_Location extends \BP_XProfile_Field_Type_Textbox {
	/**
	 * Constructor for the textarea field type.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->category = _x( 'Single Fields', 'xprofile field type category', 'hm-theme' );
		$this->name     = _x( 'Location', 'xprofile field type', 'hm-theme' );
		// $this->set_format( '/^-?(\d+)(.\d+)?,-?(\d+)(.\d+)?$/', 'replace' );
	}

	/**
	 * Output the edit field HTML for this field type.
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 2.0.0
	 *
	 * @param array $raw_properties Optional key/value array of
	 *                              {@link http://dev.w3.org/html5/markup/input.text.html permitted attributes}
	 *                              that you want to add.
	 */
	public function edit_field_html( array $raw_properties = array() ) {

		wp_enqueue_script( 'mapbox-geocoder' );
		wp_enqueue_style( 'mapbox-geocoder' );

		// User_id is a special optional parameter that certain other fields
		// types pass to {@link bp_the_profile_field_options()}.
		if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$r = bp_parse_args( $raw_properties, array(
			'type'  => 'hidden',
			'value' => bp_get_the_profile_field_edit_value(),
		) ); ?>

		<label for="<?php bp_the_profile_field_input_name(); ?>">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
		</label>

		<?php

		/** This action is documented in bp-xprofile/bp-xprofile-classes */
		do_action( bp_get_the_profile_field_errors_action() ); ?>

		<p><b>Current value:</b> <?php echo esc_html( $r['value'] ); ?></p>

		<input <?php echo $this->get_edit_field_html_elements( $r ); ?>>
		<div id='map_<?php echo bp_get_the_profile_field_input_name(); ?>'></div>

		<style>
			#map_<?php echo bp_get_the_profile_field_input_name(); ?> {
				width: 100%;
				max-width: 600px;
				clear: both;
				height: 300px;
				float: none;
				margin-bottom: 10px
			}
			.field_type_hm-xprofile-location p {
				clear: both;
			}
			.mapboxgl-marker {
				width: 10px;
				height: 10px;
				background: #D14732;
				box-shadow: 0 1px 2px rgba( 0,0,0,0.2 );
				border-radius: 10px;
			}
			.mapboxgl-ctrl-geocoder input {
				width: 100% !important;
				padding: 10px 10px 10px 40px !important;
				border: none !important;
			}

			.wp-admin .field_type_hm-xprofile-location p {
				display: inline-block;
				clear: none;
			}

		</style>
		<script>
		jQuery( document ).ready( function() {

			var input        = jQuery( '#<?php echo esc_attr( bp_get_the_profile_field_input_name() ); ?>' );
			var coordinates  = input.val().split(',');

			for ( var i = 0; i < coordinates.length; i++ ) {
				coordinates[i] = parseFloat( coordinates[i] );
			}

			console.log( input.val(), coordinates );

			mapboxgl.accessToken = <?php echo wp_json_encode( BP_XPROFILE_MAPBOX_LOCATION_ACCESS_TOKEN ); ?>;
			var map = new mapboxgl.Map({
				container: 'map_<?php echo bp_get_the_profile_field_input_name(); ?>',
				style:     'mapbox://styles/mapbox/streets-v9',
				center:    ( coordinates.length === 2 ) ? coordinates : [ -1.5489, 53.1419 ],
				zoom:      9,
				maxZoom:   9
			});

			var geoCoder = new MapboxGeocoder({
				accessToken: mapboxgl.accessToken,
				types: 'country,region,postcode,district,place,locality,neighborhood',
				zoom: 9,
			});

			map.addControl( geoCoder );

			var marker;

			var updatePosition = function( coordinates ) {
				if ( ! marker ) {
					marker = new mapboxgl.Marker()
					   .setLngLat( coordinates )
					   .addTo( map );
				}

				input.val( coordinates.join(',') );
				marker.setLngLat( coordinates );
			}

			if ( ( coordinates.length === 2 ) ) {
				updatePosition( coordinates );
			}

			geoCoder.on( 'result', function( data ) {
				updatePosition( data.result.center );
			})

			map.on( 'click', function(e) {
				updatePosition( [ e.lngLat.lng, e.lngLat.lat ] );
			} );

			// Prevent search box submitting form.
			jQuery( '.mapboxgl-ctrl-geocoder input' ).on( 'keydown', function(e) {
				if ( 13 === e.keyCode ) {
					e.preventDefault();
				}
			});

		});
		</script>

		<?php
	}

}
