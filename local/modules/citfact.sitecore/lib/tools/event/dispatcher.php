<?php



namespace Citfact\SiteCore\Tools\Event;

use Symfony\Component\Finder\Finder;
use Citfact\SiteCore\Tools\Module\Module;
use Bitrix\Main\EventManager;

class Dispatcher implements DispatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function registerByModule($moduleName)
    {
        $module = new Module($moduleName);
        $modulePathEvent = $module->getModulePath();

        // TODO: Clear out and fix if need, supposed bug
        if (!is_dir($modulePathEvent.'/lib')) {
            throw new DispatcherException(sprintf('Invalid path module %s', $modulePathEvent));
        }

        // TODO: Clear out and fix it if need, supposed bug. If dir does not exist then Finder throws exception.
        $modulePathEvent .= '/lib/eventlistener';
        $prefix = $module->getNamespace().'\\EventListener';

        $finder = new Finder();
        $finder->files()->name('*listener.php')->in($modulePathEvent);
        foreach ($finder as $file) {
            $class =  $prefix.'\\'.$file->getBasename('.php');
            $reflection = new \ReflectionClass($class);
            if ($reflection->isSubclassOf('Citfact\\Sitecore\\Tools\\Event\\ListenerInterface') && !$reflection->isAbstract()) {
                $this->addListener($reflection->newInstance(), $class);
            }
        }

        $finder = new Finder();
        $finder->files()->name('*subscriber.php')->in($modulePathEvent);
        foreach ($finder as $file) {
            $class = $prefix.'\\'.$file->getBasename('.php');
            $reflection = new \ReflectionClass($class);
            if ($reflection->isSubclassOf('Citfact\\Sitecore\\Tools\\Event\\SubscriberInterface') && !$reflection->isAbstract()) {
                $this->addSubscriber($reflection->newInstance(), $class);
            }
        }
    }

    /**
     * @param ListenerInterface $listener
     * @param string            $class
     */
    protected function addListener(ListenerInterface $listener, $class)
    {
        $event = $listener->getEvent();
        $eventManager = EventManager::getInstance();

        if ($this->isValidOptions($event, $class)) {
            if (!isset($event['sort'])) {
                $event['sort'] = 100;
            }

            $callback = sprintf('%s::%s', $class, $event['method']);
            $eventManager->addEventHandler($event['module'], $event['event'], $callback, false, $event['sort']);
        }
    }

    /**
     * @param SubscriberInterface $subscriber
     * @param string              $class
     */
    protected function addSubscriber(SubscriberInterface $subscriber, $class)
    {
        $subscriberList = $subscriber->getSubscribedEvents();
        $eventManager = EventManager::getInstance();

        foreach ($subscriberList as $subscribe) {
            if ($this->isValidOptions($subscribe, $class)) {
                if (!isset($subscribe['sort'])) {
                    $subscribe['sort'] = 100;
                }

                $callback = sprintf('%s::%s', $class, $subscribe['method']);
                $eventManager->addEventHandler($subscribe['module'], $subscribe['event'], $callback, false, $subscribe['sort']);
            }
        }
    }

    /**
     * @param $options
     * @param $class
     * @return bool
     * @throws \Exception
     */
    protected function isValidOptions($options, $class)
    {
        if (!isset($options['module']) || !isset($options['event']) || !isset($options['method'])) {
            throw new \Exception(sprintf('Invalid options in event = %s', $class));
        }

        return true;
    }
}
