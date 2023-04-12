<?php



namespace Citfact\SiteCore\Tools\Module;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

class Module implements ModuleInterface
{
    const DELIMITER_MODULE_NAME = '.';

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @param string $moduleName
     */
    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentsPath($absolute = true)
    {
        $componentsPath = '';
        $modulePath = $this->getModulePath();

        if (strpos($modulePath, Loader::BITRIX_HOLDER)) {
            $componentsPath = Loader::BITRIX_HOLDER.'/components';
        } elseif (strpos($modulePath, Loader::LOCAL_HOLDER)) {
            $componentsPath = Loader::LOCAL_HOLDER.'/components';
        }

        if ($absolute) {
            $documentRoot = Loader::getDocumentRoot();
            $componentsPath = $documentRoot.'/'.$componentsPath;
        }

        return $componentsPath;
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return ModuleManager::isModuleInstalled($this->moduleName);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallDate()
    {
        $installedModules = ModuleManager::getInstalledModules();
        if (isset($installedModules[$this->moduleName])) {
            return $installedModules[$this->moduleName]['DATE_ACTIVE'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        if (strpos($this->moduleName, self::DELIMITER_MODULE_NAME) === false) {
            return ucfirst($this->moduleName);
        }

        $moduleName = explode(self::DELIMITER_MODULE_NAME, $this->moduleName);
        foreach ($moduleName as $key => $part) {
            $moduleName[$key] = ucfirst($part);
        }

        return implode('\\', $moduleName);
    }

    /**
     * {@inheritdoc}
     */
    public function getModulePath()
    {
        $documentRoot = Loader::getDocumentRoot();
        $modulePathBitrix = $documentRoot.'/'.Loader::BITRIX_HOLDER.'/modules/'.$this->moduleName;
        $modulePathLocal = $documentRoot.'/'.Loader::LOCAL_HOLDER.'/modules/'.$this->moduleName;

        if (is_dir($modulePathBitrix)) {
            return $modulePathBitrix;
        } elseif (is_dir($modulePathLocal)) {
            return $modulePathLocal;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->moduleName;
    }
}
