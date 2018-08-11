<?php

class IndexController extends Coret_Controller_AuthorizedFrontend
{
    protected $_redirectNotAuthorized = 'login';
    protected $_playerId;
    protected $_version;

    public function indexAction()
    {
        $version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet('/css/main.css?v=' . $version);

        $this->view->jquery();

        $this->appendJavaScript(APPLICATION_PATH . '/../public/js/', $version);

        $this->view->sound();
        $this->view->title();
//        $this->view->models();
        $this->view->terrain();
        $this->view->translations();
        $this->view->Version();
        $this->view->Websocket($this->_auth->getIdentity());
    }

    protected function authorized()
    {
        $this->view->Logout();
    }

    public function unsupportedAction()
    {

    }

    protected function appendJavaScript($path, $version, $dirName = '')
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
                    $this->appendJavaScript($path . '/' . $entry, $version, $entry);
                } else {
                    if ($dirName) {
                        $this->view->headScript()->appendFile('/js/' . $dirName . '/' . $version . $entry);
                    } else {
                        $this->view->headScript()->appendFile('/js/' . $version . $entry);
                    }
                }
            }


            closedir($handle);
        }
    }
}
