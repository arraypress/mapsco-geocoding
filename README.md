# Maps.co API Geocoding Library for WordPress

A WordPress library for Maps.co Geocoding API integration with smart caching and comprehensive location data handling.

## Installation

Install via Composer:

```bash
composer require arraypress/mapsco-geocoding
```

## Requirements

- PHP 7.4 or later
- WordPress 6.2.2 or later
- Maps.co API key

## Basic Usage

```php
use ArrayPress\MapsCo\Geocoding\Client;

// Initialize with your API key
$client = new Client( 'your-api-key-here' );

// Forward geocoding (address to coordinates)
$location = $client->geocode( '1600 Pennsylvania Avenue NW, Washington, DC' );

// Reverse geocoding (coordinates to address)
$location = $client->reverse_geocode( 38.8977, -77.0365 );
```

## Available Methods

### Client Methods

```php
// Initialize client with options
$client = new Client(
	'your-api-key-here',  // API key
	'json',              // Response format (optional, default: 'json')
	true,               // Enable caching (optional, default: true)
	604800             // Cache duration in seconds (optional, default: 1 week)
);

// Forward geocoding
$location = $client->geocode( '1600 Pennsylvania Avenue NW, Washington, DC' );

// Reverse geocoding
$location = $client->reverse_geocode( 38.8977, - 77.0365 );

// Cache management
$client->clear_cache( 'address_key' );  // Clear specific cache
$client->clear_cache();                 // Clear all cached geocoding data
```

### Location Methods

```php
// Get coordinates
$lat = $location->get_latitude();   // Returns: 38.8977
$lon = $location->get_longitude();  // Returns: -77.0365

// Get formatted address
$display_name = $location->get_display_name();
// Returns: "White House, 1600, Pennsylvania Avenue Northwest, Washington, DC 20500"

// Get address components
$house_number = $location->get_house_number();  // Returns: "1600"
$street       = $location->get_street();             // Returns: "Pennsylvania Avenue Northwest"
$city         = $location->get_city();                // Returns: "Washington"
$state        = $location->get_state();              // Returns: "District of Columbia"
$postcode     = $location->get_postcode();        // Returns: "20500"
$country      = $location->get_country();          // Returns: "United States"
$country_code = $location->get_country_code(); // Returns: "US"
$borough      = $location->get_borough();          // Returns: "Ward 2"

// Get OpenStreetMap data
$place_id = $location->get_place_id();   // Returns: OSM place ID
$osm_type = $location->get_osm_type();   // Returns: "way"
$osm_id   = $location->get_osm_id();       // Returns: OSM ID

// Get location type information
$class = $location->get_class();  // Returns: e.g., "office"
$type  = $location->get_type();    // Returns: e.g., "government"

// Get importance ranking
$importance = $location->get_importance();  // Returns: float value

// Get license information
$license = $location->get_license();  // Returns: license text

// Get bounding box
if ( $location->has_bounding_box() ) {
	$bbox = $location->get_bounding_box();
	// Returns: [
	//     'min_lat' => float,
	//     'max_lat' => float,
	//     'min_lon' => float,
	//     'max_lon' => float
	// ]
}
```

### Raw Data Access

```php
// Get complete raw data array
$raw_data = $location->get_raw_data();
```

## Response Format Examples

### Forward Geocoding Response

```php
[
    'place_id' => 308584984,
    'licence' => 'Data © OpenStreetMap contributors, ODbL 1.0.',
    'osm_type' => 'way',
    'osm_id' => 238241022,
    'lat' => '38.897699700000004',
    'lon' => '-77.03655315',
    'display_name' => 'White House, 1600, Pennsylvania Avenue Northwest, Ward 2, Washington, District of Columbia, 20500, United States',
    'class' => 'office',
    'type' => 'government',
    'importance' => 1.05472115416811,
    'boundingbox' => [
        '38.8974908',
        '38.897911',
        '-77.0368537',
        '-77.0362519'
    ]
]
```

### Reverse Geocoding Response

```php
[
    'place_id' => 308584984,
    'licence' => 'Data © OpenStreetMap contributors, ODbL 1.0.',
    'osm_type' => 'way',
    'osm_id' => 238241022,
    'lat' => '38.897699700000004',
    'lon' => '-77.03655315',
    'display_name' => 'White House, 1600, Pennsylvania Avenue Northwest, Ward 2, Washington, District of Columbia, 20500, United States',
    'address' => [
        'office' => 'White House',
        'house_number' => '1600',
        'road' => 'Pennsylvania Avenue Northwest',
        'borough' => 'Ward 2',
        'city' => 'Washington',
        'state' => 'District of Columbia',
        'postcode' => '20500',
        'country' => 'United States',
        'country_code' => 'us'
    ],
    'boundingbox' => [
        '38.8974908',
        '38.897911',
        '-77.0368537',
        '-77.0362519'
    ]
]
```

## Error Handling

The library uses WordPress's `WP_Error` for error handling:

```php
$location = $client->geocode( 'invalid address' );

if ( is_wp_error( $location ) ) {
    echo $location->get_error_message();
    // Output: "No results found for the provided address"
}
```

Common error cases:
- Invalid address
- Invalid coordinates
- Invalid API key
- API request failure
- No results found
- Invalid response format

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.