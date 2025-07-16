<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Otus\Orm\BookTable;
use Bitrix\Iblock\Elements\ElementdoctorsTable;
use Bitrix\Iblock\Elements\ElementspecsTable;
use Bitrix\Iblock\Elements\ElementprocedureTable;


if (!Loader::includeModule('iblock'))
{
    return;
}

$q = new Query(BookTable::class);
$q->setSelect([
    'ID',
    'TITLE',
    'YEAR',
    'PUBLISH_DATE',
    'PAGES',
    'DOCTOR_NAME' => 'DOCTOR_RECOMMENDS.NAME',
    // Используем правильный код свойства
    'PROCEDURE_NAME' => 'PROCEDURE.NAME',
]);

$result = $q->exec();

$books = [];
$doctors = [];
$procedures = [];

while ($arItem = $result->fetch())
{
    if (!isset($books[$arItem['ID']])) {
        $books[$arItem['ID']] = [
            'TITLE' => $arItem['TITLE'],
            'YEAR' => $arItem['YEAR'],
            'PUBLISH_DATE' => $arItem['PUBLISH_DATE'],
            'PAGES' => $arItem['PAGES'],
        ];
    }

    $doctors[$arItem['ID']][] = $arItem['DOCTOR_NAME'];

    if (!empty($arItem['PROCEDURE_NAME'])) {
        $procedures[$arItem['ID']][] = $arItem['PROCEDURE_NAME'];
    }
}

foreach ($books as $id => &$book)
{
    if (isset($doctors[$id])) {
        $book['DOCTORS'] = array_unique($doctors[$id]);
    } else {
        $book['DOCTORS'] = [];
    }

    if (isset($procedures[$id])) {
        $book['PROCEDURES'] = array_unique($procedures[$id]);
    } else {
        $book['PROCEDURES'] = [];
    }
}
unset($book);

// pr($books);


?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF--8">
    <title>Список книг</title>
    <style>
        /* Простые стили для наглядности */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f9; color: #333; line-height: 1.6; }
        .container { max-width: 900px; margin: 20px auto; padding: 20px; }
        .book-card { background-color: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .book-card h3 { margin-top: 0; color: #005a9c; }
        .book-card ul { padding-left: 20px; }
        .book-card h4 { margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Каталог книг</h1>

        <?php

        if (!empty($books)): 
            foreach ($books as $book): 
        ?>

            <div class="book-card">
                <h3><?= htmlspecialchars($book['TITLE']) ?></h3>
                
                <ul>
                    <?php if (!empty($book['YEAR'])): ?>
                        <li><strong>Год:</strong> <?= htmlspecialchars($book['YEAR']) ?></li>
                    <?php endif; ?>

                    <?php if (!empty($book['PAGES'])): ?>
                        <li><strong>Страниц:</strong> <?= htmlspecialchars($book['PAGES']) ?></li>
                    <?php endif; ?>

                    <?php if (!empty($book['PUBLISH_DATE'])): ?>
                        <li><strong>Дата публикации:</strong> <?= htmlspecialchars($book['PUBLISH_DATE']) ?></li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($book['DOCTORS'])): ?>
                    <h4>Рекомендующие врачи:</h4>
                    <ul>
                        <?php foreach ($book['DOCTORS'] as $doctor): ?>
                            <li><?= htmlspecialchars($doctor) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($book['PROCEDURES'])): ?>
                    <h4>Связанные процедуры:</h4>
                    <ul>
                        <?php foreach ($book['PROCEDURES'] as $procedure): ?>
                            <li><?= htmlspecialchars($procedure) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            </div>

        <?php
            endforeach;
        else:
        ?>
            <p>Книги не найдены.</p>
        <?php
        endif;
        ?>

    </div>

</body>
</html>



<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");