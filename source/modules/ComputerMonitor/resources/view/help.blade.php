<h1 class='text-xl font-bold'>Модуль синхронизация состояния персональных компьютеров</h1>

<div class='p-1'>
    Предназначен для сбора данных персональных комьютеров - их активного пользователя и т.д.
</div>

<div class='p-1'>
    <h2 class='text-lg font-bold'>Служба мониторинга</h2>
    Для получения данных хранилищ применяется вспомогательная служба DEV01 PCMON Daemon.
    Скачать актуальную версию можно в репозиторий Github:
    <a style='text-decoration: underline'
       href='https://github.com/disaipe/dev01-pcmon-daemon/releases' target='_blank'>
        https://github.com/disaipe/dev01-wmi-server</a>.
    Для работы модуля требуется запущенная, настроенная и доступная к обращению служба.
</div>

<div class='mt-2 font-medium'>Общие требования:</div>
<ul class='pl-4'>
    <li>
        &bull; Служба должна быть настроена и запущена как служба Windows, желательно с автоматическим
        запуском и перезапуском при нештатных ситуациях.
    </li>
    <li>
        &bull; Служба должна иметь доступ к выполнению запросов WQL к удаленным ПК. При необходимости,
        настройте запуск службы от имени служебного пользователя, имеющего нужные права доступа.
    </li>
    <li>
        &bull; Портал и служба мониторинга обмениваются данными, поэтому убедитесь, что настройки фаерволла
        их не блокируют. Порт, по которому служба принимает соединения, указан в конфигурации его запуска.
    </li>
</ul>

<div class='mt-2 font-medium'>Принцип работы</div>
<div>
    Работа модуля разделена на функциональности - портал работает с данными персональных компьютеров (например, методом
    их синхронизации с Active Directory), а служба мониторинга по запросу портала производит проверку состояния
    конкретного компьютера и возвращает результат. Всю работу служба производит в стороне от портала, в фоне,
    никак не препятствуя его нормальной работе:
    <br/>
    <ul class='pl-4'>
        <ol>1. Портал формирует HTTP-запрос и отправляет его службе мониторинга (по расписанию или вручную)</ol>
        <ol>2. Служба принимает запрос, получает из него данные для подключения к ПК и сообщает порталу, что принял задачу</ol>
        <ol>
            3. Служба с помощью скрипта Powershell выполняет WQL-запрос  к удалённому комьютеру и ожидает результат.
            Как только запрос вернёт результат - отправляет HTTP-запрос на портал с результатами работы.
            Всё это время портал работает в обычном режиме
        </ol>
        <ol>4. Портал принимает запрос от службы и сохраняет результаты в базу данных</ol>
    </ul>
</div>
