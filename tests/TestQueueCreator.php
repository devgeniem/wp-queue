<?php
/**
 * Tests for the queue creator class.
 */

use Geniem\Queue\Mock\MockQueue;
use Geniem\Queue\Mock\MockEntryFetcher;
use Geniem\Queue\Mock\MockEntryHandler;
use Geniem\Queue\QueueCreator;

/**
 * Class TestQueueCreator
 */
class TestQueueCreator extends \PHPUnit\Framework\TestCase {

    public function test_create() {
        $queue = new MockQueue(
            'create_test',
            new MockEntryFetcher( [ 'test1', 'test2' ] ),
            new MockEntryHandler()
        );

        $creator = new QueueCreator( $queue );
        $creator->create();

        $this->assertEquals( 2, $queue->size() );
        $this->assertTrue( $queue->is_saved() );
    }

}
