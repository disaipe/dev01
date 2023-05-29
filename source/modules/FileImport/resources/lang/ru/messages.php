<?php

return [
    'name' => 'Импорт данных из файлов',
    'description' => 'Загрузка табличных данных из файлов (CSV, XLS, XLSX) в справочники',

    'file' => 'Файл импорта',
    'file import' => 'Файл импорта|Файлы импорта',

    'path' => 'Путь к файлу',
    'path help' => 'Укажите полный путь к файлу',

    'reference' => 'Справочник',
    'reference help' => 'Укажите справочник, в который будут сохранены загруженные данные',

    'company linking' => 'Связь с организацией',

    'fields schema' => [
        'title' => 'Сопоставление данных',
        'add' => 'Добавить колонку',

        'reference' => 'Колонка справочника',
        'reference help' => 'Выберите колонку выбранного справочника',

        'file' => 'Колонка в файле',
        'file help' => 'Укажите наименование соответствующей колонки в файле',

        'company prefix type' => 'Тип связи с организацией',
        'company prefix type help' => 'Укажите тип связи данных с организацией - по ID или коду организации',

        'by company id' => 'По ID организации',
        'by company code' => 'По коду (префиксу) организации',

        'company prefix column' => 'Колонка с данными организации',
        'company prefix column help' => 'Укажите колонку в файле, в которой находятся данные организации',
    ],

    'action' => [
        'file import' => [
            'title' => 'Импорт из файла',
            'tooltip' => 'Добавить задание на импорт из файла в очередь',

            'success' => 'Задание на импорт данных добавлено в очередь',
        ],

        'import from' => [
            'title' => 'Импорт из...',
            'tooltip' => 'Выбрать файл из указанного каталога и загрузить из него данные',

            'success' => 'Задание на импорт данных из выбранного файла добавлено в очередь',
        ]
    ],

    'job' => [
        'files import' => [
            'title' => 'Импорт данных из всех активных файлов',
        ],

        'file import' => [
            'title' => 'Импорт данных из файла :file',

            'errors' => [
                'file not exist' => 'Файл :path не существует',
                'file import not found' => 'Не найдена конфигурация импорта файла :id',
            ],
        ],
    ],
];
