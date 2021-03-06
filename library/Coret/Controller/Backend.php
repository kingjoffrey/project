<?php

abstract class Coret_Controller_Backend extends Zend_Controller_Action
{

    public $params = array();
    protected $itemCountPerPage = 20;

    public function init()
    {
        parent::init();

        $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
        if (!$auth->hasIdentity()) {
            $this->redirect('/admin/login');
        }

        $this->_helper->layout->setLayout('admin');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl('/css/admin/core-t.css'));
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl('/css/sceditor/themes/default.min.css'));

        $this->view->jquery();

        $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/core-t.js'));
//        $this->view->headScript()->appendFile($this->view->baseUrl('/js/jquery.sceditor.min.js'));

        $this->view->headMeta()->appendHttpEquiv('Content-Language', 'pl');

        $this->view->menu();
        $this->view->copyright();

        $this->view->controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
    }

    public function indexAction()
    {
//        $this->view->headScript()->appendFile($this->view->baseUrl('/js/adminajax.js'));
        $this->view->headScript()->appendScript('var controller = "' . $this->view->controllerName . '"');

        $this->indexEnding('Admin_Model_' . ucfirst($this->view->controllerName));
    }

    protected function indexEnding($className)
    {
        $this->params['id_lang'] = Zend_Registry::get('config')->id_lang;
        $this->view->m = new $className($this->params);

        $columns = array();
        $columnsLang = array();

        $c = $this->view->m->getColumns();
        foreach (array_keys($c) as $columnName) {
            $columns[] = $columnName;
        }

        if ($c = $this->view->m->getColumnsLang()) {
            foreach (array_keys($c) as $columnName) {
                $columnsLang[] = $columnName;
            }
        }

        $this->view->paginator = new Zend_Paginator($this->view->m->getPagination($columns, $columnsLang));
//        $this->view->paginator = new Zend_Paginator($this->view->m->getPagination());
        $this->view->paginator->setCurrentPageNumber($this->_request->getParam('page'));
        $this->view->paginator->setItemCountPerPage($this->itemCountPerPage);
    }

    public function addAction()
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params);

        $className = 'Admin_Form_' . ucfirst($this->view->controllerName);

        if (class_exists($className)) {
            $this->view->form = new $className();
        } else {
            $this->addForm($model);
        }

        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                try {
                    $model->save($this->view->form->getValues());
                    $this->redirect($this->view->url());
                } catch (Exception $e) {
                    echo $e->getMessage();
                    exit;
                }
            } else {
                $this->view->form->addElement('submit', 'submit', array('label' => 'Popraw'));
            }
        } else {
            $this->view->form->addElement('submit', 'submit', array('label' => 'Dodaj'));
        }
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');
        if (!Zend_Validate::is($id, 'Digits')) {
            throw new Exception('Brak id');
        }

        if ($this->_request->getParam('yes')) {
            $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
            $model = new $className($this->params, $id);
            try {
                $model->deleteElement();
                $this->myRedirect();
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        } else {
            $this->view->ask = true;
        }
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');
        if (!Zend_Validate::is($id, 'Digits')) {
            throw new Exception('No valid ID');
        }

        $className = 'Admin_Form_' . ucfirst($this->view->controllerName);

        if (class_exists($className)) {
            $this->view->form = new $className();
        }

        if ($this->_request->isPost()) {
            $this->editHandlePost($id);
        } else {
            $this->editHandleElse($id);
        }
        $this->addGalleryForm();
    }

    protected function editHandlePost($id)
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params, $id);

        $this->addForm($model, $id);

        if ($this->view->form->isValid($this->_request->getPost())) {
            try {
                $model->save($this->view->form->getValues());
                $this->myRedirect();
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        } else {
            $this->view->form->addElement('submit', 'submit', array('label' => 'Popraw'));
        }
    }

    protected function myRedirect()
    {
        $this->redirect('/admin/' . $this->view->controllerName);
    }

    protected function editHandleElse($id)
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params, $id);

        $this->addForm($model, $id);

        $element = $model->getElement();
        $this->view->form->populate($element);
        $this->view->form->addElement('submit', 'submit', array('label' => 'Zmień'));
        $this->view->form->setDefault('id', $id);

        if (isset($element['image'])) {
            $this->view->img = $this->view->controllerName . '_' . $id . '.jpg';
        }

        $columnsLang = $model->getColumnsLang();
        if ($columnsLang) {
            $this->addLangForm($id, $columnsLang);
            $this->view->JavascriptLang($this->view->controllerName, array_keys($columnsLang));
        }
    }

    protected function addForm($model, $id = null)
    {
        if (isset($this->view->form)) {
            return;
        }

        $columns = $model->getColumnsAll();

        $this->view->form = new Zend_Form();

        foreach ($columns as $key => $row) {
            if (isset($row['active']['form']) && !$row['active']['form']) {
                continue;
            }
            $className = 'Coret_Form_' . ucfirst($row['type']);
            $attributes = array('name' => $key);
            if (isset($row['label'])) {
                $attributes['label'] = $row['label'];
            }
            if (isset($row['required'])) {
                $attributes['required'] = $row['required'];
            }
            if (isset($row['validators'])) {
                $attributes['validators'] = $row['validators'];
            }
            if ($row['type'] == 'select') {
                $methodName = 'get' . ucfirst($key) . 'Array';
                $attributes['opt'] = $model->$methodName();
            }

            if (isset($row['url'])) {
                $attributes['url'] = $row['url'];
            }

            $f = new $className($attributes);
            $this->view->form->addElements($f->getElements());
        }

        if ($id) {
            $fId = new Coret_Form_Id();
            $this->view->form->addElements($fId->getElements());

            $this->view->form->setDefault('id', $id);
        }

        if ($model->isLang()) {
            $f = new Coret_Form_IdLang();
            $this->view->form->addElements($f->getElements());
            $this->view->form->setDefault('id_lang', Zend_Registry::get('config')->id_lang);
        }
    }

    protected function addLangForm($id, $columnsLang)
    {
        $this->view->formLang = new Zend_Form();
        $fLang = new Coret_Form_Lang();
        $this->view->formLang->addElements($fLang->getElements());

        foreach ($columnsLang as $key => $row) {

            $className = 'Coret_Form_' . ucfirst($row['type']);
            $f = new $className(array('name' => $key, 'label' => $row['label']));
            $this->view->formLang->addElements($f->getElements());
        }

        $fId = new Coret_Form_Id();
        $this->view->formLang->addElements($fId->getElements());

        $this->view->formLang->setDefault('id', $id);
        $this->view->formLang->setAttrib('id', 'lang');
    }

    protected function addGalleryForm()
    {

    }

    private function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        $text = str_replace('ł', 'l', $text);
        $text = str_replace('ś', 's', $text);
        $text = str_replace('ć', 'c', $text);
        $text = str_replace('ó', 'o', $text);

        // transliterate
        if (function_exists('iconv')) {
//            $text = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($text));
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

