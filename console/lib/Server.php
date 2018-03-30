<?php

use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

/**
 * @author Albert Garipov <bert320@gmail.com>
 */
class Server
{

    private $clientTasks = [];
    private $worker;

    public function __construct($socketName)
    {
        // init worker
        $this->worker = new Worker($socketName);
        $this->worker->onConnect = [$this, 'onConnect'];
        $this->worker->onMessage = [$this, 'onMessage'];
        $this->worker->onClose = [$this, 'onClose'];
    }

    public function onConnect(ConnectionInterface $connection)
    {
        echo "New connection\n";
    }

    public function onClose(ConnectionInterface $connection)
    {
        foreach ($this->clientTasks as $key => $tasks) {
            if ($tasks['connection'] === $connection) {
                echo "removed\n";
                unset($this->clientTasks[$key]);
            }
        }

        echo "Connection closed\n";
    }

    public function onMessage(ConnectionInterface $connection, $rawData)
    {
        $data = json_decode($rawData, true);

        if ($data['action'] == 'register-client-task') {
            $this->registerClientTask($connection, $data['params']);
        }
        if ($data['action'] == 'get-all-users') {
            $this->getAllUsers($connection);
        }
        if ($data['action'] == 'get-all-user-task') {
            $this->getAllUserTask($connection, $data['params']);
        }
        if ($data['action'] == 'send-message') {
            $this->sendMessage($connection, $data['params']);
        }
    }

    public function run()
    {
//        $this->worker->run();
        Worker::runAll();
    }

    /**
     * Регистрирует задачу в текущем соединении.
     * @param ConnectionInterface $connection
     * @param array $data Ключи: clientId, taskId.
     */
    public function registerClientTask(ConnectionInterface $connection, $data)
    {
        $this->clientTasks[] = [
            'connection' => $connection,
            'clientId' => $data['clientId'],
            'taskId' => $data['taskId'],
        ];

        $this->sendData($connection, [
            'action' => 'register-client-task-response',
            'status' => 'ok',
        ]);
    }

    /**
     * Возвращает массив ID все пользователей (параметр clientIds).
     * @param ConnectionInterface $connection
     */
    public function getAllUsers(ConnectionInterface $connection)
    {
        $clientIds = [];
        foreach ($this->clientTasks as $task) {
            if (!in_array($task['clientId'], $clientIds)) {
                $clientIds[] = $task['clientId'];
            }
        }

        $this->sendData($connection, [
            'action' => 'get-all-users-response',
            'clientIds' => $clientIds,
        ]);
    }

    /**
     * Возвращает все задачи указанного клиента (параметр taskIds).
     * @param ConnectionInterface $connection
     * @param array $data Ключи: clientId.
     */
    public function getAllUserTask(ConnectionInterface $connection, $data)
    {
        $taskIds = [];
        foreach ($this->clientTasks as $task) {
            if ($task['clientId'] == $data['clientId'] && !in_array($task['taskId'], $taskIds)) {
                $taskIds[] = $task['taskId'];
            }
        }

        $this->sendData($connection, [
            'action' => 'get-all-user-task-response',
            'taskIds' => $taskIds,
        ]);
    }

    /**
     * Рассылает сообщения клиентам.
     * @param ConnectionInterface $connection
     * @param array $data Ключи: userId, [taskId]. 
     * Пошлет сообщение всем клиентам, если userId=all.
     */
    public function sendMessage(ConnectionInterface $connection, $data)
    {
        foreach ($this->clientTasks as $task) {
            // продолжаем, если clientId=все или совпадает
            if ($data['clientId'] == 'all' || $data['clientId'] == $task['clientId']) {
                // прорускаем, если taskId указан и не совпадает
                if (isset($data['taskId']) && $data['taskId'] != $task['taskId']) {
                    continue;
                }
                // шлем сообщение по соединению, в котором зарегистрирована задача
                $this->sendData($task['connection'], [
                    'action' => 'show-message',
                    'message' => $data['message'],
                ]);
            }
        }

        $this->sendData($connection, [
            'action' => 'send-message-response',
            'status' => 'ok',
        ]);
    }

    private function sendData(ConnectionInterface $connection, $data)
    {
        $connection->send(json_encode($data));
    }

}