<?php


if (file_exists(__DIR__ . '/../../vendor/autoload.php')){
    require_once __DIR__ . '/../../vendor/autoload.php';
}

include_once __DIR__ . '/../app/autoload.php';

// foreach([
// 	__DIR__.'/otus/classes/lists/DoctorsPropertyValuesTable.php',
//     __DIR__.'/otus/classes/lists/ProceduresPropertyValuesTable.php',
//     __DIR__.'/otus/classes/AbstractIblockPropertyValuesTable.php',

// ]
// as $filePath){

//     if (file_exists($filePath))
//     {
//         require_once($filePath);
//     }
// }
// unset($filePath);

// вывод данных 
function pr($var, $type = false) {
    echo '<pre style="font-size:10px; border:1px solid #000; background:#FFF; text-align:left; color:#000;">';
    if ($type)
        var_dump($var);
    else
        print_r($var);
    echo '</pre>';
}
?>