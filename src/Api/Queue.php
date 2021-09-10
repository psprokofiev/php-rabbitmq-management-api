<?php

namespace RabbitMq\ManagementApi\Api;

use JsonException;

/**
 * Queue
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class Queue extends AbstractApi
{
    /**
     * A list of all queues.
     *
     * OR
     *
     * A list of all queues in a given virtual host.
     *
     * @param  string|null  $vhost
     *
     * @return array
     * @throws JsonException
     */
    public function all($vhost = null)
    {
        return $vhost
            ? $this->client->send(sprintf('/api/queues/%s', urlencode($vhost)))
            : $this->client->send('/api/queues');
    }

    /**
     * An individual queue.
     *
     * @param  string  $vhost
     * @param  string  $name
     *
     * @return array
     * @throws JsonException
     */
    public function get($vhost, $name)
    {
        return $this->client->send(
            sprintf('/api/queues/%s/%s', urlencode($vhost), urlencode($name))
        );
    }

    /**
     * To PUT a queue, you will need a body looking something like this:
     *
     * {
     *     "auto_delete": false,
     *     "durable": true,
     *     "arguments":[],
     *     "node":"rabbit@smacmullen"
     * }
     *
     * All keys are optional.
     *
     * @param  string  $vhost
     * @param  string  $name
     * @param  array  $queue
     *
     * @return array
     * @throws JsonException
     */
    public function create($vhost, $name, array $queue)
    {
        return $this->client->send(
            sprintf('/api/queues/%s/%s', urlencode($vhost), urlencode($name)),
            'PUT',
            [],
            $queue
        );
    }

    /**
     * @param  string  $vhost
     * @param  string  $name
     * @param  bool  $ifEmpty
     * @param  bool  $ifUnused
     *
     * @return array
     * @throws JsonException
     */
    public function delete($vhost, $name, $ifEmpty = false, $ifUnused = false)
    {
        $queryString = [];

        if ($ifEmpty) {
            $queryString['if-empty'] = 'true';
        }

        if ($ifUnused) {
            $queryString['if-unused'] = 'true';
        }

        return $this->client->send(sprintf('/api/queues/%s/%s?%s', urlencode($vhost), urlencode($name), http_build_query($queryString)), 'DELETE');
    }

    /**
     * A list of all bindings on a given queue.
     *
     * @param  string  $vhost
     * @param  string  $queue
     *
     * @return array
     * @throws JsonException
     */
    public function bindings($vhost, $queue)
    {
        return $this->client->send(sprintf('/api/queues/%s/%s/bindings', urlencode($vhost), urlencode($queue)));
    }

    /**
     * Contents of a queue. DELETE to purge. Note you can't GET this.
     *
     * @param  string  $vhost
     * @param  string  $name
     *
     * @return array
     * @throws JsonException
     */
    public function purgeMessages($vhost, $name)
    {
        return $this->client->send(sprintf('/api/queues/%s/%s/contents', urlencode($vhost), urlencode($name)), 'DELETE');
    }

    /**
     * Get messages from a queue. (This is not an HTTP GET as it will alter the state of the queue.) You should post a
     * body looking like:
     *
     * {"count":5,"requeue":true,"encoding":"auto","truncate":50000}
     *
     * count controls the number of messages to get. You may get fewer messages than this if the queue cannot
     * immediately provide them.
     *
     * requeue determines whether the messages will be removed from the queue. If requeue is true they will be
     * requeued - but their position in the queue may change and their redelivered flag will be set.
     *
     * encoding must be either "auto" (in which case the payload will be returned as a string if it is valid UTF-8, and
     * base64 encoded otherwise), or "base64" (in which case the payload will always be base64 encoded).
     *
     * If truncate is present it will truncate the message payload if it is larger than the size given (in bytes).
     *
     * truncate is optional; all other keys are mandatory.
     *
     * Please note that the publish / get paths in the HTTP API are intended for injecting test messages, diagnostics
     * etc - they do not implement reliable delivery and so should be treated as a sysadmin's tool rather than a general
     * API for messaging.
     *
     * @param  string  $vhost
     * @param  string  $name
     * @param  integer  $count
     * @param  bool  $requeue
     * @param  string  $encoding
     * @param  null|integer  $truncate
     *
     * @return array
     * @throws JsonException
     */
    public function retrieveMessages($vhost, $name, $count = 5, $requeue = true, $encoding = 'auto', $truncate = null)
    {
        $parameters = array(
            'count'    => $count,
            'requeue'  => $requeue,
            'encoding' => $encoding
        );

        if ($truncate) {
            $parameters['truncate'] = $truncate;
        }

        return $this->client->send(sprintf('/api/queues/%s/%s/get', urlencode($vhost), urlencode($name)), 'POST', [], $parameters);
    }
}
