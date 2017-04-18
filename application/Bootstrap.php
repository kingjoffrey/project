<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initDate()
    {
        date_default_timezone_set('Europe/Warsaw');
    }

    protected function _initDb()
    {
        $resource = $this->getPluginResource('db');
        $db = $resource->getDbAdapter();
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    protected function _initView()
    {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();

        $view->addHelperPath(APPLICATION_PATH . '/../library/Coret/View/Helper/');

        $view->doctype('XHTML1_TRANSITIONAL');

        // Set the initial title and separator:
        $view->headTitle('Wars of the Heroes')->setSeparator(' :: ');

//        $view->headLink()->headLink(
//            array(
//                'rel' => 'apple-touch-icon',
//                'sizes' => '57x57',
//                'href' => '/apple-icon-57x57.png'
//            ),
//            'PREPEND');
        $view->headLink()->headLink(
            array(
                'rel' => 'icon',
                'type' => 'image/png',
                'sizes' => '16x16',
                'href' => '/favicon-16x16.png?v=26'
            ),
            'PREPEND');

//        <link rel = "apple-touch-icon" sizes = "57x57" href = "/apple-icon-57x57.png" >
//<link rel = "apple-touch-icon" sizes = "60x60" href = "/apple-icon-60x60.png" >
//<link rel = "apple-touch-icon" sizes = "72x72" href = "/apple-icon-72x72.png" >
//<link rel = "apple-touch-icon" sizes = "76x76" href = "/apple-icon-76x76.png" >
//<link rel = "apple-touch-icon" sizes = "114x114" href = "/apple-icon-114x114.png" >
//<link rel = "apple-touch-icon" sizes = "120x120" href = "/apple-icon-120x120.png" >
//<link rel = "apple-touch-icon" sizes = "144x144" href = "/apple-icon-144x144.png" >
//<link rel = "apple-touch-icon" sizes = "152x152" href = "/apple-icon-152x152.png" >
//<link rel = "apple-touch-icon" sizes = "180x180" href = "/apple-icon-180x180.png" >
//<link rel = "icon" type = "image/png" sizes = "192x192"  href = "/android-icon-192x192.png" >
//<link rel = "icon" type = "image/png" sizes = "32x32" href = "/favicon-32x32.png" >
//<link rel = "icon" type = "image/png" sizes = "96x96" href = "/favicon-96x96.png" >
//<link rel = "icon" type = "image/png" sizes = "16x16" href = "/favicon-16x16.png" >
//<link rel = "manifest" href = "/manifest.json" >
//<meta name = "msapplication-TileColor" content = "#ffffff" >
//<meta name = "msapplication-TileImage" content = "/ms-icon-144x144.png" >
//<meta name = "theme-color" content = "#ffffff" >


        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $view->headMeta()->appendName('keywords', 'turn based, strategic, game');
        $view->headMeta()->appendName('description', 'Wars of the Heroes is a multiplayer, turn based strategic game.');
        $view->headMeta()->appendName('author', 'Bartosz Krzeszewski');
        $view->headMeta()->appendName('date', '2011');
        $view->headMeta()->appendName('copyright', 'Bartosz Krzeszewski 2011');
//        $view->headMeta()->appendName('', '');
        $view->headMeta()->appendName('viewport', 'user-scalable=no');
//        $view->headMeta()->appendName('viewport', 'width=1000, user-scalable=no');
//        $view->headMeta()->appendName('viewport', 'user-scalable=no, initial-scale=2, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height');

        $view->headMeta()->appendName('google-site-verification', 'XVxNHItfpHO6b643-xv5cPacS54KgbNXeE1EfaBawuI');


    }

    protected function _initRegisterLogger()
    {
        $logger = new Zend_Log();
        $logger->setTimestampFormat("H:i:s");
        $logger->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../log/' . date('Y-m-d') . '.log'));
        Zend_Registry::set('Zend_Log', $logger);
    }

    protected function _initSession()
    {
        Zend_Session::start();
    }

    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
    }

    public function _initRoutes()
    {
        $this->bootstrap('FrontController');
        $this->_frontController = $this->getResource('FrontController');
        $router = $this->_frontController->getRouter();

        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            $locale = new Zend_Locale();
            $lang = $locale->getLanguage();
        }

        $langRoute = new Zend_Controller_Router_Route(
            ':lang/',
            array(
                'lang' => $lang
            )
        );

        $defaultRoute = new Zend_Controller_Router_Route(
            ':controller/:action/*',
            array(
                'module' => 'default',
                'controller' => 'index',
                'action' => 'index'
            )
        );

        $defaultRoute = $langRoute->chain($defaultRoute);

        $adminRoute = new Zend_Controller_Router_Route(
            'admin/:controller/:action/*',
            array(
                'module' => 'admin',
                'controller' => 'index',
                'action' => 'index'
            )
        );

        $router->addRoute('langRoute', $langRoute);
        $router->addRoute('defaultRoute', $defaultRoute);
        $router->addRoute('adminRoute', $adminRoute);
    }

    protected function _initLanguage()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Coret_Controller_Plugin_Language());
    }

    protected function _initAutoload()
    {
        require_once APPLICATION_PATH . '/../library/Facebook/autoload.php';
    }
}

