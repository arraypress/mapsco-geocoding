<?php
/**
 * Maps.co Geocoding API Parameters Trait
 *
 * @package     ArrayPress\MapsCo\Geocoding
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\MapsCo\Geocoding\Traits;

/**
 * Trait Parameters
 *
 * Manages parameters for the Maps.co Geocoding API.
 *
 * @package ArrayPress\MapsCo\Geocoding
 */
trait Parameters {

	/**
	 * API key for Maps.co Geocoding
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Response format
	 *
	 * @var string
	 */
	private string $format = 'json';

	/**
	 * Cache settings
	 *
	 * @var array
	 */
	private array $cache_settings = [
		'enabled'    => true,
		'expiration' => WEEK_IN_SECONDS
	];

	/** API Key ******************************************************************/

	/**
	 * Set API key
	 *
	 * @param string $api_key The API key to use
	 *
	 * @return self
	 */
	public function set_api_key( string $api_key ): self {
		$this->api_key = $api_key;

		return $this;
	}

	/**
	 * Get API key
	 *
	 * @return string
	 */
	public function get_api_key(): string {
		return $this->api_key;
	}

	/** Format *******************************************************************/

	/**
	 * Set response format
	 *
	 * @param string $format Response format (json, xml, jsonv2, geojson, geocodejson)
	 *
	 * @return self
	 */
	public function set_format( string $format ): self {
		$this->format = $format;

		return $this;
	}

	/**
	 * Get response format
	 *
	 * @return string
	 */
	public function get_format(): string {
		return $this->format;
	}

	/** Cache ********************************************************************/

	/**
	 * Set cache status
	 *
	 * @param bool $enable Whether to enable caching
	 *
	 * @return self
	 */
	public function set_cache_enabled( bool $enable ): self {
		$this->cache_settings['enabled'] = $enable;

		return $this;
	}

	/**
	 * Get cache status
	 *
	 * @return bool
	 */
	public function is_cache_enabled(): bool {
		return $this->cache_settings['enabled'];
	}

	/**
	 * Set cache expiration time
	 *
	 * @param int $seconds Cache expiration time in seconds
	 *
	 * @return self
	 */
	public function set_cache_expiration( int $seconds ): self {
		$this->cache_settings['expiration'] = $seconds;

		return $this;
	}

	/**
	 * Get cache expiration time in seconds
	 *
	 * @return int
	 */
	public function get_cache_expiration(): int {
		return $this->cache_settings['expiration'];
	}

	/**
	 * Get all cache settings
	 *
	 * @return array Current cache settings
	 */
	public function get_cache_settings(): array {
		return $this->cache_settings;
	}

}