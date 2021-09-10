<?php

namespace RabbitMq\ManagementApi\Api;

use JsonException;

/**
 * Channel
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class Channel extends AbstractApi
{
    /**
     * A list of all open channels.
     *
     * @return array
     * @throws JsonException
     */
    public function all()
    {
        return $this->client->send(
            '/api/channels'
        );
    }

    /**
     * Details about an individual channel.
     *
     * @param  string  $channel
     *
     * @return array
     * @throws JsonException
     */
    public function get($channel)
    {
        return $this->client->send(
            sprintf('/api/channels/%s', urlencode($channel))
        );
    }
}
