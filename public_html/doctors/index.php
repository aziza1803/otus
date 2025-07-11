<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
$APPLICATION->SetAdditionalCSS('/doctors/style.css');


// модели работающие с инфоблоками
use Models\Lists\DoctorsPropertyValuesTable as DoctorsTable;
use Models\Lists\ProcsPropertyValuesTable as ProcsTable;
// массивы для сохранения полученных данных
$doctors = [];
$doctor = [];
$procs = [];

$path = trim($_GET['path'],'/');
$action = '';
$doctor_name = '';



if (!empty($path)) {
    $path_parts = explode('/',$path);
    if (sizeof($path_parts)<3) {
        if (sizeof($path_parts) == 2 && $path_parts[0] == 'edit') {
            $action = 'edit';
            $doctor_name = $path_parts[1];
        } else if (sizeof($path_parts) == 1 && in_array($path_parts[0],['new','newproc'])) {
            $action = $path_parts[0];
        } else $doctor_name = $path_parts[0];
    }
}

if (!empty($doctor_name)) {
        $doctor = DoctorsTable::query()
            ->setSelect([
                '*', 
                'NAME' => 'ELEMENT.NAME', 
                'PROC_IDS_MULTI',
                'ID' => 'ELEMENT.ID'
            ])
            ->where("NAME", $doctor_name)
            ->fetch();

        if (is_array($doctor)) { //выводим одного доктора

            if($doctor['PROC_IDS_MULTI']){
                $procs = ProcsTable::query()
                    ->setSelect(['NAME' => 'ELEMENT.NAME'])
                    ->where("ELEMENT.ID", "in", $doctor['PROC_IDS_MULTI'])
                    ->fetchAll();
            }
    
        }
        else {
            header("Location: /doctors");
            exit();
        }
}

// если не выбран доктор и его
// выводим всех докторов 
if (empty($doctor_name) && empty($action)) { 
    $doctors = DoctorsTable::query()
        ->setSelect(['*', "NAME" => "ELEMENT.NAME", "ID" => "ELEMENT.ID"])
        ->fetchAll();
}

if ($action == 'newproc') { // добавляем процедуру
    if (isset($_POST['proc-submit'])) {
        unset($_POST['proc-submit']);
        if (ProcsTable::add($_POST)) {
            header("Location: /doctors");
            exit();
        } else echo "Произошла ошибка 3";
    }
}

if ($action == 'new' || $action == 'edit') { // добавляем доктора
    if (isset($_POST['doctor-submit'])) {
        unset($_POST['doctor-submit']);
        if ($action == 'edit' && !empty($_POST['ID'])) {
            $ID = $_POST['ID'];
            unset($_POST['ID']);
            $_POST['IBLOCK_ELEMENT_ID']=$ID;

            $procs = $_POST['PROC_IDS_MULTI'];
            unset($_POST['PROC_IDS_MULTI']);
            CIBlockElement::SetPropertyValues($ID, DoctorsTable::IBLOCK_ID, $procs, "PROC_IDS_MULTI");

            if (DoctorsTable::update($_POST['ID'], $_POST)) {
                header("Location: /doctors");
                exit();
            } else echo "Произошла ошибка 2";
        }
        if ($action=='new' && DoctorsTable::add($_POST)) {
            header("Location: /doctors");
            exit();
        } else echo "Произошла ошибка 1";
    }

    $proc_options = ProcsTable::query()->setSelect(["ID"=>"ELEMENT.ID","NAME"=>"ELEMENT.NAME"])->fetchAll();
    if (!empty($doctor_name)) {
        $data = $doctor;
    }
}

// if (($action == 'new' || $action == 'edit') && isset($_POST['doctor-submit'])) {
    
//     // 1. Убираем ненужные данные из $_POST
//     unset($_POST['doctor-submit']);
    
//     // 2. Готовим массив для свойств
//     $properties = [];
//     if (isset($_POST['PROC_IDS_MULTI'])) {
//         $properties['PROC_IDS_MULTI'] = $_POST['PROC_IDS_MULTI'];
//         unset($_POST['PROC_IDS_MULTI']);
//     }

//     // 3. Добавляем ID инфоблока в основной массив данных
//     // Это обязательно для метода Add
//     $_POST['IBLOCK_ID'] = DoctorsTable::IBLOCK_ID;
    
//     // 4. Добавляем свойства в правильном формате
//     $_POST['PROPERTY_VALUES'] = $properties;

//     // 5. Используем конструкцию if / else if
//     if ($action == 'edit' && !empty($_POST['ID'])) {
//         $ID = $_POST['ID'];
//         // Метод update обычно сам обновляет свойства, 
//         // но если нет, то используйте CIBlockElement::SetPropertyValuesEx
//         if (DoctorsTable::update($ID, $_POST)) {
//             // Для edit можно отдельно обновить свойства, если update не справляется
//             CIBlockElement::SetPropertyValuesEx($ID, DoctorsTable::IBLOCK_ID, $_POST['PROPERTY_VALUES']);
            
//             header("Location: /doctors");
//             exit();
//         } else {
//             // Для отладки выводим ошибку Bitrix
//             global $APPLICATION;
//             echo "Произошла ошибка обновления: " . $APPLICATION->GetException();
//         }

//     } else if ($action == 'new') {
//         $el = new CIBlockElement;
//         $newID = $el->Add($_POST);

