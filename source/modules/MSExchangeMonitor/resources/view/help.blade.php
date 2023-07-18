<h1 class='text-xl font-bold'>Модуль синхронизации состояния почтовых ящиков MS Exchange</h1>

<div class='p-1'>
    Предназначен для получения состояния почтовых ящиков с сервера MS Exchange - размер, количество
    элементов.
</div>

<div class='p-1'>
    <h2 class='text-lg font-bold'>Служба мониторинга</h2>
    Для получения данных хранилищ применяется вспомогательная служба DEV01 MS Exchange.
    Скачать актуальную версию можно в репозиторий Github:
    <a style='text-decoration: underline'
       href='https://github.com/disaipe/dev01-ms-exchange/releases' target='_blank'>
        https://github.com/disaipe/dev01-ms-exchange</a>.
    Для работы модуля требуется запущенная, настроенная и доступная к обращению служба.
</div>

<div class='mt-2 font-medium'>Общие требования:</div>
<ul class='pl-4'>
    <li>
        &bull; Служба должна быть настроена и запущена как служба Windows, желательно с автоматическим
        запуском и перезапуском при нештатных ситуациях.
    </li>
    <li>
        &bull; Служба должна иметь доступ к подключению к серверу с оснасткой Microsoft.Exchange.Management.PowerShell.SnapIn
        для осуществления подключения к серверу MS Exchange.
    </li>
    <li>
        &bull; Портал и служба мониторинга обмениваются данными, поэтому убедитесь, что настройки фаерволла
        их не блокируют. Порт, по которому служба принимает соединения, указан в конфигурации его запуска.
    </li>
</ul>

<div class='mt-2 font-medium'>Принцип работы</div>
<div>
    Служба мониторинга по запросу портала производит обращение к серверу MS Exchange через оснастку Powershell
    "Microsoft.Exchange.Management.PowerShell.SnapIn" и получает от него статистику почтовых ящиков.<br>
    Для подключения необходимо указать адрес сервера с оснасткой и данные для подключения к нему с помощью
    вспомогательного скрипта `generateCredentials.ps1` (см. инструкцию к службе).
    <br/>
    <ul class='pl-4'>
        <ol>1. Портал формирует HTTP-запрос и отправляет его службе мониторинга (по расписанию или вручную)</ol>
        <ol>2. Служба принимает запрос и сообщает порталу, что задачу взята в работу</ol>
        <ol>
            3. Служба подключается к серверу MS Exchange и выполняет работу. Как только работа будет завершена -
            отправляет HTTP-запрос на портал с результатами работы. Всё это время портал работает в обычном режиме
        </ol>
        <ol>4. Портал принимает запрос от службы и сохраняет результаты в базу данных</ol>
    </ul>
</div>
