# Winter CMS DevOps
Данный репозиторий представляет собой DevOps-решение для развертывания и управления приложением на основе Winter CMS, настроенное для автоматической сборки, тестирования и развертывания с использованием Docker и GitHub Actions. 
## Технологии
- Winter CMS: PHP-фреймворк для разработки веб-приложений
- Docker: Для контейнеризации приложения
- Docker Compose: Для управления многоконтейнерным окружением
- GitHub Actions: Для автоматизации CI/CD
- PHP 8.2: Версия PHP, используемая в проекте
- MySQL: База данных для Winter CMS
- Apache: Веб-сервер для обслуживания приложения
## Требования
Для запуска проекта локально или в production вам понадобятся:
- Docker (версия 20.10.0 или выше)
- Docker Compose (версия 1.29.0 или выше)
- GitHub аккаунт (для использования GitHub Actions)
## Как запустить проект
Способ 1 ()
1. Клонируйте репозиторий на ваш компьютер: git clone https://github.com/nicekrassss/winter-cms-devops.git
2. Перейдите в директорию проекта: cd winter-cms-devops
3. Настройка переменных окружения:
Способ 1 (автоматический)
    - Создайте файл .env, скопируйте в него содержимое файла .env.example, либо введите команду: cp .env.example .env
    - Запустите команду для автоматической генерации ключа: docker-compose run --rm artisan key:generate
    - Если все прошло успешно, ключ автоматически добавится в файл .env
Способ 2 (ручной)
    - Создайте файл .env, скопируйте в него содержимое файла .env.example, либо введите команду: cp .env.example .env
    - Сгенерируйте ключ: docker-compose run --rm artisan key:generate --show
    - Скопируйте сгенерированный ключ и вставьте его в файл .env в строку APP_KEY=
4. Запустите проект с помощью Docker Compose:
 docker-compose up
Приложение будет доступно в браузере по адресу: http://localhost:8080
База данных MySQL будет доступна на порту 3306
Логи можно просмотреть с помощью команды: docker-compose logs
Для остановки проекта выполните команду: docker-compose down
Способ 2 (использование готового Docker-образа)
1. Скачайте образ: docker pull ghcr.io/nicekrassss/winter-cms-devops:latest
2. Запустите образ: docker run -p 8080:80 ghcr.io/nicekrassss/winter-cms-devops:latest 
или 
docker run -p 8080:80 -v "${pwd}\.env:/var/www/html/.env" ghcr.io/nicekrassss/winter-cms-devops:latest
На этом этапе приложение будет доступно по адресу: http://localhost:8080, база данных будет не доступна. Для ее активации скачайте из репозитория/создайте файл docker-compose.yml в вашей локальной директории 
3. Добавьте следующий код в docker-compose.yml (если выбрано создание файла. Если нет - следующий шаг): 
services:
  web:
    image: ghcr.io/nicekrassss/winter-cms-devops:latest
    build: .
    ports:
      - "8080:80"
    environment:
      DB_CONNECTION: mysql
      DB_HOST: db
      DB_PORT: 3306
      DB_DATABASE: winter
      DB_USERNAME: root
      DB_PASSWORD: secret
    depends_on:
      - db

  db:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: winter
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
4. Запустите проект: docker-compose up
Приложение будет доступно в браузере по адресу: http://localhost:8080
База данных MySQL будет доступна на порту 3306
Логи можно просмотреть с помощью команды: docker-compose logs
Для остановки проекта выполните команду: docker-compose down
## CI/CD Pipeline
В проекте используется GitHub Actions для автоматизации сборки, тестирования и развертывания. Для использования нужно настроить секреты. Основные этапы CI/CD:
1. Build: Сборка Docker-образа
2. Test: Запуск тестов Winter CMS
3. Deploy: Развертывание образа в GitHub Container Registry (GHCR)
Варианты использования CI/CD Pipeline:
1. Автоматический запуск: при каждом push в ветку main workflow автоматически запускается
2. Ручной запуск: вы можете вручную запустить workflow через интерфейс GitHub Actions (название - Winter CMS CI/CD)
3. Просмотр логов: логи каждого этапа можно просмотреть в разделе Actions вашего репозитория
Расположение файла в репозитории: .github/workflows/ci.yml
## Настройка секретов
В файле ci.yml используется секрет GHCR_TOKEN (токен для доступа к GitHub Container Registry). Сначала нужно сгенерировать собственный токен, затем добавить его в секрет.
Создание токена:
1. Перейдите в Settings (аккаунта) - Developer settings - Personal access tokens
2. Нажмите Generate new token (подойдет классический)
3. Укажите срок действия токена (по предпочтению)
4. Выберите права: 
    read:packages (для чтения образов из GHCR)
    write:packages (для загрузки образов)
Добавление секретов:
1. Перейдите в Settings (репозитория) - Secrets and variables - Actions
2. Нажмите New repository secret
3. Добавьте секрет GHCR_TOKEN и вставьте сгенерированный токен
## Как внести изменения
1. Локальная разработка
    - Внесите изменения в код
    - Запустите docker-compose up для проверки изменений
    - Проверьте, что приложение работает корректно
2. Обновление CI/CD
    - Измените файл .github/workflows/ci.yml, если нужно обновить этапы сборки или развертывания
    - Убедитесь, что все тесты проходят успешно
3. Обновление Dockerfile
    - Внесите изменения в Dockerfile, если нужно добавить новые зависимости или изменить конфигурацию
    - Пересоберите Docker-образ: docker-compose build
4. Обновление docker-compose.yml
    - Внесите изменения в docker-compose.yml, если нужно добавить новые сервисы или изменить конфигурацию существующих
    - Перезапустите проект: docker-compose up
## Архитектура
Схема локального развертывания:
![Architecture Diagram](images/local.png)
Схема CI/CD Pipeline: 
![Architecture Diagram](images/ci_cd.png)
Схема продуктивного развертывания:
![Architecture Diagram](images/prod.png)
Схема общей архитектуры:
![Architecture Diagram](images/all.png)
## Лицензия
Этот проект распространяется под лицензией MIT. Подробности см. в файле LICENSE
