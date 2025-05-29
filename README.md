# Lesson 1

## Создание скелетонов проектов

Создаем папки под проекты.
```bash
cd ~ 
mkdir -p sf-lessons/{study-on,study-on.billing}
cd sf-lessons/study-on

```

### Настройка проекта studyOn

#### Настройка docker 

Размещаем в директории study-on файл [docker-compose.yml](docker-compose.yml) и директорию [docker/](docker) из материалов урока.
Для удобства разработки можно использовать проброс портов в docker, чтобы можно было подключиться к базе данных в PhpStorm


Cоздаем файл .env, прописываем в нем переменнную NGINX_PORT, которая потребуется для запуска контейнера (команду необходимо выполнить в консоли)
```bash
echo 'NGINX_PORT=81' >> .env
```

Переименовываем файл конфигурации для nginx  
```bash
mv docker/hosts/app.conf.dist docker/hosts/app.conf  
```
и указываем в нем домен study-on.local 

```bash
$ cat docker/hosts/app.conf 
server {
    server_name study-on.local;
    include symfony;
}
```

Чтобы обращаться к проекту по домену, необходимо зарегистрировать его в локальном hosts-файле:
```bash
$ sudo cat /etc/hosts

# symfony lessons
127.0.0.1 study-on.local
```


Запускаем контейнер
```bash
docker-compose up -d
```

#### Создание проекта

Создаем проект StudyOn
```bash
docker-compose exec php composer create-project symfony/skeleton study-on
```
Проект создается в поддиректории study-on
Перемещаем файлы на уровень выше (в текущую директорию)
```bash
cp -a study-on/. .
rm -r study-on/
```

Наш .env-файл оказался перезаписан, так что снова добавляем в него переменную `NGINX_PORT` для докера (команду необходимо выполнить в консоли)

```bash
echo 'NGINX_PORT=81' >> .env
```

Доустанавливаем `symfony/webapp-pack`
```
cd study-on
docker-compose exec php composer require symfony/webapp-pack
```

На вопрос "Do you want to include Docker configuration from recipes?" отвечаем **No**, у нас в проекте будут свои конфиги Docker.

В результате проект должен открыться в браузере по адресу http://study-on.local:81/


Добавляем проект в git (не забываем подставить свое имя в адресе git-репозитория).
```
git init .
git remote add origin https://github.com/sfuser/study-on.git
```

### Настройка проекта studyOn.Billing

Перейдем в папку проекта studyOn.Billing

```bash
cd ~/sf-lessons/study-on.billing
```

#### Настройка docker 
 
Выполним настройки по аналогии со StudyOn. Отличия следующие:

* порт для nginx  `NGINX_PORT=82`

* домен в app.conf и hosts `billing.study-on.local`


#### Создание проекта

Используем только symfony/skeleton. Отдельно доустанавливаем phpunit

```bash
docker-compose exec php composer create-project symfony/skeleton study-on.billing

cp -a study-on.billing/. .
rm -r study-on.billing/

echo 'NGINX_PORT=82' >> .env

docker-compose exec php composer require symfony/phpunit-bridge

```

Проект должен открыться в браузере по адресу http://billing.study-on.local:82/

Добавляем проект в git (не забываем подставить свое имя в адресе git-репозитория).
```
git init .
git remote add origin https://github.com/sfuser/study-on.billing.git
```

## Работа с Docker Compose

Ниже показаны примеры типовых операций с docker-compose. Все операции выполняются в папке, где расположен `docker-compose.yml`.

Запуск сборки
```bash
$ docker-compose up -d
```

Остановка работы сборки
```bash
$ docker-compose down
```

Запуск команды внутри запущенного контейнера
```bash
$ docker-compose exec php bin/console list
```

## Работа с make

Пояснения и синтаксис запуска make-команд из [Makefile](Makefile).

Запустить docker-compose
```bash
$ make up
```

Остановить docker-compose
```bash
$ make down
```

Очистить кеш Symfony
```bash
$ make clear
```

Создать миграцию
```bash
$ make migration
```

Применить миграции
```bash
$ make migrate
```

Загрузить фикстуры
```bash
$ make fixtload
```
