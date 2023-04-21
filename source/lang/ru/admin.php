<?php

return [
    'user' => 'Пользователь|Пользователи',
    'domain' => 'Домен|Домены',
    'module' => 'Модуль|Модули',
    'reference' => 'Справочник|Справочники',
    'job protocol' => 'Фоновая задача|Фоновые задачи',

    'code' => 'Код',
    'name' => 'Наименование',
    'short name' => 'Короткое наименование',
    'login' => 'Логин',
    'password' => 'Пароль',
    'email' => 'E-mail',
    'identity' => 'Идентификатор',
    'host' => 'Хост',
    'port' => 'Порт',
    'timeout' => 'Таймаут',
    'schedule' => 'Расписание',
    'state' => 'Состояние',
    'result' => 'Результат',
    'run' => 'Запустить',
    'common' => 'Общее',

    'configuration' => 'Конфигурация',
    'description' => 'Описание',

    'enabled' => 'Активно',
    'system' => 'Системное',

    'error' => 'Ошибка',

    'created_at' => 'Создано',
    'updated_at' => 'Обновлено',
    'started_at' => 'Запущено',
    'ended_at' => 'Закончено',

    'cron_helper' => '<pre class="text-xs ml-1">
 - - - - -
 | | | | |
 | | | | |
 | | | | +----- day of week (0 - 7) (Sunday=0 or 7)
 | | | +---------- month (1 - 12)
 | | +--------------- day of month (1 - 31)
 | +-------------------- hour (0 - 23)
 +------------------------- min (0 - 59)</pre>',

    'menu' => [
        'access' => 'Доступ',
        'links' => 'Ссылки',
        'common' => 'Общее',
        'debug' => 'Отладка',

        'portal' => 'Перейти на портал',
    ],

    '$user' => [
        'name' => 'Имя пользователя',
        'name help' => 'Отображается другим пользователям',
        'email help' => 'Используется для входа в систему',
        'password help' => 'Оставьте пустым, если не изменение не требуется',
        'domain help' => 'Связанный домен',
    ],

    '$domain' => [
        'name_helper' => 'Наименование подключения для отображения пользователям, в т.ч. на экране аутентификации',
        'code_helper' => 'Код домена (только латиница и символы "_-")',
        'username' => 'Служебный пользователь',
        'base_dn' => 'Базовый DN',
        'base_dn_helper' => 'Например, "dc=domain,dc=local"',
        'filters' => 'Фильтры',
        'filter_add' => 'Добавить фильтр',
        'rule' => 'Правило',
        'rule_helper' => 'Задайте LDAP-фильтр для ограничения пользователей с возможностью аутентификации на сайт',
        'test_connection' => 'Тест соединения',
        'connection_success' => 'Подключение успешно',
    ],

    '$indicator' => [
        'common' => 'Базовая конфигурация',
        'schema' => 'Схема',
        'column' => 'Колонка',
        'schema_add' => 'Добавить показатель',

        'count_helper' => 'В качестве значения будет использовано количество полученных элементов',
        'sum_helper' => 'В качестве значения будет использована сумма значений из указанной ниже колонки',
    ],

    '$expression' => [
        'count' => 'Количество',
        'sum' => 'Сумма',
    ],

    '$module' => [
        'migrate' => 'Применить миграции',
        'migrations started' => 'Процесс применения миграций запущен',
    ],

    '$service' => [
        'queue' => 'Служба очередей',

        'widget' => [
            'title' => 'Службы',
            'description' => 'Фоновые службы на уровне ОС для полноценной работы всех механизмов портала',
            'name' => 'Служба',
            'status' => 'Состояние',
            'actions' => 'Действия',
        ],
    ],
];
