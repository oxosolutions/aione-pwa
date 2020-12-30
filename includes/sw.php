<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
function aione_pwa_get_sw_filename() {
    return apply_filters( 'aione_pwa_sw_filename', 'aione-pwa-sw' . aione_pwa_multisite_filename_postfix() . '.js' );
}
function aione_pwa_sw( $arg = 'src' ) {	
	$sw_filename = aione_pwa_get_sw_filename();	
	switch( $arg ) {
		// TODO: Case `filename` can be deprecated in favor of @see aione_pwa_get_sw_filename().
		// Name of service worker file
		case 'filename':
			return $sw_filename;
			break;

		case 'abs':
			return trailingslashit( ABSPATH ) . $sw_filename;
			break;

		// Link to service worker
		case 'src':
		default:
		
			// Get Settings
			$settings = aione_pwa_get_settings();
			
			
			if ( $settings['is_static_sw'] === 1 ) {
				return trailingslashit( network_site_url() ) . $sw_filename;
			}
			
			// For dynamic files, return the home_url
			return home_url( '/' ) . $sw_filename;
			
			break;
	}
}
function aione_pwa_generate_sw() {	
	// Delete service worker if it exists
	aione_pwa_delete_sw();
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	// Return true if dynamic file returns a 200 response.
	if ( aione_pwa_file_exists( home_url( '/' ) . aione_pwa_get_sw_filename() ) && defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		
		// set file status as dynamic file in database.
		$settings['is_static_sw'] = 0;
		
		// Write settings back to database.
		update_option( 'aione_pwa_settings', $settings );
		
		return true;
	}
	
	if ( aione_pwa_put_contents( aione_pwa_sw( 'abs' ), aione_pwa_sw_template() ) ) {
		
		// set file status as satic file in database.
		$settings['is_static_sw'] = 1;
		
		// Write settings back to database.
		update_option( 'aione_pwa_settings', $settings );
		
		return true;
	}
	
	return false;
}
function aione_pwa_delete_sw() {
	return aione_pwa_delete( aione_pwa_sw( 'abs' ) );
}
function aione_pwa_get_offline_page() {
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	return get_permalink( $settings['offline_page'] ) ? aione_pwa_httpsify( get_permalink( $settings['offline_page'] ) ) : aione_pwa_httpsify( aione_pwa_get_bloginfo( 'sw' ) );
}
function aione_pwa_sw_template() {
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	// Start output buffer. Everything from here till ob_get_clean() is returned
	ob_start();  ?>
'use strict';

/**
 * Service Worker of AionePWA
 */
 
const cacheName = '<?php echo parse_url( get_bloginfo( 'url' ), PHP_URL_HOST ) . '-aione-pwa-' . AIONE_PWA_VERSION; ?>';
const startPage = '<?php echo aione_pwa_get_start_url(); ?>';
const offlinePage = '<?php echo aione_pwa_get_offline_page(); ?>';
const filesToCache = [<?php echo apply_filters( 'aione_pwa_sw_files_to_cache', 'startPage, offlinePage' ); ?>];
const neverCacheUrls = [<?php echo apply_filters( 'aione_pwa_sw_never_cache_urls', '/\/wp-admin/,/\/wp-login/,/preview=true/' ); ?>];

// Install
self.addEventListener('install', function(e) {
	console.log('AionePWA service worker installation');
	e.waitUntil(
		caches.open(cacheName).then(function(cache) {
			console.log('AionePWA service worker caching dependencies');
			filesToCache.map(function(url) {
				return cache.add(url).catch(function (reason) {
					return console.log('AionePWA: ' + String(reason) + ' ' + url);
				});
			});
		})
	);
});

// Activate
self.addEventListener('activate', function(e) {
	console.log('AionePWA service worker activation');
	e.waitUntil(
		caches.keys().then(function(keyList) {
			return Promise.all(keyList.map(function(key) {
				if ( key !== cacheName ) {
					console.log('AionePWA old cache removed', key);
					return caches.delete(key);
				}
			}));
		})
	);
	return self.clients.claim();
});

// Fetch
self.addEventListener('fetch', function(e) {
	
	// Return if the current request url is in the never cache list
	if ( ! neverCacheUrls.every(checkNeverCacheList, e.request.url) ) {
	  console.log( 'AionePWA: Current request is excluded from cache.' );
	  return;
	}
	
	// Return if request url protocal isn't http or https
	if ( ! e.request.url.match(/^(http|https):\/\//i) )
		return;
	
	// Return if request url is from an external domain.
	if ( new URL(e.request.url).origin !== location.origin )
		return;
	
	// For POST requests, do not use the cache. Serve offline page if offline.
	if ( e.request.method !== 'GET' ) {
		e.respondWith(
			fetch(e.request).catch( function() {
				return caches.match(offlinePage);
			})
		);
		return;
	}
	
	// Revving strategy
	if ( e.request.mode === 'navigate' && navigator.onLine ) {
		e.respondWith(
			fetch(e.request).then(function(response) {
				return caches.open(cacheName).then(function(cache) {
					cache.put(e.request, response.clone());
					return response;
				});  
			})
		);
		return;
	}

	e.respondWith(
		caches.match(e.request).then(function(response) {
			return response || fetch(e.request).then(function(response) {
				return caches.open(cacheName).then(function(cache) {
					cache.put(e.request, response.clone());
					return response;
				});  
			});
		}).catch(function() {
			return caches.match(offlinePage);
		})
	);
});

// Check if current url is in the neverCacheUrls list
function checkNeverCacheList(url) {
	if ( this.match(url) ) {
		return false;
	}
	return true;
}
<?php return apply_filters( 'aione_pwa_sw_template', ob_get_clean() );
}