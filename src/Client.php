<?php
/**
 * Maps.co Geocoding API Client
 *
 * A comprehensive utility class for interacting with the Maps.co Geocoding API service.
 * Supports both forward and reverse geocoding with WordPress transient caching.
 *
 * Example usage:
 * ```php
 * // Initialize the client
 * $client = new Client('your-api-key-here');
 *
 * // Forward geocoding (address to coordinates)
 * $location = $client->geocode('1600 Pennsylvania Avenue NW, Washington, DC');
 *
 * // Access location data
 * $lat = $location->get_latitude();
 * $lon = $location->get_longitude();
 * $address = $location->get_formatted_address();
 *
 * // Reverse geocoding (coordinates to address)
 * $location = $client->reverse_geocode(38.8977, -77.0365);
 * ```
 *
 * @package     ArrayPress\MapsCo\Geocoding
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\MapsCo\Geocoding;

use WP_Error;

class Client {

	/**
	 * API key for Maps.co
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Base URL for the Geocoding API
	 *
	 * @var string
	 */
	private const API_BASE = 'https://geocode.maps.co/';

	/**
	 * Response format (json, xml, jsonv2, geojson, geocodejson)
	 *
	 * @var string
	 */
	private string $format;

	/**
	 * Whether to enable response caching
	 *
	 * @var bool
	 */
	private bool $enable_cache;

	/**
	 * Cache expiration time in seconds
	 *
	 * @var int
	 */
	private int $cache_expiration;

	/**
	 * Initialize the Geocoding client
	 *
	 * @param string $api_key          API key for Maps.co
	 * @param string $format           Response format (default: json)
	 * @param bool   $enable_cache     Whether to enable caching (default: true)
	 * @param int    $cache_expiration Cache expiration in seconds (default: 1 week)
	 */
	public function __construct(
		string $api_key,
		string $format = 'json',
		bool $enable_cache = true,
		int $cache_expiration = 604800
	) {
		$this->api_key          = $api_key;
		$this->format           = $format;
		$this->enable_cache     = $enable_cache;
		$this->cache_expiration = $cache_expiration;
	}

	/**
	 * Forward geocode an address to coordinates
	 *
	 * @param string $address Address to geocode
	 *
	 * @return Response|WP_Error Location object or WP_Error on failure
	 */
	public function geocode( string $address ) {
		if ( empty( $address ) ) {
			return new WP_Error(
				'invalid_address',
				__( 'Address cannot be empty', 'arraypress' )
			);
		}

		$cache_key = $this->get_cache_key( 'forward_' . $address );

		// Check cache if enabled
		if ( $this->enable_cache ) {
			$cached_data = get_transient( $cache_key );
			if ( false !== $cached_data ) {
				return new Response( $cached_data );
			}
		}

		$response = $this->make_request( 'search', [
			'q'      => $address,
			'format' => $this->format
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! is_array( $response ) || empty( $response ) ) {
			return new WP_Error(
				'no_results',
				__( 'No results found for the provided address', 'arraypress' )
			);
		}

		// Maps.co returns an array of results, sort by importance and use the most relevant one
		usort( $response, function ( $a, $b ) {
			return ( $b['importance'] ?? 0 ) <=> ( $a['importance'] ?? 0 );
		} );

		$data = $response[0];

		if ( $this->enable_cache ) {
			set_transient( $cache_key, $data, $this->cache_expiration );
		}

		return new Response( $data );
	}

	/**
	 * Reverse geocode coordinates to an address
	 *
	 * @param float $latitude  Latitude coordinate
	 * @param float $longitude Longitude coordinate
	 *
	 * @return Response|WP_Error Location object or WP_Error on failure
	 */
	public function reverse_geocode( float $latitude, float $longitude ) {
		if ( ! $this->is_valid_coordinates( $latitude, $longitude ) ) {
			return new WP_Error(
				'invalid_coordinates',
				__( 'Invalid coordinates provided', 'arraypress' )
			);
		}

		$cache_key = $this->get_cache_key( "reverse_{$latitude}_{$longitude}" );

		// Check cache if enabled
		if ( $this->enable_cache ) {
			$cached_data = get_transient( $cache_key );
			if ( false !== $cached_data ) {
				return new Response( $cached_data );
			}
		}

		$response = $this->make_request( 'reverse', [
			'lat'    => $latitude,
			'lon'    => $longitude,
			'format' => $this->format
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( $this->enable_cache ) {
			set_transient( $cache_key, $response, $this->cache_expiration );
		}

		return new Response( $response );
	}

	/**
	 * Make request to the Geocoding API
	 *
	 * @param string $endpoint API endpoint (search or reverse)
	 * @param array  $params   Query parameters
	 *
	 * @return array|WP_Error Response array or WP_Error on failure
	 */
	private function make_request( string $endpoint, array $params = [] ) {
		$params['api_key'] = $this->api_key;

		$url = add_query_arg( $params, self::API_BASE . $endpoint );

		$response = wp_remote_get( $url, [
			'timeout' => 15,
			'headers' => [
				'Accept' => 'application/json',
			],
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'api_error',
				sprintf(
					__( 'Geocoding API request failed: %s', 'arraypress' ),
					$response->get_error_message()
				)
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 ) {
			return new WP_Error(
				'api_error',
				sprintf(
					__( 'Geocoding API returned error code: %d', 'arraypress' ),
					$status_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error(
				'json_error',
				__( 'Failed to parse Geocoding API response', 'arraypress' )
			);
		}

		if ( isset( $data['error'] ) ) {
			return new WP_Error(
				'api_error',
				$data['error'] ?? __( 'Unknown API error', 'arraypress' )
			);
		}

		return $data;
	}

	/**
	 * Validate coordinates
	 *
	 * @param float $latitude  Latitude to validate
	 * @param float $longitude Longitude to validate
	 *
	 * @return bool True if coordinates are valid
	 */
	private function is_valid_coordinates( float $latitude, float $longitude ): bool {
		return $latitude >= - 90 && $latitude <= 90 && $longitude >= - 180 && $longitude <= 180;
	}

	/**
	 * Generate cache key
	 *
	 * @param string $key Base key to hash
	 *
	 * @return string Cache key
	 */
	private function get_cache_key( string $key ): string {
		return 'mapsco_geocoding_' . md5( $key . $this->api_key );
	}

	/**
	 * Clear cached data
	 *
	 * @param string|null $key Optional specific key to clear cache for
	 *
	 * @return bool True on success, false on failure
	 */
	public function clear_cache( ?string $key = null ): bool {
		if ( $key !== null ) {
			return delete_transient( $this->get_cache_key( $key ) );
		}

		global $wpdb;
		$pattern = $wpdb->esc_like( '_transient_mapsco_geocoding_' ) . '%';

		return $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					$pattern
				)
			) !== false;
	}

}