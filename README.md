# test

Установка

1. Выполните команду composer install в папке /console
2. Сделайте documentRoot веб-сервера в папке /web
3. При необходимости поменяйте константы SERVER_NAME в файлах client1.html, client2.html, client.php



Команды
- php client.php --get-all-users - возвращает ID всех подключившихся клиентов
- php client.php --get-all-user-task=123 - возвращает ID всех текущих задач для клиента с ID=123
- php client.php --send-message=all --message="Hello!" - отправляет сообщение всем пользователям
- php client.php --send-message=123 --message="Hello!" - отравляет сообщение клиенту с ID=123
- php client.php --send-message=123 --task=456 --message="Hello!" - отравляет сообщение одному клиенту в одну задачу
