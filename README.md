# WordPress Queue

WordPress Queue is a modular library for managing queued tasks in WordPress.

## Installation

Install with Composer:

```
composer config repositories.wp-queue git git@github.com:devgeniem/wp-queue.git
composer require devgeniem/wp-queue
```

## Queue structure

A queue consists of its name, entry handler and entries. Entries are typed objects holding the data. An entry handler is the controller called when dequeueing a single entry. The name is used to identify the queue, for instance, with WP-CLI.

Queue functionalities are defined with the `\Geniem\Queue\Interfaces\StorageInterface` interface. You can create your own queue storage by implementing this interface. We provide an abstract class, `Geniem\Queue\Storage\Base`, that you can extend as a starting point.

## Queue creation

When creating a new queue, all its entries should be stored in the protected `$entries` property as an instance of a class implementing the `\Geniem\Queue\Interfaces\EntryInterface`. The actual entry data is untyped, but we encourage keeping the type consistent withing a specific queue. The actual entry handler can be created by implementing the `\Geniem\Queue\Interfaces\EntryFetcherInterface` interface.

The queue creation is handled with the `Geniem\Queue\QueueCreator` class. This ensures all dependecies are strictly typed and injected in place.

## Dequeueing

The dequeue process is handled by the `Geniem\Queue\Dequeuer`. It is always instantiated with a [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger. All dequeues are logged in a standardized way through the given logger. The logger can be replaced with a filter globally or for a specific queue.

```php
// Replace globally.
add_filter( 'wpq_get_dequeue_logger', function( $logger ) {
    return new MyPSR3Logger();
} )
// Replace for a specific queue.
add_filter( 'wpq_get_dequeue_logger_my_queue', function( $logger ) {
    return new MyPSR3Logger();
} )
```

## Usage

To create a new queue, you must first define an entry fetcher and an entry handler. The entry fetcher is defined by the `EntryFetcherInterface` and the handler is defined by `EntryHandlerInterface`. Implement these interfaces in your corresponding classes.

### Examples

In the following examples we create a simple fetcher returning an array of entries containing a simple string in the data. Then finally the handler just logs the data into PHP error log. The whole process is completed with defining the queue for WordPress Queue through filters. After this, the queue is accessible through WP-CLI.

#### Fetcher example

```php
class MyFetcher implements \Geniem\Queue\Interfaces\EntryFetcherInterface {

    public function fetch() : ?array {
        $entry_data = [
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
        ];

        $entries = array_map( function( $data ) {
            $entry = new \Geniem\Queue\Entry();
            $entry->set_data( $data );
            return $entry;
        }, $entry_data );

        return $entries;
    }

}
```

#### Handler example

```php
class MyHandler implements \Geniem\Queue\Interfaces\EntryHandlerInterface {

    public function handle( \Geniem\Queue\Interfaces\EntryInterface $entry ) {
        error_log( 'Entry data: ' . $entry->get_data() );
    }

}
```

### WP-CLI example

To allow WordPress Queue to find our example queue by its name "my_queue", we must define it through a filter. Here we use the default RedisCache as our queue storage.

```php
add_filter( 'wpq_get_queue_my_queue', function() {
    $redis_queue = new Geniem\Queue\Storage\RedisCache();
    $redis_queue->set_entry_fetcher( new MyFetcher() );
    $redis_queue->set_entry_handler( new MyHandler() );
    return $redis_queue;
}, 1, 0 );
```

Creating the queue:

```
wp queue create my_queue
```

Dequeueing a single entry from the queue:

```
wp queue dequeue my_queue
```

## Contributors

- [Ville Siltala](https://github.com/villesiltala)

## License

Code released under the [MIT License](https://github.com/twbs/bootstrap/blob/master/LICENSE).
