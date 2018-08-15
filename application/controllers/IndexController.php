<?php

class IndexController extends Coret_Controller_AuthorizedFrontend
{
    protected $_redirectNotAuthorized = 'login';
    protected $_playerId;
    protected $_version;

    public function indexAction()
    {
        if ($this->_request->getParam('version')) {
            $version = Zend_Registry::get('config')->version;

            $this->view->headLink()->prependStylesheet('/css/main.css?v=' . $version);

            $this->view->jquery();
            $this->view->headScript()->appendFile('/js/jquery.mousewheel.min.js');
            $this->view->headScript()->appendFile('/js/Tween.js');

            $this->appendJavaScript(APPLICATION_PATH . '/../public/js/');

            $this->view->sound();
            $this->view->title();
//        $this->view->models();
            $this->view->terrain();
            $this->view->translations();
            $this->view->Version();
            $this->view->Websocket($this->_auth->getIdentity());

        } else {
            $this->redirect('/' . Zend_Registry::get('lang') . '/index/index/version/' . Zend_Registry::get('config')->version);
//            echo '/' . Zend_Registry::get('lang') . '/index/index/version/' . Zend_Registry::get('config')->version;
        }
    }

    protected function authorized()
    {
        $this->view->Logout();
    }

    public function unsupportedAction()
    {

    }

    protected function appendJavaScript($path, $dirName = '')
    {
        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $array[] = $entry;
                }
            }

            asort($array);

            foreach ($array as $k => $entry) {
                if (is_dir($path . '/' . $entry)) {
                    $this->appendJavaScript($path . '/' . $entry, $entry);
                } else {
                    if ($dirName) {
                        $this->view->headScript()->appendFile('/js/' . $dirName . '/' . $entry);
                    } else {
                        $this->view->headScript()->appendFile('/js/' . $entry);
                    }
                }
            }


            closedir($handle);
        }
    }
}
