<?php

abstract class Coret_Model_ParentDb extends Coret_Db_Table_Abstract
{

    /**
     * @var int
     */
    protected $_id;

    /**
     * @var array
     */
    protected $_params;

    protected $_columns;
    protected $_adminId = 'adminId';
    protected $_sequence = '';

    protected $_select;

    /**
     * @param array $params
     * @param int $id
     */
    public function __construct(Array $params = array(), $id = 0)
    {
        parent::__construct();

        $this->_id = intval($id);
        $this->_params = $params;

        if (Zend_Registry::get('config')->resources->db->adapter == 'pdo_mysql') {
            $this->_db->query("SET NAMES 'utf8'");
            $this->_db->query("set character set 'utf8'");
        }

        $this->mixColumns();
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $this->_name . '` (
            `' . $this->_primary . '` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        foreach ($this->_columns as $columnName => $val) {
            switch ($val['type']) {
                case 'varchar':
                    $sql .= '`' . $columnName . '` varchar(256) NOT NULL,';
                    break;
                case 'text':
                    $sql .= '`' . $columnName . '` text NOT NULL,';
                    break;
                case 'checkbox':
                    $sql .= '`' . $columnName . '` bool NOT NULL,';
                    break;
                case 'numeric':
                    $sql .= '`' . $columnName . '` integer NOT NULL,';
                    break;
            }
        }
        $sql .= 'PRIMARY KEY(`' . $this->_primary . '`),
            UNIQUE KEY `' . $this->_primary . '` (`' . $this->_primary . '`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1';
        $this->_db->query($sql);
    }

    /**
     * @param $post
     * @return bool|mixed|type
     */
    public function save($post)
    {
        $dane = $this->prepareData($post);

        if (isset($post['id']) && $post['id']) {
            $this->_id = $post['id'];
            $dane['data'] = $this->handleImg($dane, $post['id']);
            if ($dane['data']) {
                $this->updateElement($dane['data']);
            }

            if ($dane['data_lang'] && $post['id_lang']) {
                if ($this->checkIfLangExist($post['id'], $post['id_lang'])) {
                    return $this->updateElementLang($dane['data_lang'], $post['id']);
                } else {
                    $this->insertElementLang($dane['data_lang'], $post['id']);
                }
            }
        } else {
            $dane['data'] = $this->addDataInsert($dane['data']);
            $id = $this->insertElement($dane['data']);
            if (!$id) {
                throw new Exception('There is no ID');
            }

            $this->_id = $id;
            $dane['data'] = $this->handleImg($dane, $id);
            if ($dane['data']) {
                $this->updateElement($dane['data']);
            }
            if ($dane['data_lang']) {
                $this->insertElementLang($dane['data_lang'], $id);
            }
            return true;
        }
    }

    protected function addDataInsert($dane)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $dane['controller'] = $this->_params['controller'];
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $dane['action'] = $this->_params['action'];
        }
        return $dane;
    }

    protected function handleImg($dane, $id)
    {
        foreach ($dane['data_img'] as $k => $v) {
            $mThumb = new Coret_Model_Thumbnail(array(), $id, $v);

            if (isset($this->_columns[$k]['destination']) && $this->_columns[$k]['destination']) {
                $mThumb->setDestinationDir($this->_columns[$k]['destination']);
            }

            $mThumb->createThumbnail($this->_columns[$k]['resize']['width'], $this->_columns[$k]['resize']['height'], $k);

            if (isset($this->_columns[$k]['big']['width']) && isset($this->_columns[$k]['big']['height'])) {
                $mThumb->createThumbnail($this->_columns[$k]['big']['width'], $this->_columns[$k]['big']['height'], 'big_' . $k);
            }

            $dane['data'][$k] = $mThumb->getType();

        }
        return $dane['data'];
    }

    /**
     * @param $dane
     * @return string
     */
    public function insertElement($dane)
    {
        return $this->insert($dane);
    }

    /**
     * @param $dane
     * @param $id
     * @return mixed|string
     * @throws Exception
     * @throws Zend_Exception
     */
    public function insertElementLang($dane, $id)
    {
        $dane[$this->_primary] = $id;
        if (!isset($dane['id_lang']) || !$dane['id_lang']) {
            $dane['id_lang'] = Zend_Registry::get('config')->id_lang;
        }
        $this->_sequence = true;
        $this->insert($dane, $this->_name . '_Lang');
    }

    /**
     * @param array $dane
     * @return int|void
     * @throws Exception
     */
    public function updateElement(array $dane)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_id)
        );
        $where = $this->addWhere($where);
        return $this->update($dane, $where);
    }

    /**
     * @param array $dane
     * @param $id
     * @return int|void
     * @throws Exception
     */
    public function updateElementLang(array $dane, $id)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $id)
        );

        $where = $this->addWhereLang($where, $dane);

        return $this->update($dane, $where, $this->_name . '_Lang');
    }

    /**
     * @param array $columns
     * @param array $columns_lang
     */
    protected function prepareSelect(array $columns = array(), array $columns_lang = array())
    {
        $this->_select = $this->_db->select();

        if ($columns) {
            $this->_select->from($this->_name, $columns);
        } else {
            $this->_select->from($this->_name);
        }

        $this->addSelectJoin($columns_lang);
        $this->addSelectWhereLang();
        $this->addSelectWhere();
        $this->addSelectGroup();
        $this->addSelectOrder();
    }

    /**
     * @return mixed
     */
    protected function getSelect()
    {
        return $this->_select;
    }

    public function getList4FormSelect($columnName)
    {
        if (is_array($columnName)) {
            $array = $columnName;
            $array[] = $this->_primary;
            $this->prepareSelect($array);
        } else {
            $this->prepareSelect(array($this->_primary, $columnName));
        }

        $selectOptions = array();

        foreach ($this->selectAll($this->_select) as $row) {
            if (is_array($columnName)) {
                $name = '';
                foreach ($columnName as $val) {
                    if ($name) {
                        $name .= ' ';
                    }
                    $name .= $row[$val];
                }
                $selectOptions[$row[$this->_primary]] = $name;
            } else {
                $selectOptions[$row[$this->_primary]] = $row[$columnName];
            }
        }

        return $selectOptions;
    }

    /**
     * @param array $columns
     * @param array $columns_lang
     * @return array
     */
    public function getList(array $columns = array(), array $columns_lang = array())
    {
        $this->prepareSelect($columns, $columns_lang);
        return $this->_db->fetchAll($this->_select);
    }

    /**
     * @param array $columns
     * @param array $columns_lang
     * @return array
     * @throws Zend_Exception
     */
    public function getListWithAuthor(array $columns = array(), array $columns_lang = array())
    {
        $result = array();

        $adminClassName = Zend_Registry::get('config')->adminClassName;
        if (!$adminClassName) {
            throw new Zend_Exception('Admin class name not enabled in application.ini');
        }

        $mAdmin = new $adminClassName();

        foreach ($this->getList($columns, $columns_lang) as $row) {
            if (isset($row[$this->_adminId])) {
                $row['author'] = $mAdmin->getAuthorById($row[$this->_adminId]);
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param array $columns
     * @param array $columns_lang
     * @return Zend_Paginator_Adapter_DbSelect
     */
    public function getPagination(array $columns = array(), array $columns_lang = array())
    {
        $this->prepareSelect($columns, $columns_lang);
        return new Zend_Paginator_Adapter_DbSelect($this->_select);
    }

    /**
     * @param null $id
     * @return array|mixed
     * @throws Zend_Exception
     */
    public function getElement($id = null)
    {
        if (!$id) {
            $id = $this->_id;
        }

        if (!$id) {
            throw new Zend_Exception('No element ID');
        }

        if ($id == -1) {
            $this->_select = $this->_db->select()
                ->from($this->_name)
                ->limit(1);
        } else {
            $this->_select = $this->_db->select()
                ->from($this->_name)
                ->where($this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ?', $id);
        }

        $this->addSelectWhereLang();
        $this->addSelectWhere();
        $this->addSelectJoin();
        $result = $this->_db->fetchRow($this->_select);
        if ($result) {
            if (isset($result['adminId'])) {
                $adminClassName = Zend_Registry::get('config')->adminClassName;
                if (!$adminClassName) {
                    throw new Zend_Exception('Admin class name not enabled in application.ini');
                }
                $mAdmin = new $adminClassName();
                $result['author'] = $mAdmin->getAuthorById($result['adminId']);
            }
            return $result;
        }

        return array();
    }

    /**
     * @param $select
     * @param array $columns_lang
     * @return mixed
     */
    protected function addSelectJoin()
    {
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addSelectWhere()
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $this->_select->where($this->_name . ' . controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $this->_select->where($this->_name . ' . action = ?', $this->_params['action']);
        }

        $search = Zend_Controller_Front::getInstance()->getRequest()->getParam('search');
        if ($search) {
            $whereString = '';
            foreach (array_keys($this->_columns) as $key) {
                if ($this->_columns[$key]['type'] == 'varchar' || $this->_columns[$key]['type'] == 'text') {
                    if ($whereString) {
                        $whereString .= ' OR ';
                    }
                    $whereString .= 'upper(' . $this->_db->quoteInto($this->_name . ' . ' . $this->_db->quoteIdentifier($key) . ') LIKE upper(?)', '%' . $search . '%');
                }
            }
            if ($whereString) {
                $this->_select->where($whereString);
            }
        }
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addSelectWhereLang()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            if (!isset($this->_params['id_lang']) || !$this->_params['id_lang']) {
                $this->_params['id_lang'] = Zend_Registry::get('config')->id_lang;
            }
            $columns_lang = array();
            foreach (array_keys($this->_columns_lang) as $column) {
                $columns_lang[] = $column;
            }
            $this->_select->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), $columns_lang);
            $this->_select->where($this->_db->quoteIdentifier($this->_name . '_Lang') . ' . id_lang = ?', $this->_params['id_lang']);
        }
    }

    /**
     * @param $where
     * @return array
     */
    protected function addWhere($where)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $where[] = $this->_db->quoteInto('controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $where[] = $this->_db->quoteInto('action = ?', $this->_params['action']);
        }
        return $where;
    }

    /**
     * @param $where
     * @return array
     */
    protected function addWhereLang($where, $data)
    {
        $where[] = $this->_db->quoteInto('id_lang = ?', $data['id_lang']);
        return $where;
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addSelectGroup()
    {
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addSelectOrder()
    {
        $sort = Zend_Controller_Front::getInstance()->getRequest()->getParam('sort');
        $order = Zend_Controller_Front::getInstance()->getRequest()->getParam('order');

        if ($sort) {
            if ($order) {
                $this->_select->order($sort . ' ' . $order);
            } else {
                $this->_select->order($sort);
            }
        } elseif (isset($this->_sort) && $this->_sort) {
            if (isset($this->_order) && $this->_order) {
                $this->_select->order($this->_sort . ' ' . $this->_order);
            } else {
                $this->_select->order($this->_sort);
            }
        } else {
            $this->_select->order($this->_primary);
        }
    }

    /**
     * @return int
     */
    public function deleteElement()
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_id)
        );

        $where = $this->addWhere($where);

        return $this->_db->delete($this->_name, $where);
    }

    /**
     * @param array $post
     * @return array
     */
    protected function prepareData(Array $post)
    {
        $data = array(
            'date' => new Zend_Db_Expr('now()'),
            $this->_adminId => Zend_Auth::getInstance()->getIdentity()->user_id
        );
        $data_lang = array();
        $data_img = array();

        reset($this->_columns);
        reset($this->_columns_lang);

        for ($i = 0; $i < count($this->_columns); $i++) {
            $column = key($this->_columns);

            if (isset($post[$column])) {
                if (isset($this->_columns[$column]['active']['db']) && !$this->_columns[$column]['active']['db']) {
                    next($this->_columns);
                    continue;
                }

                switch ($this->_columns[$column]['type']) {
                    case 'image':
                        if ($post[$column]) {
                            $data_img[$column] = $post[$column];
                        }
                        next($this->_columns);
                        continue;
                        break;
                    case 'number':
                        $validator = new Zend_Validate_Digits();
                        if ($validator->isValid($post[$column])) {
                            $data[$column] = $post[$column];
                        }
                        break;
                    default:
                        $data[$column] = $post[$column];
                }
            }

            next($this->_columns);
        }

        if (isset($this->_columns_lang) && $this->_columns_lang) {
            $data_lang['id_lang'] = $post['id_lang'];
            for ($i = 0; $i < count($this->_columns_lang); $i++) {
                $column_lang = key($this->_columns_lang);

                if (isset($post[$column_lang])) {
                    if (!$post[$column_lang]) {
                        next($this->_columns_lang);
                        continue;
                    }
                    $data_lang[$column_lang] = $post[$column_lang];
                }

                next($this->_columns_lang);
            }
        }

        return array('data' => $data, 'data_lang' => $data_lang, 'data_img' => $data_img);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function addDataForInsert($data)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $data['controller'] = $this->_params['controller'];
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $data['action'] = $this->_params['action'];
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * @return array
     */
    public function getColumnsAll()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            return array_merge($this->_columns, $this->_columns_lang);
        }
        return $this->_columns;
    }

    /**
     *
     * @return array
     */
    public function getColumnsLang()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            return $this->_columns_lang;
        }
    }

    /**
     *
     */
    protected function mixColumns()
    {
        foreach ($this->columns() as $name => $column) {
            $this->_columns[$name] = $column;
        }
    }

    /**
     * @return array
     */
    protected function columns()
    {
        return array();
    }

    /**
     * @return bool
     */
    public function isLang()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            return true;
        }
    }

    /**
     * @return mixed|null
     */
    public function getPrimary()
    {
        return $this->_primary;
    }

    /**
     * @param $id
     * @param $id_lang
     * @return mixed
     */
    protected function checkIfLangExist($id, $id_lang)
    {
        $select = $this->_db->select()
            ->from($this->_name . '_Lang', $this->_primary)
            ->where('id_lang = ?', $id_lang)
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $id);

        return $this->_db->fetchOne($select);
    }

    public function updateActive($active, $id)
    {
        $data = array(
            'active' => $active
        );
        $where = $this->_db->quoteInto($this->_primary . ' = ?', $id);
        return $this->update($data, $where);
    }

}
