<?php

return [
    'user' => 'Пользователь|Пользователи',
    'role' => 'Роль|Роли',
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

    'created at' => 'Создано',
    'updated at' => 'Обновлено',
    'started at' => 'Запущено',
    'ended at' => 'Закончено',

    'last sync' => 'Последняя синхронизация',
    'last sync date' => 'Последняя синхронизация: :date',
    'next sync date' => 'Ближайшая синхронизация: :date',

    'job started' => 'Задание запущено',
    'job staring error' => 'Ошибка запуска задания',

    'where' => 'Где',
    'or' => 'Или',
    'and' => 'И',

    'field' => 'Поле',
    'condition' => 'Условие',
    'value' => 'Значение',

    'add' => 'Добавить',

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
        'name helper' => 'Наименование подключения для отображения пользователям, в т.ч. на экране аутентификации',
        'code helper' => 'Код домена (только латиница и символы "_-")',
        'username' => 'Служебный пользователь',
        'base dn' => 'Базовый DN',
        'base dn helper' => 'Например, "dc=domain,dc=local"',
        'filters' => 'Фильтры',
        'filter add' => 'Добавить фильтр',
        'rule' => 'Правило',
        'rule helper' => 'Задайте LDAP-фильтр для ограничения пользователей с возможностью аутентификации на сайт',
        'test connection' => 'Тест соединения',
        'connection success' => 'Подключение успешно',
    ],

    '$indicator' => [
        'common' => 'Базовая конфигурация',

        'schema' => 'Схема',
        'column' => 'Колонка',
        'schema add' => 'Добавить показатель',

        'conditions' => 'Условия выборки',
        'conditions helper' => 'Добавьте дополнительные условия для запроса данных, если требуется. Указанные здесь'
            .' условия будут применены при формировании отчета к SQL-запросу соответствующего справочника.',

        'count helper' => 'В качестве значения будет использовано количество полученных элементов',
        'sum helper' => 'В качестве значения будет использована сумма значений из указанной ниже колонки',
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

    '$schedule' => [
        'widget' => [
            'title' => 'Фоновые задачи',
            'attempts' => 'Попытка',
            'available at' => 'Создано',
            'reserved at' => 'Запланировано',
        ]
    ],
];
