<?php
/**
 * Class Location
 *
 * Represents a geocoded location with various properties.
 *
 * @package     ArrayPress\MapsCo\Geocoding
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\MapsCo\Geocoding;

class Response {

	/**
	 * Raw location data from the API
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Base URLs for various mapping services
	 */
	private const MAP_URLS = [
		'google' => 'https://www.google.com/maps/search/?api=1&query=',
		'apple'  => 'https://maps.apple.com/?q=',
		'bing'   => 'https://www.bing.com/maps?cp=',
		'osm'    => 'https://www.openstreetmap.org/?mlat='
	];

	/**
	 * Initialize the Location object
	 *
	 * @param array $data Raw location data from the API
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * Get the latitude coordinate
	 *
	 * @return float|null
	 */
	public function get_latitude(): ?float {
		return isset( $this->data['lat'] ) ? (float) $this->data['lat'] : null;
	}

	/**
	 * Get the longitude coordinate
	 *
	 * @return float|null
	 */
	public function get_longitude(): ?float {
		return isset( $this->data['lon'] ) ? (float) $this->data['lon'] : null;
	}

	/**
	 * Get coordinates as an array
	 *
	 * @return array|null Returns array with latitude and longitude or null if not available
	 */
	public function get_coordinates(): ?array {
		$lat = $this->get_latitude();
		$lon = $this->get_longitude();

		if ( $lat === null || $lon === null ) {
			return null;
		}

		return [
			'latitude'  => $lat,
			'longitude' => $lon
		];
	}

	/**
	 * Get Google Maps URL for the coordinates
	 *
	 * @return string|null URL to view location on Google Maps
	 */
	public function get_google_maps_url(): ?string {
		$coordinates = $this->get_coordinates();
		if ( ! $coordinates ) {
			return null;
		}

		return self::MAP_URLS['google'] .
		       esc_attr( $coordinates['latitude'] ) . ',' .
		       esc_attr( $coordinates['longitude'] );
	}

	/**
	 * Get Apple Maps URL for the coordinates
	 *
	 * @return string|null URL to view location on Apple Maps
	 */
	public function get_apple_maps_url(): ?string {
		$coordinates = $this->get_coordinates();
		if ( ! $coordinates ) {
			return null;
		}

		return self::MAP_URLS['apple'] .
		       esc_attr( $coordinates['latitude'] ) . ',' .
		       esc_attr( $coordinates['longitude'] );
	}

	/**
	 * Get Bing Maps URL for the coordinates
	 *
	 * @return string|null URL to view location on Bing Maps
	 */
	public function get_bing_maps_url(): ?string {
		$coordinates = $this->get_coordinates();
		if ( ! $coordinates ) {
			return null;
		}

		return self::MAP_URLS['bing'] .
		       esc_attr( $coordinates['latitude'] ) . '~' .
		       esc_attr( $coordinates['longitude'] );
	}

	/**
	 * Get OpenStreetMap URL for the coordinates
	 *
	 * @return string|null URL to view location on OpenStreetMap
	 */
	public function get_openstreetmap_url(): ?string {
		$coordinates = $this->get_coordinates();
		if ( ! $coordinates ) {
			return null;
		}

		return self::MAP_URLS['osm'] .
		       esc_attr( $coordinates['latitude'] ) .
		       '&mlon=' . esc_attr( $coordinates['longitude'] ) .
		       '&zoom=12';
	}

	/**
	 * Get all map URLs for the coordinates
	 *
	 * @return array Array of map service URLs
	 */
	public function get_map_urls(): array {
		return [
			'google' => $this->get_google_maps_url(),
			'apple'  => $this->get_apple_maps_url(),
			'bing'   => $this->get_bing_maps_url(),
			'osm'    => $this->get_openstreetmap_url()
		];
	}

	/**
	 * Get the formatted display name
	 *
	 * @return string|null
	 */
	public function get_display_name(): ?string {
		return $this->data['display_name'] ?? null;
	}

	/**
	 * Get the place ID from OpenStreetMap
	 *
	 * @return int|null
	 */
	public function get_place_id(): ?int {
		return isset( $this->data['place_id'] ) ? (int) $this->data['place_id'] : null;
	}

	/**
	 * Get the OSM type (node, way, relation)
	 *
	 * @return string|null
	 */
	public function get_osm_type(): ?string {
		return $this->data['osm_type'] ?? null;
	}

	/**
	 * Get the OSM ID
	 *
	 * @return int|null
	 */
	public function get_osm_id(): ?int {
		return isset( $this->data['osm_id'] ) ? (int) $this->data['osm_id'] : null;
	}

	/**
	 * Get the location class (e.g., office, building, tourism)
	 *
	 * @return string|null
	 */
	public function get_class(): ?string {
		return $this->data['class'] ?? null;
	}

	/**
	 * Get the location type (e.g., government, yes, information)
	 *
	 * @return string|null
	 */
	public function get_type(): ?string {
		return $this->data['type'] ?? null;
	}

	/**
	 * Get the location importance ranking
	 *
	 * @return float|null
	 */
	public function get_importance(): ?float {
		return isset( $this->data['importance'] ) ? (float) $this->data['importance'] : null;
	}

	/**
	 * Get the license information
	 *
	 * @return string|null
	 */
	public function get_license(): ?string {
		return $this->data['licence'] ?? null;
	}

	/**
	 * Get the bounding box coordinates
	 *
	 * @return array|null Array with min/max lat/lon values or null
	 */
	public function get_bounding_box(): ?array {
		if ( ! isset( $this->data['boundingbox'] ) || ! is_array( $this->data['boundingbox'] ) ) {
			return null;
		}

		return [
			'min_lat' => (float) $this->data['boundingbox'][0],
			'max_lat' => (float) $this->data['boundingbox'][1],
			'min_lon' => (float) $this->data['boundingbox'][2],
			'max_lon' => (float) $this->data['boundingbox'][3]
		];
	}

	/**
	 * Check if the location has a bounding box
	 *
	 * @return bool
	 */
	public function has_bounding_box(): bool {
		return isset( $this->data['boundingbox'] ) && is_array( $this->data['boundingbox'] );
	}

	/**
	 * Get the bounding box dimensions in kilometers
	 *
	 * @return array|null Width and height in kilometers, or null if bounding box is not available
	 */
	public function get_bounding_box_dimensions(): ?array {
		$box = $this->get_bounding_box();
		if ( ! $box ) {
			return null;
		}

		$earthRadiusKm = 6371;

		$latDistance = deg2rad( $box['max_lat'] - $box['min_lat'] );
		$lonDistance = deg2rad( $box['max_lon'] - $box['min_lon'] );
		$latCenter   = deg2rad( ( $box['max_lat'] + $box['min_lat'] ) / 2 );

		$widthKm  = $earthRadiusKm * $lonDistance * cos( $latCenter );
		$heightKm = $earthRadiusKm * $latDistance;

		return [
			'width_km'  => abs( $widthKm ),
			'height_km' => abs( $heightKm ),
		];
	}

	/**
	 * Get the approximate radius of the bounding box in kilometers
	 *
	 * @return float|null Radius in kilometers, or null if bounding box is not available
	 */
	public function get_bounding_box_radius(): ?float {
		$box = $this->get_bounding_box();
		if ( ! $box ) {
			return null;
		}

		$earthRadiusKm = 6371;

		$latCenter = ( $box['max_lat'] + $box['min_lat'] ) / 2;
		$lonCenter = ( $box['max_lon'] + $box['min_lon'] ) / 2;

		$cornerLat = $box['max_lat'];
		$cornerLon = $box['max_lon'];

		$latDistance = deg2rad( $cornerLat - $latCenter );
		$lonDistance = deg2rad( $cornerLon - $lonCenter );

		$a = sin( $latDistance / 2 ) ** 2 +
		     cos( deg2rad( $latCenter ) ) * cos( deg2rad( $cornerLat ) ) * sin( $lonDistance / 2 ) ** 2;
		$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

		return $earthRadiusKm * $c;
	}


	/**
	 * Get all address components
	 *
	 * @return array|null
	 */
	public function get_address(): ?array {
		if ( isset( $this->data['address'] ) ) {
			return $this->data['address'];
		}

		// For forward geocoding, try to parse from display_name
		if ( isset( $this->data['display_name'] ) ) {
			$parts   = explode( ', ', $this->data['display_name'] );
			$address = [];

			// Last part is always country
			if ( count( $parts ) > 0 ) {
				$address['country'] = array_pop( $parts );
			}

			// Second to last is usually postal code
			if ( count( $parts ) > 0 ) {
				$address['postcode'] = array_pop( $parts );
			}

			// Third to last is usually state/region
			if ( count( $parts ) > 0 ) {
				$address['state'] = array_pop( $parts );
			}

			// Fourth to last is usually city
			if ( count( $parts ) > 0 ) {
				$address['city'] = array_pop( $parts );
			}

			// First part usually contains the street info
			if ( count( $parts ) > 0 ) {
				$address['road'] = implode( ', ', $parts );
			}

			return $address;
		}

		return null;
	}

	/**
	 * Get a specific address component
	 *
	 * @param string $component Component name (e.g., 'city', 'country', 'postcode')
	 *
	 * @return string|null
	 */
	public function get_address_component( string $component ): ?string {
		$address = $this->get_address();

		return $address[ $component ] ?? null;
	}

	/**
	 * Get the house number
	 *
	 * @return string|null
	 */
	public function get_house_number(): ?string {
		return $this->get_address_component( 'house_number' );
	}

	/**
	 * Get the street name
	 *
	 * @return string|null
	 */
	public function get_street(): ?string {
		return $this->get_address_component( 'road' );
	}

	/**
	 * Get the city/town name
	 *
	 * @return string|null
	 */
	public function get_city(): ?string {
		return $this->get_address_component( 'city' );
	}

	/**
	 * Get the state/province name
	 *
	 * @return string|null
	 */
	public function get_state(): ?string {
		return $this->get_address_component( 'state' );
	}

	/**
	 * Get the postal/zip code
	 *
	 * @return string|null
	 */
	public function get_postcode(): ?string {
		return $this->get_address_component( 'postcode' );
	}

	/**
	 * Get the country name
	 *
	 * @return string|null
	 */
	public function get_country(): ?string {
		return $this->get_address_component( 'country' );
	}

	/**
	 * Get the country code (ISO 3166-1 alpha-2)
	 *
	 * @return string|null
	 */
	public function get_country_code(): ?string {
		$code = $this->get_address_component( 'country_code' );

		return $code ? strtoupper( $code ) : null;
	}

	/**
	 * Get the borough/district name
	 *
	 * @return string|null
	 */
	public function get_borough(): ?string {
		return $this->get_address_component( 'borough' );
	}

	/**
	 * Get the raw data array
	 *
	 * @return array
	 */
	public function get_raw_data(): array {
		return $this->data;
	}

}