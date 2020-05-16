<?php

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \DMBGEO\OrderDaData;

$module_id = 'dmbgeo.orderdadata';
$module_path = str_ireplace($_SERVER["DOCUMENT_ROOT"], '', __DIR__) . $module_id . '/';

\CModule::IncludeModule('main');
\CModule::IncludeModule($module_id);
\CModule::IncludeModule('iblock');

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);
if ($APPLICATION->GetGroupRight($module_id) < "S") {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}



$orderFieldsPerson=OrderDaData::getOrderFields();

$DaDataOptions['N'] = 'Не выбранно';
$DaDataOptions['NAME'] = 'Подсказки по ФИО';
$DaDataOptions['ADDRESS'] = 'Подсказки по адресу';
$DaDataOptions['CITY'] = 'Подсказки по Городу';
$DaDataOptions['FIAS'] = 'Подсказки по ФИАС';
$DaDataOptions['PARTY'] = 'Подсказки по организациям и ИП';
$DaDataOptions['EMAIL'] = 'Подсказки по email';
$DaDataOptions['BANK'] = 'Подсказки по банкам';
$DaDataOptions['INN'] = 'Подсказки по ИНН';
$DaDataOptions['BIC'] = 'Подсказки по БИК';

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
//получим инфоблоки пользователей на сайте, чтоб добавить в настройки
$SITES = OrderDaData::getSites();
$OPTION_ORDER_PARAMS=Array();
foreach ($SITES as $SITE) {

	$OPTIONS = array(
		'Настройки интеграции c DaData',
		array('OPTION_MODULE_STATUS_' . $SITE['LID'], Loc::getMessage('OPTION_MODULE_STATUS'), '', array('checkbox', "Y")),
		array('OPTION_API_KEY_' . $SITE['LID'], Loc::getMessage('OPTION_API_KEY'), '', array('text', 80)),
		array('OPTION_STANDART_KEY_' . $SITE['LID'], Loc::getMessage('OPTION_STANDART_KEY'), '', array('text', 80)),

	);
	$OrdrFieldsParam['N'] = 'Не выбранно';
	foreach ($orderFieldsPerson as $PersonId  => $orderFields) {

		$OPTIONS[] = 'Настройка подсказок [ '.reset($orderFields)['PERSON_TYPE']['NAME'] . ' => ' . reset($orderFields)['PERSON_TYPE']['ID'] . ' ]';
		foreach ($orderFields as $fieldID => $OrdrField) {
			$OPTION_ORDER_PARAMS[$fieldID]=$fieldID;
			$OrdrFieldsParam[$fieldID]=$OrdrField['NAME'] . ' [' . $OrdrField['ID'] . ']';
			$OPTIONS[] = array('OPTION_ORDER_FIELD_' . $fieldID . '_' . $SITE['LID'], $OrdrField['NAME'] . ' [' . $OrdrField['ID'] . ']', 'N', array("selectbox", $DaDataOptions));
			$params[] = 'OPTION_ORDER_FIELD_' . $fieldID . '_' . $SITE['LID'];
		}
	}
	$OPTIONS[] = 'Настройка привязки полей';
	$OPTIONS[] = array('OPTION_KPP_'. $SITE['LID'], 'Привязка КПП', 'N', array("selectbox", $OrdrFieldsParam));
	$OPTIONS[] = array('OPTION_COMPANY_NAME_'. $SITE['LID'], 'Привязка Наименования компании', 'N', array("selectbox", $OrdrFieldsParam));
	$OPTIONS[] = array('OPTION_BANK_'. $SITE['LID'], 'Привязка Наименования банка', 'N', array("selectbox", $OrdrFieldsParam));
	$OPTIONS[] = array('OPTION_COR_ACCOUNT_'. $SITE['LID'], 'Привязка Корреспондентского счета', 'N', array("selectbox", $OrdrFieldsParam));
	
	$params[] = 'OPTION_KPP_' . $SITE['LID'];
	$params[] = 'OPTION_COMPANY_NAME_' . $SITE['LID'];
	$params[] = 'OPTION_BANK_' . $SITE['LID'];
	$params[] = 'OPTION_COR_ACCOUNT_' . $SITE['LID'];
	$aTabs[] = array(
		'DIV' => $SITE['LID'],
		'TAB' => $SITE['NAME'],
		'OPTIONS' => $OPTIONS,
	);

	$params[] = 'OPTION_MODULE_STATUS_' . $SITE['LID'];
	$params[] = 'OPTION_API_KEY_' . $SITE['LID'];
	$params[] = 'OPTION_STANDART_KEY_' . $SITE['LID'];
}

unset($orderFields['N']);
$params[] = 'OPTION_ORDER_PARAMS';

if ($request->isPost() && $request['Apply'] && check_bitrix_sessid()) {

	foreach ($params as $param) {
		if (array_key_exists($param, $_POST) === true) {
			Option::set($module_id, $param, is_array($_POST[$param]) ? implode(",", $_POST[$param]) : $_POST[$param]);
		} else {
			Option::set($module_id, $param, "N");
		}
	}
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<? $tabControl->Begin(); ?>

<form method='post' action='<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request['mid']) ?>&amp;lang=<?= $request['lang'] ?>' name='DMBGEO_ORDER_SPLIT_settings'>

	<? $n = count($aTabs); ?>
	<? foreach ($aTabs as $key => $aTab) :

		if ($aTab['OPTIONS']) : ?>

			<? $tabControl->BeginNextTab(); ?>

			<? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>
			<? endif ?>;
		<? endforeach; ?>
		<?

		$tabControl->Buttons(); ?>
		<input type="hidden" name="OPTION_ORDER_PARAMS" value="<?= implode(',', $OPTION_ORDER_PARAMS) ?>">
		<input type="submit" name="Apply" value="<? echo GetMessage('MAIN_SAVE') ?>">
		<input type="reset" name="reset" value="<? echo GetMessage('MAIN_RESET') ?>">
		<?= bitrix_sessid_post(); ?>
</form>
<? $tabControl->End(); ?>