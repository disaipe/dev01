<?php

return [
    'name' => 'Коннектор Active Directory',
    'description' => 'Синхронизация данных из Active Directory',

    'section_sync' => 'Параметры синхронизации',

    'base dn' => 'Базовый DN',
    'base dn helper' => 'Базовый DN для поиска записей. Если не указано, будет использован базовый DN домена',

    'base dn or ou' => 'Список базовых DN ',
    'base dn or ou helper' => 'Укажите одно или несколько DN на отдельных строках для ограничения'
        .' загружаемых данных. Если не указано, будет использован базовый DN домена.'
        .' Например:</br>'
        .'<i>OU=AllUsers,DC=domain,DC=local</i><br/>'
        .'<i>OU=Users,OU=Departament,OU=Company,DC=domain,DC=local</i>',

    'filter' => 'Фильтр',
    'filters helper' => 'Укажите набор LDAP фильтров, которые будут применены к запросу DC при получении данных.'
        .' Обратите внимание, что фильтрация по OU осуществляется с помощью поля <b>Базовый DN</b>, в котором'
        .' вы можете указать один или несколько DN. При необходимости, объединяйте условия в логические блоки'
        .' <i>И</i>, <i>ИЛИ</i>. Для дополнительной информации по синтаксису и правилам написания фильтров'
        .' ознакомьтесь в дополнительной'
        .' <a href="https://google.com" target="_blank" style="text-decoration: underline">литературе</a>.'
        .' Например, для фильтрации пользователей по группе можно использовать примерно следующий синтаксис: "'
        .'<i>memberOf=CN=Admins,OU=Groups,OU=Accounts,DC=domain,DC=local</i>"',

    'ad_entry' => 'Доменная запись|Доменные записи',

    'job' => [
        'ldap sync' => [
            'title' => 'Синхронизация записей',
            'description' => 'Периодическая синхронизация записей.',
        ],
    ],

    'action' => [
        'test filters' => [
            'title' => 'Тест фильтров',
            'description' => 'Вы можете выполнить тестовый запрос для проверки фильтров с помощью кнопки ниже.'
                .' Имейте в виду, что для оптимизации работы запроса принудительно назначено ограничение в 1000'
                .' записей. Результат будет будет отображен в виде вплывающего уведомления с количеством'
                .' найденных записей.',

            'found N records' => 'Найдено :count записей',
            'records not found' => 'Записей не найдено',
        ],
    ],
];
