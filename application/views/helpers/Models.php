<?php

class Zend_View_Helper_Models extends Zend_View_Helper_Abstract
{

    public function models()
    {
        if ($handle = opendir(APPLICATION_PATH . '/../public/models/')) {
            while (false !== ($filename = readdir($handle))) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $file_parts = pathinfo($filename);

                if (!isset($file_parts['extension'])) {
                    continue;
                }

                if ($file_parts['extension'] == 'json') {
                    $this->view->placeholder('models')->append('');
                    $this->view->headScript()->appendFile('/models/'.$file_parts['filename'].'.json?v=' . Zend_Registry::get('config')->version);
                }

            }
            closedir($handle);
        }
    }

}
