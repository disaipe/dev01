<?php
    $module = $getModule();
?>
<div class='flex flex-col px-2 py-1'>
    <span>{{ $module->getName() }}</span>
    <span class='text-xs text-gray-500'> {{ $module->getDescription() }}</span>
</div>
