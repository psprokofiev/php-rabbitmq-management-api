<?php

namespace RabbitMq\ManagementApi;

use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Str;
use JsonException;

class Client
{
    /** @var PendingRequest */
    protected $client;

    /**
     * @param  string  $host
     * @param  string  $username
     * @param  string  $password
     */
    public function __construct($host = 'http://localhost:15672', $username = 'guest', $password = 'guest')
    {
        $this->client = (new Http)
            ->baseUrl(
                env('RABBITMQ_MANAGMENT_BASEURI', $host)
            )->withBasicAuth(
                env('RABBITMQ_MANAGMENT_USERNAME', $username),
                env('RABBITMQ_MANAGMENT_PASSWORD', $password)
            )->withHeaders([
                'Content-Type' => 'application/json'
            ]);
    }

    /**
     * Declares a test queue, then publishes and consumes a message. Intended for use by monitoring tools. If
     * everything is working correctly, will return HTTP status 200 with body:
     *
     * {"status":"ok"}
     *
     * Note: the test queue will not be deleted (to to prevent queue churn if this is repeatedly pinged).
     *
     * @param  string  $vhost
     *
     * @return array
     * @throws JsonException
     */
    public function alivenessTest($vhost)
    {
        return $this->send(sprintf('/api/aliveness-test/%s', urlencode($vhost)));
    }

    /**
     * Various random bits of information that describe the whole system.
     *
     * @return array
     * @throws JsonException
     */
    public function overview()
    {
        return $this->send('/api/overview');
    }

    /**
     * A list of extensions to the management plugin.
     *
     * @return array
     * @throws JsonException
     */
    public function extensions()
    {
        return $this->send('/api/extensions');
    }

    /**
     * The server definitions - exchanges, queues, bindings, users, virtual hosts, permissions and parameters.
     *
     * Everything apart from messages. POST to upload an existing set of definitions. Note that:
     *
     * - The definitions are merged. Anything already existing is untouched.
     * - Conflicts will cause an error.
     * - In the event of an error you will be left with a part-applied set of definitions.
     *
     * For convenience, you may upload a file from a browser to this URI (i.e. you can use multipart/form-data as well as
     * application/json) in which case the definitions should be uploaded as a form field named "file".
     *
     * @return mixed
     * @throws JsonException
     */
    public function definitions()
    {
        return $this->send('/api/definitions');
    }

    /**
     * @return Api\Connection
     */
    public function connections()
    {
        return new Api\Connection($this);
    }

    /**
     * @return Api\Channel
     */
    public function channels()
    {
        return new Api\Channel($this);
    }

    /**
     * @return Api\Consumer
     */
    public function consumers()
    {
        return new Api\Consumer($this);
    }

    /**
     * @return Api\Exchange
     */
    public function exchanges()
    {
        return new Api\Exchange($this);
    }

    /**
     * @return Api\Queue
     */
    public function queues()
    {
        return new Api\Queue($this);
    }

    /**
     * @return Api\Vhost
     */
    public function vhosts()
    {
        return new Api\Vhost($this);
    }

    /**
     * @return Api\Binding
     */
    public function bindings()
    {
        return new Api\Binding($this);
    }

    /**
     * @return Api\User
     */
    public function users()
    {
        return new Api\User($this);
    }

    /**
     * @return Api\Permission
     */
    public function permissions()
    {
        return new Api\Permission($this);
    }

    /**
     * @return Api\Parameter
     */
    public function parameters()
    {
        return new Api\Parameter($this);
    }

    /**
     * @return Api\Policy
     */
    public function policies()
    {
        return new Api\Policy($this);
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function whoami()
    {
        return $this->send('/api/whoami');
    }

    /**
     * @param  string  $endpoint
     * @param  string  $method
     * @param  array  $headers
     * @param  null  $body
     *
     * @return mixed
     * @throws JsonException
     */
    public function send(string $endpoint, $method = 'GET', array $headers = [], $body = null)
    {
        $method = Str::lower($method);

        if ($body !== null) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $response = $this->client
            ->withHeaders($headers)
            ->$method($endpoint, $body);

        return $response->json();
    }
}
