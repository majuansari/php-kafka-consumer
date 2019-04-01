# PHP KAFKA CONSUMER

A consumer of Kafka in PHP

## Install

1. Install [librdkafka c library](https://github.com/edenhill/librdkafka)

    ```bash
    $ cd /tmp
    $ mkdir librdkafka
    $ cd librdkafka
    $ git clone https://github.com/edenhill/librdkafka.git .
    $ ./configure
    $ make
    $ make install
    ```

2. Install the [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) PECL extension

    ```bash
    $ pecl install rdkafka
    ```

3. Add the following to your php.ini file to enable the php-rdkafka extension

    `extension=rdkafka.so`

4. Install this package via composer using:

    `composer require arquivei/php-kafka-consumer`
    
## Usage 

```php
<?php

require_once 'vendor/autoload.php';

use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config\Sasl;
use Kafka\Consumer\Entities\Config\MaxAttempt;

class DefaultConsumer implements Consumer
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        print 'Init: ' . date('Y-m-d H:i:s') . PHP_EOL;
        sleep(2);
        print 'Finish: ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

$config = new Config(
    new Sasl('username', 'pasword', 'mechanisms'),
    'topic',
    'broker:port',
    1,
    'php-kafka-consumer-group-id',
    DefaultConsumer::class,
    new MaxAttempt(1),
    'security-protocol'
);

(new \Kafka\Consumer\Consumer($config))->consume();

```

## Usage with Laravel

You need to add the `php-kafka-consig.php` in `config` path:

```php
<?php

return [
    'topic' => 'topic',
    'broker' => 'broker',
    'groupId' => 'group-id',
    'securityProtocol' => 'security-protocol',
    'sasl' => [
        'mechanisms' => 'mechanisms',
        'username' => 'username',
        'password' => 'password',
    ],
];

```

Use the command to execute the consumer:

```bash
$ php artisan arquivei:php-kafka-consumer --consumer="App\Consumers\YourConsumer" --commit=1
```

## TODO

- Add unit tests
