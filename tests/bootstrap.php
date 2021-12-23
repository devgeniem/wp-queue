<?php
/**
 * Bootsrtap PHPUnit tests.
 */

use M6Web\Component\RedisMock\RedisMockFactory;

/**
 * Define mocks for WP functionalities.
 */
function wp_mocks() {
    WP_Mock::setUp();

    // Mock the plugin file data.
    WP_Mock::userFunction( 'get_file_data',
        [
            'return' => [
                'Version' => '1.0.0-beta',
            ]
        ]
    );

    // Mock the plugin dir url.
    WP_Mock::userFunction( 'plugin_dir_url',
        [
            'return' => '/wp-content/plugins/wp-queue'
        ]
    );

    // Replace the Redis instance with a mocked filter.
    $factory          = new RedisMockFactory();
    $redis_mock_class = $factory->getAdapterClass( 'Predis\Client' );
    $redis_mock       = new $redis_mock_class();

    \WP_Mock::onFilter( 'wpq_redis_instance' )
        ->with( null )
        ->reply( $redis_mock );
}
wp_mocks();

add_filter( 'wqp_init_hook', function () { return null; } );

require_once dirname( __DIR__ ) . '/plugin.php';
