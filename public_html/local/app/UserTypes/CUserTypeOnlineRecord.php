<?php

namespace UserTypes;

use CJSCore;

class CUserTypeOnlineRecord 
{
    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public static function getUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'ONLINE_RECORD',
            'DESCRIPTION' => 'Онлайн-запись',

            'GetPropertyFieldHtml' => [self::class, 'GetPropertyFieldHtml'], //метод отображения свойства в Админке
            'GetPublicViewHTML' => [self::class, 'GetPublicViewHTML'], // метод отображения значения свойства в Публичной части
            'GetPublicEditHTML' => [self::class, 'GetPropertyFieldHtml'], //метод отображения значения в форме редактирования
        ];


    }

    public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
    {

        $strResult = '<button id="onl-btn-9129961501" type="bitton">Записать</button>';

        return $strResult;


    }


    public static function getDataValues($id) {

        if(empty($id)){
            return [];
        }

        \Bitrix\Main\Loader::includeModule('iblock');

        $data = \Bitrix\Iblock\Elements\ElementdoctorsTable::getList([
            'filter' => ['ID' => $id],
            'select' => ['PROC_IDS_MULTI.ELEMENT'],
        ])->fetchObject();

        $valuesElement = [];

        // if($data->getProcIdsMulti() !== null)){
            foreach ($data->getProcIdsMulti()->getAll() as $el){
                $valuesElement[$el->getElement()->getId()] = $el->getElement()->getName();
            }
        // }

        return $valuesElement;
    }


    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {       
        $strResult = '';

        if(!isset($arProperty['ELEMENT_ID'])){
            return $strResult;
        }

        $doctorId = $arProperty['ELEMENT_ID'] ?? 0;

        \CJSCore::Init(['popup']);
        $inpid = 'rec_id_' . rand(0, 99);

        $valuesElement = self::getDataValues($arProperty['ELEMENT_ID']);
        if (empty($valuesElement))
        {
            return 'У врача нет процедур';
        }

        $count = 0;

        foreach($valuesElement as $id => $el){
            $strResult .= '<a data-pr-id="'. $id. '" data-doctor-id="' . $doctorId . '" class="book_procedure" style="cursor:pointer;" id="elem_'. $inpid . '_' . $id. '">' . $el .'</a><br>';
            $count++;
        }


        $strResult .= '
        <script type="text/javascript"> 

        BX.ready(function () {
            let bookProcedure = document.querySelectorAll(".book_procedure");
            bookProcedure.forEach(function(procedure) {
                procedure.addEventListener("click", onAddOnlineRecord);
            });
        });    

        function LazyBanner(name, date, procedure, doctor) {
            
            console.log("LazyBanner");
            console.log(name);
            console.log(date);
            console.log(procedure);
            
            BX.ajax({
                url: "/local/ajax/hendlers_userfields_online_records.php", // файл на который идет запрос
                method: "POST", // метод запроса GET/POST
                // параметры передаваемый запросом
                data: {
                    NAME: name,
                    TIME: date,
                    PROC_ID: procedure,
                    DOCTOR_ID: doctor,
                },
                // ответ сервера лежит в data
                onsuccess: function(data) {
                    //document.querySelector("#www").innerHTML = data
                }
            });
            
        }       

        function onAddOnlineRecord(e){
            e.preventDefault();
            e.stopPropagation();  

            let pr_id = e.target.getAttribute("data-pr-id");
            let doctor_id = e.target.getAttribute("data-doctor-id");
            console.log(pr_id);

            let content = BX.create("div", {
                children: [
                    BX.create("input", {
                        attrs: {
                            type: "text",
                            name: "name_online_record",
                            placeholder: "Ваше ФИО",
                            id: "input_name_online_record_" + pr_id,
                        }
                    }),
                    BX.create("br"),
                    BX.create("br"),
                    BX.create("input", {
                        attrs: {
                            type: "datetime-local",
                            name: "date_online_record",
                            id: "input_date_online_record_" + pr_id,
                        }
                    }),
                    BX.create("br")
                ]
              });         
           
            BX.PopupWindowManager.create("bookingPopup_" + pr_id, pr_id, {
                    content: content,
                    titleBar: {content: BX.create("span", {html: "Запись на процедуру"})},
                    closeIcon: {right: "20px", top: "10px"},
                    width: 400,
                    height: 400,
                    zIndex: 100,
                    closeIcon: {
                        //Объект со стилями для иконки закрытия, при null -иконки не будет
                        //opacity: 1
                    },
                    titleBar: "Записаться на прием",
                    closeByEsc: true, // закрывать при нажатии на Esc
                    darkMode: false, //окно будет светлым или темным
                    autoHide: false, //закрытие при клике вне окна
                    draggable: true, //можно двигать или нет
                    resizable: true, //можно изменят размер
                    min_height: 100, //минимальная высота окна
                    min_width: 100, //минимальная ширина окна
                    lightShadow: false, // использовать светлую тень у окна
                    angle: false, // появится уголок
                    overlay: {
                        // объект со стилями фона
                        backgroundColor: "black",
                        opacity: 400
                    },
                    buttons: [
                        new BX.PopupWindowButton({
                            text: "Добавить запись", //текст кнопки
                            id: "add_new_online_record_"+pr_id, //идентификатор
                            events: {
                                click: function(){
                                   
                                    let nameValue = BX("input_name_online_record_" + pr_id).value;
                                    let dateValue = BX("input_date_online_record_" + pr_id).value;
                                     
                                    // console.log("Что пришло сюда");
                                    // console.log(pr_id);   

                                    LazyBanner(nameValue, dateValue, pr_id, doctor_id);                                 
                                    
                                    BX.PopupWindowManager.getCurrentPopup().close();
                                }
                            }
                        })
                    ]
            }).show();

        }
        
            
            
            
        
        </script>
        ';




        return $strResult;

    }





}