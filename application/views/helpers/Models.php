<?php

class Zend_View_Helper_Models extends Zend_View_Helper_Abstract
{

    public function models()
    {
//        $version = Zend_Registry::get('config')->version;
//        $mUnit = new Application_Model_Unit();
//
//        foreach ($mUnit->getUnitsNames() as $row) {
//            echo $row['name'];
//            $fileName = APPLICATION_PATH . '/../public/models/' . $row['name'] . '.json';
//            if (file_exists($fileName)) {
//                $this->view->placeholder('models')->append('');
//                $this->view->headScript()->appendFile('/models/' . $fileName . '.json?v=' . $version);
//            }
//        }
//
//        return;

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
                    $this->view->headScript()->appendFile('/models/' . $file_parts['filename'] . '.json?v=' . Zend_Registry::get('config')->version);
                }

            }
            closedir($handle);
        }
    }

}