//         if ($newID) {
//             header("Location: /doctors");
//             exit();
//         } else {
//             // Выводим конкретную ошибку от Bitrix, чтобы понять причину
//             echo "Произошла ошибка добавления: " . $el->LAST_ERROR;
//         }
//     }
// }

// Код для отображения формы
$proc_options = ProcsTable::query()->setSelect(["ID" => "ELEMENT.ID", "NAME" => "ELEMENT.NAME"])->fetchAll();
if (!empty($doctor_name)) {
    $data = $doctor;
}

?>
<section class="doctors">
    <h1><a href="/doctors">Врачи</a></h1>

    <?php if (empty($action)):?>
    <div class="add-buttons">
        <?php if (empty($doctor_name)):?>
        <a href="/doctors/new"><button>Добавить врача</button></a>
        <a href="/doctors/newproc"><button>Добавить процедуру</button></a>
        <?php else:?>
            <a href="/doctors/edit/<?=$doctor_name?>"><button>Изменить данные врача</button></a>
        <?php endif;?>
    </div>
    <?php endif;?>

    <div class="cards-list">
    <?php foreach ($doctors as $doc) { ?>
        <a class="card" href="/doctors/<?=$doc["NAME"]?>">
            <div class="fio">
                <?=$doc['LAST_NAME']?>
                <?=$doc['FIRST_NAME']?>
            </div>
        </a>
    <?php } ?>
    </div>

    <?php if (is_array($doctor) && sizeof($doctor)>0 && $action!='edit'):?>
    <div class="doctor-page">
        <h2><?=$doctor['LAST_NAME']." ".$doctor['FIRST_NAME']?></h2>
        <h3>Процедуры:</h3>
        <ul>
            <?php foreach ($procs as $proc):?>
                <li><?=$proc['NAME']?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($action=='new' || $action=='edit'):?>
    <form method="POST">
        <h2 style="text-align:center;">Данные врача</h2>
        <div class="doctor-add-form">

            <?php if (isset($data['ID'])):?>
                <input type="hidden" name="ID" value="<?=$data['ID']?>" />
            <?php endif;?>

            <input type="text" name="NAME" placeholder="Название страницы врача (фамилия латиницей)" value="<?=$data['NAME']??''?>"/>
            <input type="text" name="LAST_NAME" placeholder="Фамилия врача" value="<?=$data['LAST_NAME']??''?>"/>
            <input type="text" name="FIRST_NAME" placeholder="Имя врача" value="<?=$data['FIRST_NAME']??''?>"/>

            <select multiple name="PROC_IDS_MULTI[]">
                <option value="" selected disabled>Процедуры</option>
                <?php foreach ($proc_options as $proc):?>
                    <option value="<?=$proc['ID']?>"
                            <?php if (isset($data['PROC_IDS_MULTI']) && in_array($proc['ID'],$data['PROC_IDS_MULTI'])):?>selected<?php endif;?>>
                        <?=$proc['NAME']?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" name="doctor-submit" value="Сохранить"/>
    </div>
    </form>
    <?php endif; ?>

    <?php if ($action=='newproc'):?>
        <form method="POST">
            <h2 style="text-align:center;">Добавить процедуру</h2>
            <div class="doctor-add-form">
                <input type="text" name="NAME" placeholder="Название процедуры"/>
                <input type="submit" name="proc-submit" value="Сохранить"/>
            </div>
        </form>
    <?php endif; ?>

</section>

<style>
    /* --- Контейнер формы --- */
.doctor-add-form {
    background-color: #ffffff;
    padding: 2rem 2.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 30, 80, 0.08);
    max-width: 500px;
    margin: 1rem auto; /* Центрирование формы */
    display: flex;
    flex-direction: column;
    gap: 1.25rem; /* Расстояние между элементами */
}

/* --- Заголовок формы --- */
.doctor-add-form + h2, /* Для случая, когда h2 перед формой */
h2 { /* Общий стиль для h2, если он внутри */
    text-align: center;
    color: #334155;
    font-weight: 600;
    margin-bottom: 0;
}

/* --- Общие стили для полей ввода и списка --- */
.doctor-add-form input[type="text"],
.doctor-add-form select[multiple] {
    width: 100%;
    padding: 12px 15px;
    font-size: 1rem;
    border: 1px solid #d1d9e6;
    border-radius: 8px;
    background-color: #fdfdff;
    color: #334155;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-sizing: border-box; /* Чтобы padding не влиял на ширину */
}

/* --- Стили для плейсхолдера --- */
.doctor-add-form input::placeholder {
    color: #94a3b8;
}

/* --- Эффект при фокусе (когда пользователь кликает на поле) --- */
.doctor-add-form input[type="text"]:focus,
.doctor-add-form select[multiple]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

/* --- Стилизация списка с множественным выбором --- */
.doctor-add-form select[multiple] {
    height: 150px; /* Задаем высоту, чтобы список не был слишком большим */
}

/* --- Опции внутри списка --- */
.doctor-add-form select[multiple] option {
    padding: 8px 12px;
}

/* --- Кнопка "Сохранить" --- */
.doctor-add-form input[type="submit"] {
    width: 100%;
    padding: 14px 20px;
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    background-color: #3b82f6; /* Акцентный синий цвет */
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease;
}

/* --- Эффект при наведении на кнопку --- */
.doctor-add-form input[type="submit"]:hover {
    background-color: #2563eb; /* Чуть темнее при наведении */
}

/* --- Эффект при нажатии на кнопку --- */
.doctor-add-form input[type="submit"]:active {
    transform: scale(0.99); /* Легкое "вдавливание" */
}
</style>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>