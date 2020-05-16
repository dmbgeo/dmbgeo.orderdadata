<?
use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class dmbgeo_orderdadata extends CModule
{
    public $MODULE_ID = 'dmbgeo.orderdadata';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function dmbgeo_orderdadata()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("ORDERDADATA_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("ORDERDADATA_MODULE_DESC");

        $this->PARTNER_NAME = getMessage("ORDERDADATA_PARTNER_NAME");
        $this->PARTNER_URI = getMessage("ORDERDADATA_PARTNER_URI");
        $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php',
        );
    }


    
    public function InstallEvents()
    {
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler("sale", "OnSaleComponentOrderOneStepProcess", $this->MODULE_ID, '\DMBGEO\EventHandlers\OrderProcess', "handler");

    }

    public function UnInstallEvents()
    {
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("sale", "OnSaleComponentOrderOneStepProcess", $this->MODULE_ID, '\DMBGEO\EventHandlers\OrderProcess', "handler");

    }

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function InstallFiles($arParams = array())
    {

        $path = $this->GetPath() . "/install/install_files";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)) {
            $filesDirectroy=new \Bitrix\Main\IO\Directory($path, $siteId = null);
            foreach($filesDirectroy->getChildren() as $directory){
                $directoryName=$directory->getName();
                
                if($directoryName == 'css'){
                    CopyDirFiles($directory->getDirectoryName().'/'.$directoryName, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/", true, true);
                }
                if($directoryName == 'js'){
                    CopyDirFiles($directory->getDirectoryName().'/'.$directoryName, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/", true, true);
                }
                if($directoryName == 'admin'){
                    CopyDirFiles($directory->getDirectoryName().'/'.$directoryName, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/", true, true);
                    if ($dir = opendir($directory->getDirectoryName().'/'.$directoryName)) {
                        while (false !== $item = readdir($dir)) {
                            if (in_array($item, $this->exclusionAdminFiles)) {
                                continue;
                            }
        
                            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $item,
                                '<' . '? require("'. $directory->getDirectoryName().'/'.$directoryName . '/' . $item . '");?' . '>');
                        }
                        closedir($dir);
                    }
                }
                if($directoryName == 'components'){
                    
                    if(!\Bitrix\Main\IO\Directory::isDirectoryExists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/dmbgeo/")){
                        \Bitrix\Main\IO\Directory::createDirectory($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/dmbgeo/");
                    }

                    CopyDirFiles($directory->getDirectoryName().'/'.$directoryName, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/dmbgeo/", true, true);
                }
                if($directoryName == 'tools'){
                    CopyDirFiles($directory->getDirectoryName().'/'.$directoryName, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/", true, true);
                }

                if($directoryName == 'files'){
                    $this->copyArbitraryFiles();
                }
               
            }
        
        }

        return true;
    }

    public function UnInstallFiles()
    {


        $path = $this->GetPath() . "/install/install_files";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)) {
            $filesDirectroy=new \Bitrix\Main\IO\Directory($path, $siteId = null);
            foreach($filesDirectroy->getChildren() as $directory){
                $directoryName=$directory->getName();
                if($directoryName == 'css'){
                    \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/".$this->MODULE_ID.'/');
                }
                if($directoryName == 'js'){
                    \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/".$this->MODULE_ID.'/');
                }
                if($directoryName == 'admin'){
                    DeleteDirFiles($directory->getDirectoryName().'/'.$directoryName, $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');
                    if ($dir = opendir($path)) {
                        while (false !== $item = readdir($dir)) {
                            if (in_array($item, $this->exclusionAdminFiles)) {
                                continue;
                            }
        
                            \Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                        }
                        closedir($dir);
                    }
                }
                if($directoryName == 'components'){
                    DeleteDirFiles($directory->getDirectoryName().'/'.$directoryName.'/dmbgeo/', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/dmbgeo');
                }
                if($directoryName == 'tools'){
                    \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/".$this->MODULE_ID.'/');
                }

                if($directoryName == 'files'){
                    $this->deleteArbitraryFiles();
                }
               
            }
        
        }

       
        return true;
    }

    public function copyArbitraryFiles()
    {
        $rootPath = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath() . '/install/install_files/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            $destPath = $rootPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            ($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
        }
    }

    public function deleteArbitraryFiles()
    {
        $rootPath = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath() . '/install/install_files/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            if (!$object->isDir()) {
                $file = str_replace($localPath, $rootPath, $object->getPathName());
                \Bitrix\Main\IO\File::deleteFile($file);
            }
        }
    }

    public function UnInstallOptions()
    {
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
    }


    public function DoInstall()
    {

        global $APPLICATION;
        if ($this->isVersionD7()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
            $this->InstallFiles();

        } else {
            $APPLICATION->ThrowException(Loc::getMessage("ORDERDADATA_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("ORDERDADATA_INSTALL"), $this->GetPath() . "/install/step.php");
    }

    public function DoUninstall()
    {

        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        // $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallOptions();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("ORDERDADATA_UNINSTALL"), $this->GetPath() . "/install/unstep.php");
    }
}
