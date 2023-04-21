<h1 class='text-xl font-bold'>Модуль синхронизация состояния баз данных</h1>

<p class='p-1'>
    Предназначен для подключения к серверам баз данных и сбора статистики находящихся на них баз данных -
    состав, размер и т.д.
</p>

<div class='p-1'>
    Поддерживаемые типы баз данных:
    <ul class='pl-2'>
        <li>&bull; SQL Server 2012+</li>
        <li>&bull; MySQL <i>(не протестировано)</i></li>
        <li>&bull; MariaDB <i>(не протестировано)</i></li>
    </ul>
</div>

<h2 class='text-lg font-bold mt-4'>Sql Server</h2>
<p>
    Для возможности подключения к серверам SQL Server установлен и настроен драйвер <i>sqlsrv</i> и <i>pdo_sqlsrv</i>
    версии 5.10.1. У драйверов данного типа есть особенности и
    <a style='text-decoration: underline;'
        href='https://learn.microsoft.com/en-us/sql/connect/php/microsoft-php-drivers-for-sql-server-support-matrix?view=sql-server-ver16#sql-server-version-certified-compatibility' target='_blank'>ограничения</a>,
    которые нужно иметь ввиду при работе с ними.
</p>
<p>
    <b>Внимание!</b> При создании подключения к серверу SQL Server замечена <i>особенность</i> - при указании порта
    подключения это подключение перестаёт работать. Причина такого поведения пока не понятна, крайне рекомендуется
    не указывать порт при создании и настройке сервера баз данных.
</p>

<h2 class='text-lg font-bold mt-4'>Индикаторы</h2>
<div>

    <ul class='pl-2'>
        <li>&bull; Размер баз данных - сумма размеров всех баз данных по коду организации в гигабайтах.</li>
    </ul>
</div>
