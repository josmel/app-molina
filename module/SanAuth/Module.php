<?php
namespace SanAuth;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class Module implements AutoloaderProviderInterface
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            
            'factories' => array(
                'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
                
                'SanAuth\Model\MyAuthStorage' => function ($sm)
                {
                    return new \SanAuth\Model\MyAuthStorage('zf_tutorial');
                },
                
                'AuthService' => function ($sm)
                {
                    $dbTableAuthAdapter = $sm->get('TableAuthService');
                    
                    $authService = new AuthenticationService();
                    $authService->setStorage(new \Zend\Authentication\Storage\Session('Auth')); // $authService->setStorage($sm->get('SanAuth\Model\MyAuthStorage')); //
                    $authService->setAdapter($dbTableAuthAdapter);
                    return $authService;
                },
                'TableAuthService' => function ($sm)
                {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'user', 'userName', 'password', 'SHA1(?)'); //
                    return $dbTableAuthAdapter;
                }
            )
        )
        ;
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        // $this->getDbDatos($e);
        
        $app = $e->getApplication();
        $app->getEventManager()
            ->getSharedManager()
            ->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function ($e)
      {
            $locator = $e->getApplication()
                ->getServiceManager();
            $authAdapter = $locator->get('AuthService');
            $controller = $e->getTarget();
            $routeMatch = $e->getRouteMatch();
           // $actionName = $routeMatch->getParam('action', 'not-found');
            $actionName ='auth//authenticate';
            
            if ($actionName == 'login') {
                if ($authAdapter->hasIdentity() === true) {
                    $storage = new \Zend\Authentication\Storage\Session('Auth');
                    $session = $storage->read();
                    $controller->layout()->session = $session;
                    return $controller->redirect()
                            ->toRoute('/event');
                } else {
                    return;
                }
            } else {
                $storage = new \Zend\Authentication\Storage\Session('Auth');
                $session = $storage->read();
                $controller->layout()->session = $session;
                return;
            }

        }, 100);
    }

}
