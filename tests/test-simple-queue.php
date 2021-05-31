<?php
/**
 * Tests plugin features by testing the simple queue with mocks.
 */

use Geniem\Queue\Instance\SimpleQueue;
use Geniem\Queue\Interfaces\QueueInterface;
use Geniem\Queue\Mock\MockEntryFetcher;
use Geniem\Queue\Mock\MockEntryHandler;

/**
 * Class SimpleQueueTest
 */
class SimpleQueueTest extends WP_UnitTestCase {

    /**
     * Holds the queue instance.
     *
     * @var QueueInterface
     */
    protected $queue;

    /**
	 * Test queue container.
	 */
	public function test_container_has() {
	    $plugin = wpq();
	    $container = $plugin->get_queue_container();

        $queue = new SimpleQueue(
            'test',
            new MockEntryFetcher( [] ),
            new MockEntryHandler()
        );

	    $container->add( $queue );

	    $this->assertTrue( $container->has( $queue->get_name() ) );
	}

    /**
     * Test queue container.
     */
    public function test_container_get() {
        $plugin = wpq();
        $container = $plugin->get_queue_container();

        $sample_queue = new SimpleQueue(
            'sample',
            new MockEntryFetcher( [ 1, 2, 3 ] ),
            new MockEntryHandler()
        );

        $container->add( $sample_queue );

        $get_sample = $container->get( $sample_queue->get_name() );

        $other_queue = new SimpleQueue(
            'other',
            new MockEntryFetcher( [ 4, 5, 6 ] ),
            new MockEntryHandler()
        );

        $this->assertSame( $sample_queue, $get_sample );
        $this->assertNotSame( $other_queue, $get_sample );
    }
}
