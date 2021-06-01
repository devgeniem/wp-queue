<?php
/**
 * Test for the dequeuer class.
 */

use Geniem\Queue\Enqueuer;
use Geniem\Queue\Entry;
use Geniem\Queue\Mock\MockQueue;
use Geniem\Queue\Mock\MockEntryFetcher;
use Geniem\Queue\Mock\MockEntryHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class TestDequeuer
 */
class TestDequeuer extends TestCase {

    public function test_dequeue() {
        $queue = new MockQueue(
            'enqueue_test_1',
            new MockEntryFetcher( [ 'testdata' ] ),
            new MockEntryHandler()
        );

        $entry1 = ( new Entry() )->set_data( 'test 1' );
        $entry2 = ( new Entry() )->set_data( 'test 2' );
        $queue->enqueue( $entry1 );
        $queue->enqueue( $entry2 );

        $dequeued1 = $queue->dequeue();
        $dequeued2 = $queue->dequeue();
        $just_null = $queue->dequeue();

        $this->assertSame( $entry1->get_data(), $dequeued1->get_data() );
        $this->assertSame( $entry2->get_data(), $dequeued2->get_data() );
        $this->assertNull( $just_null );
    }
}