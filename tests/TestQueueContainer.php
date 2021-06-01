<?php
/**
 * Tests for the queue container functionalities.
 */

use Geniem\Queue\Mock\MockQueue;
use Geniem\Queue\Mock\MockEntryFetcher;
use Geniem\Queue\Mock\MockEntryHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueContainerTest
 */
class QueueContainerTest extends TestCase {

    /**
     * Test the has method.
     */
    public function test_container_has() {
        $plugin = wpq();
        $container = $plugin->get_queue_container();

        $queue = new MockQueue(
            'test1',
            new MockEntryFetcher( [] ),
            new MockEntryHandler()
        );

        $container->add( $queue );

        $this->assertTrue( $container->has( 'test1' ) );
    }

    /**
	 * Test the add method.
	 */
	public function test_container_add() {
        $plugin = wpq();
        $container = $plugin->get_queue_container();

        $queue = new MockQueue(
            'test2',
            new MockEntryFetcher( [] ),
            new MockEntryHandler()
        );

        $container->add( $queue );

        $with_same_name = new MockQueue(
            'test2',
            new MockEntryFetcher( [] ),
            new MockEntryHandler()
        );

        $container->add( $with_same_name );

        $current = $container->get( $queue->get_name() );
        $this->assertSame( $current, $queue );
        $this->assertNotSame( $current, $with_same_name );
	}

    /**
     * Test the get method.
     */
    public function test_container_get() {
        $plugin = wpq();
        $container = $plugin->get_queue_container();

        $first_queue = new MockQueue(
            'test3',
            new MockEntryFetcher( [ 1, 2, 3 ] ),
            new MockEntryHandler()
        );

        $container->add( $first_queue );

        $second_queue = new MockQueue(
            'test4',
            new MockEntryFetcher( [ 4, 5, 6 ] ),
            new MockEntryHandler()
        );
        $container->add( $second_queue );

        $get_first = $container->get( $first_queue->get_name() );
        $get_second = $container->get( $second_queue->get_name() );

        $this->assertSame( $first_queue, $get_first );
        $this->assertSame( $second_queue, $get_second );
        $this->assertNotSame( $first_queue, $get_second );
        $this->assertNotSame( $second_queue, $get_first );
    }

    /**
     * Test the replace method.
     */
    public function test_container_replace() {
        $plugin = wpq();
        $container = $plugin->get_queue_container();

        $replacable = new MockQueue(
            'replacable',
            new MockEntryFetcher( [ 1, 2, 3 ] ),
            new MockEntryHandler()
        );

        $container->add( $replacable );

        $replace_with = new MockQueue(
            'replacable',
            new MockEntryFetcher( [ 4, 5, 6 ] ),
            new MockEntryHandler()
        );

        $container->replace( $replace_with );

        $current = $container->get( 'replacable' );

        $this->assertSame( $replace_with, $current );
        $this->assertNotSame( $current, $replacable );
    }
}
