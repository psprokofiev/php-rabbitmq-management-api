PHP RabbitMQ Management Api
===========================

A simple object oriented wrapper for the [RabbitMQ Management HTTP Api](https://www.rabbitmq.com/management.html) in PHP

Installation
------------

Installable through composer via:

```bash
$ composer require psprokofiev/rabbitmq-management-api
```

Basic Usage
-----------

Define host and credentials via .env,  
or pass its into Client constructor.

```dotenv
RABBITMQ_MANAGMENT_BASEURI=
RABBITMQ_MANAGMENT_USERNAME=
RABBITMQ_MANAGMENT_PASSWORD=
```

```php
<?php

use RabbitMQ\ManagementApi\Client;

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Client(); 
// or new Client('host', 'username', 'password');

$queue = $client->queues()->get('/', 'sample-messages-queue');
$response = $client->exchanges()->publish('/', 'sample-messages', [
    'properties' => [],
    'routing_key' => '',
    'payload' => 'This is a test',
    'payload_encoding' => 'string'
]);

if ($response['routed']) {
    print 'Message delivered';
}
```
