<?

namespace DMBGEO\EventHandlers;

use DMBGEO\OrderDaData;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class OrderProcess
{

    public static function handler(&$arResult, &$arUserResult, &$arParams)
    {

        $SITE_ID = SITE_ID;

        if (OrderDaData::isEnable($SITE_ID)) {

            \CJSCore::Init(array("jquery"));

            global $APPLICATION;
            $token = OrderDaData::getOptionApiKey($SITE_ID);
            $OrderParams = OrderDaData::getOptionOrderParams($SITE_ID);
            $APPLICATION->SetAdditionalCSS('/bitrix/css/' . OrderDaData::MODULE_ID . '/suggestions.min.css');
            $APPLICATION->AddHeadScript('/bitrix/js/' . OrderDaData::MODULE_ID . '/jquery.suggestions.min.js');
            $params = array();
            $bindingOptions = OrderDaData::getBindingOptions($SITE_ID);

            foreach ($OrderParams as $id => $type) {

                // if (!array_key_exists($id, $arResult['ORDER_PROP']['PRINT'])) continue;
                if ($type == "CITY") {
                    $params[] = array('id' => $id, 'type' => 'ADDRESS', 'value' => 'city');
                } elseif ($type == "INN") {
                    $params[] = array('id' => $id, 'type' => 'PARTY', 'value' => 'inn');
                } elseif ($type == "BIC") {
                    $params[] = array('id' => $id, 'type' => 'BANK', 'value' => 'bic');
                } else {
                    $params[] = array('id' => $id, 'type' => $type, 'value' => 'value');
                }
            }

?>
            <script>
                window.dedata = {
                    token: '<?= $token ?>',
                    params: JSON.parse('<?= json_encode($params); ?>'),
                    options: JSON.parse('<?= json_encode($bindingOptions); ?>')
                }
                $(document).ready(function() {
                    setDeDataParams();
                    BX.addCustomEvent('onAjaxSuccess', setDeDataParams);
                });

                function setDeDataParams() {
                    console.log('init DeData params');
                    var suggestions;
                    // console.log(window.dedata);
                    window.dedata.params.forEach(element => {
                        if ($('#soa-property-' + element.id).length > 0) {
                            suggestions = $('#soa-property-' + element.id).suggestions({
                                token: window.dedata.token,
                                type: element.type,
                                count: 5,
                                /* Вызывается, когда пользователь выбирает одну из подсказок */
                                onSelect: function(suggestion) {
                                    var data = suggestion.data;
                                    if (!data)
                                        return;
                                    // console.log(element, suggestion,options);
                                    switch (element.value) {
                                        case "value":
                                            $(this).val(suggestion.value);
                                            break;
                                        case "unrestricted_value":
                                            $(this).val(suggestion.unrestricted_value);
                                            break;
                                        case "inn":
                                            $(this).val(data.inn);
                                            if (window.dedata.options.COMPANY_NAME != "N") {
                                                $('#soa-property-' + window.dedata.options.COMPANY_NAME).val(suggestion.value);
                                            }
                                            if (window.dedata.options.KPP != "N") {
                                                $('#soa-property-' + window.dedata.options.KPP).val(data.kpp);
                                            }

                                            break;
                                        case "bic":
                                            $(this).val(data.bic);
                                            if (window.dedata.options.BANK != "N") {
                                                $('#soa-property-' + window.dedata.options.BANK).val(suggestion.value);
                                            }
                                            if (window.dedata.options.COR_ACCOUNT != "N") {
                                                $('#soa-property-' + window.dedata.options.COR_ACCOUNT).val(data.correspondent_account);
                                            }
                                            break;
                                        default:
                                            $(this).val(data[element.value]);

                                    }
                                }
                            });
                            // console.log(suggestions);
                        }
                    });

                }
            </script>

<?

        }
    }
}
