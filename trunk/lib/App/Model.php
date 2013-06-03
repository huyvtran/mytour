<?php

set_include_path(ROOT . '/lib');
require_once 'Zend/Db.php';
require_once 'Zend/Db/Table.php';

class Model_Expr extends Zend_Db_Expr {

    function __constructor() {
        parent::__constructor();

    }

}

class App_Model extends Zend_Db_Table {

    function __constructor() {
        parent::__constructor();

    }

    public function query( $statement ) {
        return $this->_db->query($statement);

    }

    public function exec( $statement ) {
        return $this->query($statement);

    }

    public function lastId() {
        $params = (array) func_get_args();
        return call_user_method_array("lastInsertId", $this->_db, $params);

    }

    public function fetchAll() {
        if ( func_num_args() > 1 ) {

            $sql = func_get_arg(1);
            $cache_id = 'cache/DB_' . func_get_arg(0);

            if ( file_exists($cache_id) ) {
                return unserialize(file_get_contents($cache_id));
            } else {
                $result = $this->_db->fetchAll($sql);
                try {
                    $f = fopen($cache_id, 'w');
                    fputs($f, serialize($result));
                    fclose($f);
                } catch (Exception $ex) {

                }
                return $result;
            }
        } else {
            return $this->_db->fetchAll(func_get_arg(0));
        }

    }

    public function fetchRow() {
        if ( func_num_args() > 1 ) {

            $sql = func_get_arg(1);
            $cache_id = 'cache/DB_' . func_get_arg(0);

            if ( file_exists($cache_id) ) {
                return unserialize(file_get_contents($cache_id));
            } else {
                $result = $this->_db->fetchRow($sql);
                try {
                    $f = fopen($cache_id, 'w');
                    fputs($f, serialize($result));
                    fclose($f);
                } catch (Exception $ex) {

                }
                return $result;
            }
        } else {
            return $this->_db->fetchRow(func_get_arg(0));
        }

    }

    public function fetchOne() {
        $params = func_get_args();

        $result = call_user_func_array(array(&$this, 'fetchRow'), $params);

        if ( !is_array($result) ) {
            return NULL;
        } else {
            foreach ( $result as $v )
                return $v;
            return NULL;
        }

    }

    public function getTotal( $tb, $where = '' ) {
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        $total = $this->_db->fetchRow("SELECT COUNT(*) as `total` FROM $tb $where");
        return $total['total'];

    }

    public function insert( $tb, $data ) {
        $this->removeCache($tb);
        return $this->_db->insert($tb, $data);

    }

    public function insertMany( $tb, $rows, $ignore = true ) {
        if ( count($rows) == 0 )
            return false;

        $fields = array_keys($rows[0]);

        if ( count($fields) == 0 )
            return false;

        $field_str = array();
        $row_str = array();
        foreach ( $fields as $f ) {
            $field_str[] = "`$f`";
            $row_str[] = '?';
        }
        $field_str = implode(',', $field_str);
        $row_str = implode(',', $row_str);

        $ignore = $ignore ? 'IGNORE' : '';

        $stmt = $this->getAdapter()
                ->prepare('INSERT ' . $ignore . ' INTO ' . $tb . ' (' . $field_str . ')
                        VALUES (' . $row_str . ')' . str_repeat(',(' . $row_str . ')', count($rows) - 1));

        $params = array();
        foreach ( $rows as $row ) {
            foreach ( $fields as $f ) {
                $params[] = $row[$f];
            }
        }

        return $stmt->execute($params);

    }

    public function update( $tb, $data, $where = '' ) {
        $this->removeCache($tb);
        return $this->_db->update($tb, $data, $where);

    }

    public function delete( $tb, $where ) {
        return $this->_db->delete($tb, $where);

    }

    public function removeCache( $namespace ) {
        //if $namespace match name*
        // then remove all file match name*
        $path = 'cache/';
        if ( ($m = preg_match('#([^*]+)\*#is', $namespace)) != null ) {
            foreach ( scandir($path) as $file ) {
                if ( is_file($path . $file) ) {
                    if ( substr($file, 0, strlen($m[1])) == $m[1] ) {
                        @unlink($path . 'DB_' . $file);
                    }
                }
            }
        } else {
            @unlink($path . 'DB_' . $namespace);
        }

    }

    public function fetchID( $table, $id ) {
        if ( is_array($id) ) {
            $ids = mysql_join_group($id);
            return $this->fetchAll("SELECT * FROM `$table` WHERE `ID` IN $ids");
        }
        return $this->fetchRow("SELECT * FROM `$table` WHERE `ID`='$id'");

    }

    public function fetchOneByID( $field, $table, $id ) {
        return $this->fetchOne("SELECT $field FROM $table WHERE ID='$id'");

    }

}

?>
