# Тестовое задание PHP

## Инициализация проекта

1 Запустить команду в корневой директории `docker-compose up -d`

2 Зайти в контейнер с приложением `docker exec -it php7.4 bash`

3 Запустить команду для установки зависимостей `composer install`

4 Запустить команду `php bin/console doctrine:migrations:migrate -q`

5 Запустить команду `/etc/init.d/cron start`

6 Открыть сайт `http://localhost/admin`
