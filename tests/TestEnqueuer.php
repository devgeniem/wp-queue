<?php
/**
 * Test for the enqueuer class.
 */

use Geniem\Queue\Enqueuer;
use Geniem\Queue\Entry;
use Geniem\Queue\Mock\MockQueue;
use Geniem\Queue\Mock\MockEntryFetcher;
use Geniem\Queue\Mock\MockEntryHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class TestEnqueuer
 */
class TestEnqueuer extends TestCase {

    public function test_enqueue() {
        $queue = new MockQueue(
            'enqueue_test_1',
            new MockEntryFetcher( [ 'testdata' ] ),
            new MockEntryHandler()
        );

        $entry = ( new Entry() )->set_data( 'test 2' );

        $enqueuer = new Enqueuer();
        $enqueuer->enqueue( $queue, $entry );

        $this->assertEquals( 1, $queue->size() );

        $dequeued = $queue->dequeue();

        $this->assertSame( $entry, $dequeued );
    }

    public function test_fetch() {
        $test_data = 'test';

        $queue = new MockQueue(
            'enqueue_test_2',
            new MockEntryFetcher( [ $test_data ] ),
            new MockEntryHandler()
        );

        $enqueuer = new Enqueuer();
        $enqueuer->fetch( $queue );

        $this->assertEquals( 1, $queue->size() );

        $dequeued = $queue->dequeue();

        $this->assertEquals( $test_data, $dequeued->get_data() );
    }
}