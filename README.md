![geniem-github-banner](https://cloud.githubusercontent.com/assets/5691777/14319886/9ae46166-fc1b-11e5-9630-d60aa3dc4f9e.png)

# WordPress Queue

[![Build Status](https://travis-ci.org/devgeniem/wp-queue.svg?branch=master)](https://travis-ci.org/devgeniem/wp-queue) [![Latest Stable Version](https://poser.pugx.org/devgeniem/wp-queue/v/stable)](https://packagist.org/packages/devgeniem/wp-queue) [![Total Downloads](https://poser.pugx.org/devgeniem/wp-queue/downloads)](https://packagist.org/packages/devgeniem/wp-queue) [![Latest Unstable Version](https://poser.pugx.org/devgeniem/wp-queue/v/unstable)](https://packagist.org/packages/devgeniem/wp-queue) [![License](https://poser.pugx.org/devgeniem/wp-queue/license)](https://packagist.org/packages/devgeniem/wp-queue)

WordPress Queue is a modular library for managing queued tasks in WordPress.

## Installation

Install with Composer:

```
composer config repositories.wp-queue git git@github.com:devgeniem/wp-queue.git
composer require devgeniem/wp-queue
```

The plugin initializes itself in the `plugins_loaded` hook. Your code should start using the plugin features after this hook is run by WordPress. An alternative is to customize the plugin initialization hook with the provided filter `wqp_init_hook`. See [plugin.php](./plugin.php) for details.

## Functionalities

### Queue structure

A queue consists of its name, an entry handler and some entries. Entries are statically typed objects holding the data. An entry handler is the controller called when dequeueing a single entry. The name is used to identify the queue, for instance, with WP-CLI.

Queue functionalities are defined with the `\Geniem\Queue\Interfaces\QueueInterface` interface. You can create your own queue controller by implementing this interface. We provide an abstract class, `Geniem\Queue\Instance\Base`, that you can extend as a starting point.

### Queue creation

To create a queue, call the implementation constructor with your unique name for the queue. To let WordPress Queue to know about your queue, pass it to the queue container via the `wpq_add_queue` hook. The queue container implements the [PSR-11](https://www.php-fig.org/psr/psr-11/) container interface.

Here is an example of creating a Redis queue:

```php
add_action( 'wpq_add_queue', function( \Psr\Container\ContainerInterface $container ) {
    $my_queue = new Geniem\Queue\Instance\RedisQueue( 'my_queue' );
    // ...
    $container->add( $my_queue );
}, 1, 1 );
```

When creating a new queue, all its entries should be stored in the protected `$entries` property as an instance of a class implementing the `\Geniem\Queue\Interfaces\EntryInterface`. The actual entry data is untyped, but we encourage keeping the type consistent withing a specific queue. The actual entry handler can be created by implementing the `\Geniem\Queue\Interfaces\EntryFetcherInterface` interface.

The queue creation is handled with the `Geniem\Queue\QueueCreator` class. This ensures all dependecies are strictly typed and injected in place.

### Accessing a queue

To interact manually with your previously created queue, you can access it through the plugin's queue container. To access the plugin, you can use the global helper function `wpq()`. It returns the plugin singleton.

```php
$my_queue = wpq()->get_queue_container()->get( 'my_queue' );
```

### Entry handling

A queue consists of a list of entries implementing the `\Geniem\Queue\Interfaces\EntryInterface` interface. WordPress Queue is agnostic about the handling of entries. It is left for you to implement. The dequeuer uses a try-catch block around calling the handle method. Thus, your handler should throw errors if the handling process fails. This enables logging errors and deciding whether to proceed with the dequeue or to rollback to the previous state in the queue. Here is an example of a simple handler that just logs the data in the queue.

```php
class MyHandler implements \Geniem\Queue\Interfaces\EntryHandlerInterface {

    public function handle( \Geniem\Queue\Interfaces\EntryInterface $entry ) {
        error_log( 'Entry data: ' . $entry->get_data() );
    }

}
```

After creating the handler, pass an instance to your queue:

```php
add_action( 'wpq_add_queue', function( \Psr\Container\ContainerInterface $container ) {
    $my_queue = new Geniem\Queue\Instance\RedisQueue( 'my_queue' );

    // Set the handler.
    $my_queue->set_entry_handler( new Myhandler() );

    $container->add( $my_queue );
}, 1, 1 );
```

### Entry fetching and enqueueing

WordPress Queue introduces a concept of a 'fetcher'. A fetcher is an instance with a functionality of fetching more entries for a queue. Fetchers should implement the `\Geniem\Queue\Interfaces\EntryFetcherInterface` interface. One example of using a fetcher is to integrate your queue to an external API providing some data to be passed to the queue.

The `Geniem\Queue\Enqueuer` class calls the fetcher's fetch method and wraps the resulting array items into entry objects if not already wrapped. After this, each entry is run through the `enqueue` method of the given queue instance.

Here is a simple fetcher example always returning the same array of entries.

```php
class MyFetcher implements \Geniem\Queue\Interfaces\EntryFetcherInterface {

    public function fetch() : ?array {
        $entry_data = [
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
        ];

        return $entries;
    }

}
```

And then, adding an fetcher instance for your queue:

```php
add_action( 'wpq_add_queue', function( \Psr\Container\ContainerInterface $container ) {
    $my_queue = new Geniem\Queue\Instance\RedisQueue( 'my_queue' );

    // Set the fetcher.
    $my_queue->set_entry_fetcher( new MyFetcher() );

    $container->add( $my_queue );
}, 1, 1 );
```

The fetching process is run with the `Geniem\Queue\Enqueuer`. You can run it with the WP-CLI command or if you want to do it manually in PHP, run the following:

```
$enqueuer = new \Geniem\Queue\Enqueuer();
$enqueuer->fetch( $my_queue );
```

To enqueue a single entry, call the `enqueue` method and pass an entry:

```
$entry = ( new \Geniem\Queue\Entry() )->set_data( 'Just a string' );
$enqueuer = new \Geniem\Queue\Dequeuer();
$enqueuer->enqueue( $my_queue, $entry );
```

_Note! You can call the enqueue method directly from your queue instance, but we recommend using the enqueuer for generalized error handling and logging._

### Dequeueing

A dequeue process handles the first entry in a queue. If the handing is successful, the entry is popped out from the queue. Note that the final implementation of the queue processsing is dependant of the queue class.

The dequeue process is handled by the `Geniem\Queue\Dequeuer`. When using the WP-CLI commands, this is done automatically. If you want to manually run the dequeu, do the following:

```
$dequeuer = new \Geniem\Queue\Dequeuer();
$dequeuer->dequeue( $my_queue );
```

_Note! You can call the dequeue method directly from your queue instance, but we recommend using the dequeuer for generalized error handling and logging._

## Example

In the following examples we create a simple fetcher returning an array of entries containing a simple string in the data. Then finally the handler just logs the data into PHP error log. The whole process is completed with creating a Redis queue. After this, the queue is accessible through WP-CLI.

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

### Usage example

To allow WordPress Queue to find our example queue by its name "my_queue", we must define it by adding it to the queue container in the `wpq_add_queue` hook. Here we use the default RedisQueue as our queue instance. To add a new queue into the container call the `add` method. To replace an existing one with the same name, call the `replace` method.

```php
add_action( 'wpq_add_queue', function( \Psr\Container\ContainerInterface $container ) {
    $redis_queue = new Geniem\Queue\Instance\RedisQueue( 'my_queue' );
    $redis_queue->set_entry_fetcher( new MyFetcher() );
    $redis_queue->set_entry_handler( new MyHandler() );

    $container->add( $redis_queue );
}, 1, 1 );
```

## WP-CLI commands

After the queue has been instantiated and added to the queue container, you can start to interact with it through WP-CLI.

### Create

To create the queue, call the WP-CLI `create` command. This will run the entry fetcher if one is set for the queue and (re)create the queue by saving it with the newly fetched entries.

```
wp queue create my_queue
```

### Dequeue

After creating, you can dequeue a single entry from the queue:

```
wp queue dequeue my_queue
```

### Fetch

To fetch more entries to the queue, run the `fetch` command. This command will try to call the fetcher's `fetch` method and append the found entries to the queue.

```
wp queue fetch my_queue
```

## Tests

The plugin is tested locally with [PHPUnit](https://phpunit.de/) and automatically with the GitHub integration of [Travic CI](https://travis-ci.org/). For local testing we provide a Dockerfile configuration to run PHPUnit inside a Docker container. The container also contains [pywatch](https://pypi.org/project/pywatch/) for watching changes tests and then rerunning them. To start running tests locally, navigate to your plugin directory and follow this process:

```
# Install local composer packages.
composer install
# Build and tag the container.
docker build . -t phptest:7.4
# Run the container and watch changes.
docker run --rm -it -v $(pwd):/opt phptest:7.4 "php ./vendor/bin/phpunit" ./tests/*.php
```

## Contributors

- [Ville Siltala](https://github.com/villesiltala)

