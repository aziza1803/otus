<?php

namespace UserTypes;

use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;

class BookingProceduresProperty
{
    public static function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE'        => PropertyTable::TYPE_STRING,
            'USER_TYPE'            => 'booking_procedures',
            'DESCRIPTION'          => 'Запись на процедуры',
            'GetPropertyFieldHtml' => array(self::class, 'GetPropertyFieldHtml'),
            'GetPublicViewHTML'    => array(self::class, 'GetPublicViewHTML'),
            'GetAdminListViewHTML' => array(self::class, 'GetAdminListViewHTML'),
        );
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
 
        $doctorId = $arProperty['ELEMENT_ID'] ?? 0;
        $procedures = [];

        if ($doctorId && Loader::includeModule('iblock'))
        {
            $res = \CIBlockElement::GetProperty($arProperty['IBLOCK_ID'], $doctorId, array(), array("CODE" => "PROC_IDS_MULTI"));
            while ($ob = $res->GetNext())
            {
                $procedures[] = $ob['VALUE'];
            }
            if (empty($procedures)) {
                return 'У данного врача нет процедур.';
            }
            $procedureOptions = [];
            foreach ($procedures as $procedureId)
            {
                $procedure = \CIBlockElement::GetByID($procedureId)->GetNext();
                if ($procedure)
                {
                    $procedureOptions[] = [
                        'ID' => $procedureId,
                        'NAME' => $procedure['NAME'],
                    ];
                }
            }

            $procedureOptionsJson = json_encode($procedureOptions, JSON_UNESCAPED_UNICODE);

            $html = '<button class="book_procedure_button" type="button" id="book_procedure_button_' . $doctorId . '" data-procedures=\'' . json_encode($procedureOptions, JSON_UNESCAPED_UNICODE) . '\'>Записать</button>';

            $html .= <<<EOD
<script>
if (!document.getElementById('bookingScript')) {
    var scriptIdentifier = document.createElement('div');
    scriptIdentifier.id = 'bookingScript';
    document.body.appendChild(scriptIdentifier);

    BX.ready(function() {
        var bookButtons = document.querySelectorAll(".book_procedure_button");
        bookButtons.forEach(function(bookButton) {
            bookButton.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();  

                var doctorId = this.id.split('_').pop();

                var procedures = JSON.parse(this.getAttribute('data-procedures'));

                var optionsHtml = '';
                procedures.forEach(function(procedure) {
                    optionsHtml += '<option value="' + procedure.ID + '">' + BX.util.htmlspecialchars(procedure.NAME) + '</option>';
                });
                var formContent = '<form id="bookingForm_' + doctorId + '">' +
                    '<label>ФИО пациента: <input type="text" name="patient_name"></label><br>' +
                    '<label>Время записи: <input type="datetime-local" name="appointment_time"></label><br>' +
                    '<label>Процедура: <select name="procedure_id">' +
                    optionsHtml +
                    '</select></label><br>' +
                    '<input type="hidden" name="doctor_id" value="' + doctorId + '">' +
                    '<button type="submit">Записаться</button>' +
                    '</form>';
                BX.PopupWindowManager.create("bookingPopup_" + doctorId, null, {
                    content: formContent,
                    titleBar: {content: BX.create("span", {html: "Запись на процедуру"})},
                    closeIcon: {right: "20px", top: "10px"},
                    width: 400,
                    height: 300,
                    overlay: {backgroundColor: "black", opacity: "80"},
                    autoHide: true,
                    buttons: []
                }).show();

                var form = document.getElementById("bookingForm_" + doctorId);
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); 
                    event.stopPropagation(); 

                    var formData = {
                        patient_name: form.patient_name.value,
                        appointment_time: form.appointment_time.value,
                        procedure_id: form.procedure_id.value,
                        doctor_id: form.doctor_id.value
                    };

                    BX.ajax({
                        url: "/local/ajax/booking.php",
                        data: formData,  // Передаем объект данных напрямую
                        method: "POST",
                        dataType: "json",
                        onsuccess: function(response) {
                            console.log('Raw response:', response);
                            try {
                                if(response.status == "success") {
                                    alert("Запись успешно создана");
                                    BX.PopupWindowManager.getCurrentPopup().close();
                                    window.location.href;
                                } else {
                                    alert(response.message);
                                }
                            } catch (e) {
                                console.error('JSON parsing error:', e);
                                alert("Некорректный ответ от сервера");
                            }
                        },
                        onfailure: function(error) {
                            console.error('AJAX error:', error);
                            alert("Ошибка при отправке запроса");
                        }
                    });

                    form.removeEventListener('submit', arguments.callee);
                });
            });
        });
    });
}
</script>
EOD;
            return $html;
        }
        return 'Процедуры не найдены.';
    }


    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName);
    }
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return 'Запись на процедуры';
    }
}
