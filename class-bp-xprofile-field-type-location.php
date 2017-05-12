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

			$val = json_decode( htmlspecialchars_decode( $r['value'] ) );

			/** This action is documented in bp-xprofile/bp-xprofile-classes */
			do_action( bp_get_the_profile_field_errors_action() ); ?>

			<p>
				<b>Current value:</b>
				<?php if ( isset( $val->coordinates ) ) { echo esc_html( implode( ', ', $val->coordinates ) ); } ?>
				<?php if ( isset( $val->name ) ) { echo esc_html( $val->name ); } ?>
			</p>

			<input <?php echo $this->get_edit_field_html_elements( $r ); ?>>
			<div id='map_<?php echo bp_get_the_profile_field_input_name(); ?>'></div>

			<style>
				#map_<?php echo bp_get_the_profile_field_input_name(); ?> {
					width: 100%;
					max-width: 600px;
					clear: right;
					height: 300px;
					float: left;
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

				/*.wp-admin #map_<?php echo bp_get_the_profile_field_input_name(); ?> {
					width: calc( 100% - 200px );
				}*/

			</style>
			<script>

			var input = jQuery( '#<?php echo esc_attr( bp_get_the_profile_field_input_name() ); ?>' );
			var data  = JSON.parse( input.val() );

			jQuery( document ).ready( function() {
				mapboxgl.accessToken = <?php echo wp_json_encode( BP_XPROFILE_MAPBOX_LOCATION_ACCESS_TOKEN ); ?>;
				var map = new mapboxgl.Map({
					container: 'map_<?php echo bp_get_the_profile_field_input_name(); ?>',
					style:     'mapbox://styles/mapbox/streets-v9',
					center:    ( 'coordinates' in data ) ? data.coordinates : [ -1.5489, 53.1419 ],
					zoom:      10
				});

				var geoCoder = new MapboxGeocoder({
					accessToken: mapboxgl.accessToken,
					types: 'country,region,postcode,district,place,locality,neighborhood',
				});

				map.addControl( geoCoder );

				var marker = new mapboxgl.Marker()
					.setLngLat( ( 'coordinates' in data ) ? data.coordinates : [ -1.5489, 53.1419 ] )
					.addTo( map );

				var updatePosition = function( data ) {
					input.val( JSON.stringify( data ) );
					console.log( data );
					marker.setLngLat( data.coordinates );
				}
				console.log( marker );


				geoCoder.on( 'result', function( data ) {
					updatePosition( {
						coordinates: data.result.center,
						name:        data.result.place_name,
					});
				})

				map.on( 'click', function(e) {
					console.log( e );
					updatePosition( {
						coordinates: [ e.lngLat.lng, e.lngLat.lat ],
						name: '',
					});
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
