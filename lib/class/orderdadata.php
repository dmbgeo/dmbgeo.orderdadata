<?

namespace DMBGEO;

use \Bitrix\Main\Config\Option;

class OrderDaData
{
    const MODULE_ID = 'dmbgeo.orderdadata';

    public static function isEnable($SITE_ID = SITE_ID)
    {
        if (Option::get(SELF::MODULE_ID, 'OPTION_MODULE_STATUS_' . $SITE_ID) == 'Y') {
            return true;
        }
        return false;
    }


    public function getDaDataClient($SITE_ID = SITE_ID)
    {

        $api_key = self::getOptionApiKey($SITE_ID);
        $standart_key = self::getOptionStandartKey($SITE_ID);
        if (!empty($api_key) && !empty($standart_key)) {
            return new Dadata\Client(new \GuzzleHttp\Client(), [
                'token' => $api_key,
                'secret' => $standart_key,
            ]);
        }

        return null;
    }
    public static function getOptionApiKey($SITE_ID = SITE_ID)
    {
        return Option::get(SELF::MODULE_ID, 'OPTION_API_KEY_' . $SITE_ID);
    }
    public static function getOptionStandartKey($SITE_ID = SITE_ID)
    {
        return Option::get(SELF::MODULE_ID, 'OPTION_STANDART_KEY_' . $SITE_ID);
    }

    public static function getBindingOptions($SITE_ID = SITE_ID)
    {   
        $result=Array();
        $result['KPP']=Option::get(SELF::MODULE_ID, 'OPTION_KPP_' . $SITE_ID);
        $result['COMPANY_NAME']=Option::get(SELF::MODULE_ID, 'OPTION_COMPANY_NAME_' . $SITE_ID);
        $result['BANK']=Option::get(SELF::MODULE_ID, 'OPTION_BANK_' . $SITE_ID);
        $result['COR_ACCOUNT']=Option::get(SELF::MODULE_ID, 'OPTION_COR_ACCOUNT_' . $SITE_ID);
        
        return $result;
    }
    public static function getOptionOrderParams($SITE_ID = SITE_ID)
    {
        $keys =  Option::get(SELF::MODULE_ID, 'OPTION_ORDER_PARAMS');
        $keys = explode(',', $keys);
        $params = array();
        foreach ($keys as $key) {
            $val = self::getOption('OPTION_ORDER_FIELD_' . $key, $SITE_ID);
            if ($val !== "N") {
                $params[$key] = self::getOption('OPTION_ORDER_FIELD_' . $key, $SITE_ID);
            }
        }
        return $params;
    }

    public static function getOrderFields()
    {
        $orderFields = array();
        if (!\CModule::IncludeModule('sale')) return $orderFields;
        $db_props = \CSaleOrderProps::GetList(array("PERSON_TYPE_ID" => 'ASC', "SORT" => "ASC"), array('ACTIVE' => 'Y'));

        while ($props = $db_props->Fetch()) {
            $props['PERSON_TYPE'] = \CSalePersonType::GetByID($props['PERSON_TYPE_ID']);
            $orderFields[$props['PERSON_TYPE_ID']][$props['ID']] = $props;
        }
        return $orderFields;
    }




    public static function getSites()
    {
        $ids = array();
        $rsSites = \CSite::GetList($by = "sort", $order = "desc");
        while ($arSite = $rsSites->Fetch()) {
            $ids[] = $arSite;
        }

        return $ids;
    }


    public static function getOption($name, $SITE_ID = SITE_ID)
    {
        return Option::get(SELF::MODULE_ID, $name . '_' . $SITE_ID);
    }
}
