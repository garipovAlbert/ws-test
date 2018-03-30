<?php

use WebSocket\Client;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/CommandLine.php';

const SERVER_NAME = 'ws://localhost:8000';




$client = new Client(SERVER_NAME);

$commands = CommandLine::parseArgs($argv);



if (isset($commands['get-all-users'])) {
    $client->send(json_encode([
        'action' => 'get-all-users',
    ]));

    $data = json_decode($client->receive(), true);

    if (count($data['clientIds']) > 0) {
        print("Client IDs: " . join(', ', $data['clientIds']) . ".\n");
    } else {
        print("No clients\n");
    }
}


if (isset($commands['get-all-user-task'])) {
    $client->send(json_encode([
        'action' => 'get-all-user-task',
        'params' => [
            'clientId' => (int) $commands['get-all-user-task'],
        ],
    ]));

    $data = json_decode($client->receive(), true);

    if (count($data['taskIds']) > 0) {
        print("Task IDs: " . join(', ', $data['taskIds']) . ".\n");
    } else {
        print("No tasks\n");
    }
}


if (isset($commands['send-message'])) {
    $params = [
        'clientId' => $commands['send-message'],
        'message' => $commands['message'],
    ];
    if (isset($commands['task'])) {
        $params['taskId'] = $commands['task'];
    }

    $client->send(json_encode([
        'action' => 'send-message',
        'params' => $params,
    ]));

    $data = json_decode($client->receive(), true);
    print($data['status'] . "\n");
}



