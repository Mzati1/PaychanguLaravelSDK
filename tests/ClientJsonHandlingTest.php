<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Paychangu\Laravel\Http\Client;

it('returns array for normal JSON object responses', function () {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'success', 'data' => ['x' => 1]])),
    ]);

    $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock), 'http_errors' => true]);
    $client = new Client('key', 'https://api.paychangu.com/', $guzzle);

    $response = $client->get('anything');

    expect($response)->toBeArray();
    expect($response['status'])->toBe('success');
    expect($response['data']['x'])->toBe(1);
});

it('returns empty array for JSON scalar responses', function () {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode('OK')),
    ]);

    $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock), 'http_errors' => true]);
    $client = new Client('key', 'https://api.paychangu.com/', $guzzle);

    $response = $client->get('anything');

    expect($response)->toBeArray();
    expect($response)->toBe([]);
});

it('throws API error without array access issues for non-JSON error bodies', function () {
    $mock = new MockHandler([
        new Response(400, ['Content-Type' => 'text/plain'], 'Bad Request'),
    ]);

    $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock), 'http_errors' => true]);
    $client = new Client('key', 'https://api.paychangu.com/', $guzzle);

    $call = fn () => $client->get('anything');

    expect($call)->toThrow(Exception::class);
});
