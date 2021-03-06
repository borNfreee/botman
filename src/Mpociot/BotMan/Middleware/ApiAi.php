<?php

namespace Mpociot\BotMan\Middleware;

use Mpociot\BotMan\Message;
use Mpociot\BotMan\Http\Curl;
use Mpociot\BotMan\Drivers\Driver;
use Mpociot\BotMan\Interfaces\HttpInterface;
use Mpociot\BotMan\Interfaces\MiddlewareInterface;

class ApiAi implements MiddlewareInterface
{
    /** @var string */
    protected $token;
    /** @var HttpInterface */
    protected $http;
    /** @var string */
    protected $apiUrl = 'https://api.api.ai/v1/query';

    /**
     * Wit constructor.
     * @param string $token wit.ai access token
     * @param HttpInterface $http
     */
    public function __construct($token, HttpInterface $http)
    {
        $this->token = $token;
        $this->http = $http;
    }

    /**
     * Create a new Wit middleware instance.
     * @param string $token wit.ai access token
     * @return ApiAi
     */
    public static function create($token)
    {
        return new static($token, new Curl());
    }

    /**
     * Handle / modify the message.
     *
     * @param Message $message
     * @param Driver $driver
     */
    public function handle(Message &$message, Driver $driver)
    {
        $response = $this->http->post($this->apiUrl, [], [
            'query' => [$message->getMessage()],
            'sessionId' => time(),
            'lang' => 'en',
        ], [
            'Authorization: Bearer '.$this->token,
            'Content-Type: application/json; charset=utf-8',
        ], true);

        $response = json_decode($response->getContent());
        $reply = isset($response->result->speech) ? $response->result->speech : '';
        $action = isset($response->result->action) ? $response->result->action : '';
        $intent = isset($response->result->metadata->intentName) ? $response->result->metadata->intentName : '';

        $message->addExtras('apiReply', $reply);
        $message->addExtras('apiAction', $action);
        $message->addExtras('apiIntent', $intent);
    }

    /**
     * @param Message $message
     * @param string $test
     * @param bool $regexMatched
     * @return bool
     * @internal param string $test
     */
    public function isMessageMatching(Message $message, $test, $regexMatched)
    {
        return true;
    }
}
