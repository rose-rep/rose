<?php



namespace Citfact\SiteCore\Tools\Event;

interface DispatcherInterface
{
    /**
     * @param  string $moduleName
     * @return void
     */
    public function registerByModule($moduleName);
}
