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

When creating a new queue, all its entries should be stored in the protected `$entries` property as an instance of a class implementing the `\Geniem\Queue\Interfaces\EntryInterface`. The actual entry data is untyped, but we encourage keeping the type consistent withing a specific queue. The actual entry handler can be created by implementing the `\Geniem\Queue\Interfaces\FetchableInterface` interface.

The queue creation is handled with the `Geniem\Queue\QueueCreator` class. This ensures all dependecies are strictly typed and injected in place.

## Dequeueing

The dequeue process is handled by the `Geniem\Queue\Dequeuer`. It is always instantiated with a [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger. All dequeues are logged in a standardized way through the given logger. The logger can be replaced with a filter.

```php
// Replace globally.
add_filter( 'wpq_get_dequeue_logger', function( $logger ) {
    return new MyPSR3Logger();
} );
```

## Usage

To create a new queue, you must first define an entry fetcher and an entry handler. The entry fetcher is defined by the `FetchableInterface` and the handler is defined by `HandleableInterface`. Implement these interfaces in your corresponding classes.

### Examples

In the following examples we create a simple fetcher returning an array of entries containing a simple string in the data. Then finally the handler just logs the data into PHP error log. The whole process is completed with defining the queue for WordPress Queue through filters. After this, the queue is accessible through WP-CLI.

#### Fetcher example

```php
class MyFetcher implements \Geniem\Queue\Interfaces\FetchableInterface {

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
class MyHandler implements \Geniem\Queue\Interfaces\HandleableInterface {

    public function handle( \Geniem\Queue\Interfaces\EntryInterface $entry ) {
        error_log( 'Entry data: ' . $entry->get_data() );
    }

}
```

### Usage example

To allow WordPress Queue to find our example queue by its name "my_queue", we must define it by adding it to the queue container in the `wpq_add_queue` hook. Here we use the default RedisQueue as our queue instance. To add a new queue into the container call the `add` method. To replace an existing one with the same name, call the `replace` method.

```php
do_action( 'wpq_add_queue', function( \Psr\Container\ContainerInterface $container ) {
    $redis_queue = new Geniem\Queue\Instance\RedisQueue( 'my_queue' );
    $redis_queue->set_entry_fetcher( new MyFetcher() );
    $redis_queue->set_entry_handler( new MyHandler() );
    
    $container->add( $redis_queue );
}, 1, 1 );
```

### WP CLI commands

After the queue has been instantiated and added to the queue container, you can start to interact with it through WP CLI.

#### Create

To create the queue, call the WP CLI `create` command. This will run the entry fetcher if one is set for the queue and (re)create the queue by saving it with the newly fetched entries.

```
wp queue create my_queue
```

#### Dequeue

After creating, you can dequeue a single entry from the queue:

```
wp queue dequeue my_queue
```

#### Fetch

To fetch more entries to the queue, run the `fetch` command. This command will try to call the fetcher's `fetch` method and append the found entries to the queue.

```
wp queue fetch my_queue
```

## Contributors

- [Ville Siltala](https://github.com/villesiltala)

## License

Code released under the [MIT License](./LICENSE).
