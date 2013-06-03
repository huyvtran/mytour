<?php

/*
  ZoneData help to make a check form submit quickly
  Result is a instance of  ZoneZDataResult:
 */

class ZDataResult {
    /*
      data of object get from acceptable submit
     */

    public $data = array();
    public $oldData = null;
    public $error = array();

    public function isError() {
        return count($this->error) > 0;

    }

    public function isValid() {
        return !$this->isError();

    }

    public function setData( $data, $value ) {
        if ( is_array($data) ) {
            foreach ( $data as $k => $v )
                $this->data[$k] = $v;
            return $this;
        }
        $this->data[$data] = $value;
        return $this;

    }

    public function setOldData( $data ) {
        $this->oldData = $data;
        return $this;

    }

    public function setError( $field, $message ) {
        $this->error[$field] = $message;
        return $this;

    }

    public function getData() {
        return $this->data;

    }

    public function getOldData() {
        return $this->oldData;

    }

    public function getError() {
        $error = $this->error;
        return func_num_args() == 0 ? $error : $error[func_get_arg(0)];

    }

}

class ZData {
    /* store all normal fields */

    /* protected */

    public $fields = array();

    /* old data for edit action */
    public $oldData = null;

    /* cache file uploaded */
    protected $caches = array();

    /* namespace of $_POST & $_FILE */
    protected $indexRequest = null;

    /* index of item for mul-fields */
    protected $indexItem = null;

    /*
      a class has valid method
      @func
      @args: $newData, $oldData
     */
    public $validator = null;

    /*
      Register a field to data
     */

    public function addField( $field, $config = array() ) {
        return $this->addFields($field, $config);

    }

    public function addFields( $field, $config = array() ) {
        if ( is_array($field) ) {
            foreach ( $field as $a => $v ) {
                $this->addFields($a, $v);
            }
            return $this;
        }

        if ( is_array($config) ) {
            if ( !isset($config['name']) ) {
                $config['name'] = $field;
            }

            if ( isset($config['request_label']) ) {
                $config['name'] = $config['request_label'];
            }

            $config['type'] = strtoupper($config['type']);

            if ( $config['type'] == 'DATE' ) {
                $config['fix_value'] = change_date_format;
            }

            if ( $config['type'] == 'DATETIME' ) {
                $config['fix_value'] = change_datetime_format;
            }

            if ( !array_key_exists('default_value', $config) ) {
                if ( in_array($config['type'], array('LIST', 'DATE', 'PHONE', 'DATETIME', 'NUMBER', 'INT', 'FLOAT', 'ENUM', 'FILE', 'IMAGE', 'BINARY')) ) {
                    $config['default_value'] = null;
                } else {
                    $config['default_value'] = '';
                }
            }
        } else {
            $value = $config;
            $config = array(
                type => 'NONE',
                default_value => $value
            );
        }

        $this->fields[$field] = $config;

    }

    /*
      Change a field settings
     */

    public function changeField( $field, $key, $value ) {
        if ( isset($this->fields[$field]) ) {
            $this->fields[$field][$key] = $value;
        }

    }

    /*
      Remove a field settings
     */

    public function removeField( $field ) {
        if ( isset($this->fields[$field]) ) {
            unset($this->fields[$field]);
        }

    }

    /*
      If a form which contain field upload
      return a error then we need free cache
     */

    public function removeCache() {
        $files = $this->caches;
        while (count($files) > 0) {
            @unlink(array_shift($files));
        }

    }

    public function freeFile() {
        $this->removeCache();

    }

    public function setIndexRequest( $idx ) {
        $this->indexRequest = $idx;

    }

    public function setIndexItem( $idx ) {
        $this->indexItem = $idx;

    }

    public function setOldData( $data ) {
        $this->oldData = $data;

    }

    public function setValidator( $validator ) {
        $this->validator = $validator;

    }

    /*
      The short way to getResult with return the first error
     */

    public function getData() {

        $result = $this->getResult();
        if ( $result->isError() ) {
            $error = $result->getError();
            if ( count($error) == 1 ) {
                foreach ( $error as $f => $m ) {
                    return $m;
                }
            }

            $s = '';
            foreach ( $error as $f => $m ) {
                $s .= "<div>+) $m</div>";
            }
            return $s;
        }

        return $result->getData();

    }

    /*
      Validator data submit and return status
     */

    public function getResult() {
        $result = new ZDataResult();

        if ( empty($this->indexRequest) ) {
            $REQUEST = $_REQUEST;
            $FILES = $_FILES;
        } else {
            $REQUEST = (array) $_REQUEST[$this->indexRequest];
            $FILES = (array) $_FILES[$this->indexRequest];
        }

        foreach ( $this->fields as $field => $config ) {

            $k = $config['name'];
            $upper_label = utf8_ucfirst($config['label']);
            $lower_label = utf8_lcfirst($config['label']);

            $type = strtoupper($config['type']);

            if ( in_array($type, array('FILE', 'IMAGE', 'BINARY')) ) {
                continue;
            }

            //new version for NONE field
            if ( !is_array($config) ) {
                $result->setData($field, $config);
                continue;
            }

            //old version
            if ( $config['type'] == 'NONE' ) {
                $result->setData($field, $config['default_value']);
                continue;
            }


            /*
              ENUM & LIST is a special field
             */
            if ( $type == 'ENUM' ) {
                if ( is_null($this->indexItem) ) {
                    if ( !array_key_exists($k, $REQUEST) && is_null($config['default_value']) && $config['no_empty'] ) {
                        $result->setError($k, isset($config['error_empty_message']) ? $config['error_empty_message'] : "$upper_label bắt buộc phải nhập");
                        continue;
                    }
                    $v = $REQUEST[$k];
                } else {
                    if ( !array_key_exists($this->indexItem, $REQUEST[$k]) && ais_null($config['default_value']) && $config['no_empty'] ) {
                        $result->setError($k, isset($config['error_empty_message']) ? $config['error_empty_message'] : "$upper_label bắt buộc phải nhập");
                        continue;
                    }
                    $v = $REQUEST[$k][$this->indexItem];
                }

                $enum_value = (array) $config['value'];


                if ( !in_array($v, $enum_value) ) {
                    if ( array_key_exists('default_value', $config) ) {
                        $result->setData($field, $config['default_value']);
                    } else {
                        $result->setError($k, isset($config['error_datatype_message']) ? $config['error_datatype_message'] : "$upper_label có giá trị không hợp lệ");
                        continue;
                    }
                } else {
                    $result->setData($field, $v);
                }
                continue;
            }

            if ( $type == 'LIST' ) {

                if ( is_null($this->indexItem) ) {
                    $v = $REQUEST[$k];
                } else {
                    $v = $REQUEST[$k][$this->indexItem];
                }

                $list_value = (array) $config['value'];
                $r = array();

                foreach ( (array) $v as $i ) {
                    if ( in_array($i, $list_value) ) {
                        $r[] = $i;
                    }
                }

                if ( count($r) > 0 ) {
                    $result->setData($field, implode((isset($config['separator']) ? $config['separator'] : ','), $r));
                } else {
                    if ( array_key_exists('default_value', $config) ) {
                        $result->setData($field, $config['default_value']);
                    }
                }
                continue;
            }

            if ( $this->indexItem === null ) {
                if ( $REQUEST[$k] === null || $REQUEST[$k] === '' ) {
                    if ( $config['no_empty'] ) {
                        $result->setError($k, isset($config['error_empty_message']) ? $config['error_empty_message'] : "$upper_label bắt buộc phải nhập"
                        );
                        continue;
                    } else {
                        if ( array_key_exists('default_value', $config) ) {
                            $result->setData($field, $config['default_value']);
                        } else {
                            $result->setData($field, null);
                        }
                        continue;
                    }
                }
            } else {

                if ( $REQUEST[$k][$this->indexItem] === null || $REQUEST[$k][$this->indexItem] === '' ) {
                    if ( $config['no_empty'] ) {
                        $result->setError($k, isset($config['error_empty_message']) ? $config['error_empty_message'] : "$upper_label bắt buộc phải nhập"
                        );
                        continue;
                    } else {
                        if ( array_key_exists('default_value', $config) ) {
                            $result->setData($field, $config['default_value']);
                        } else {
                            $v = $REQUEST[$k][$this->indexItem];
                            $result->setData($field, $REQUEST[$k][$this->indexItem] ? $v : null);
                        }
                        continue;
                    }
                } else {
                    //if ( !$is_file && !$is_files ) {
                    // }
                }
            }

            if ( is_null($this->indexItem) ) {
                $value = $REQUEST[$k];
            } else {
                $value = $REQUEST[$k][$this->indexItem];
            }

            if ( isset($config['fix_value']) ) {
                $value = $config['fix_value']($value);
            }

            if ( !is_array($value) ) {
                $value = stripslashes($value);
            } else {
                foreach ( $value as $j => $v ) {
                    $value[$j] = stripslashes($v);
                }
            }

            if ( !in_array($type, array('DATE', 'DATETIME')) ) {
                /* Some config for all field type */
                if ( isset($config['min_length']) ) {
                    if ( mb_strlen($value, 'utf8') < (int) $config['min_length'] ) {
                        $result->setError($k, utf8_ucfirst($config['label']) . " quá ngắn ( tối thiểu {$config['min_length']} kí tự )"
                        );
                    }
                }

                if ( isset($config['max_length']) ) {
                    if ( mb_strlen($value, 'utf8') > (int) $config['max_length'] ) {
                        $result->setError($k, utf8_ucfirst($config['label']) . " quá dài  ( tối đa {$config['max_length']} kí tự )."
                        );
                        continue;
                    }
                }

                //check preg_match
                if ( isset($config['mathes']) ) {
                    foreach ( $config['mathes'] as $match => $message ) {
                        if ( !preg_match($match, $value) ) {
                            $result->setError($k, preg_replace(array("{Label}", "{label}"), array(utf8_ucfirst($config['label']), utf8_lcfirst($config['label'])), $message));
                            continue;
                        }
                    }
                }
            }


            switch ($type) {

                /* Simple type a non-html string or html string */
                case 'TEXT':
                case 'CHAR':
                case 'HTML':
                    $result->setData($field, $this->filter($type != 'HTML' ? escape_html($value) : $value, $config));
                    break;

                case 'EMAIL':
                    if ( isEmail($value) ) {
                        $result->setData($field, $this->filter($value, $config));
                    } else {
                        $result->setError($k, isset($config['error_format_message']) ? $config['error_format_message'] : "$upper_label không hợp lệ"
                        );
                    }
                    break;

                case 'DATE':
                    if ( $value === '' || date('Y-m-d', strtotime($value)) == $value ) {

                        $time = strtotime($value);

                        if ( isset($config['min']) ) {
                            if ( $time < strtotime($config['min']) ) {
                                $result->setError($k, $config['error_min_message'] ? $config['error_min_message'] : "$upper_label không hợp lệ");
                                break;
                            }
                        }

                        if ( isset($config['max']) ) {
                            if ( $time > strtotime($config['max']) ) {
                                $result->setError($k, $config['error_max_message'] ? $config['error_min_message'] : "$upper_label không hợp lệ");
                                break;
                            }
                        }

                        $result->setData($field, $this->filter($value, $config));
                    } else {

                        $result->setError($k, $config['error_format_message'] ? $config['error_format_message'] : "$upper_label không hợp lệ"
                        );
                    }
                    break;

                case 'DATETIME':
                    if ( $value === '' || date('Y-m-d H:i:s', strtotime($value)) == $value ) {
                        $result->setData($field, $this->filter($value, $config));
                    } else {
                        $result->setError($k, $config['error_max_message'] ? $config['error_max_message'] : "$upper_label không hợp lệ");
                    }
                    break;

                case 'TIME':
                    if ( preg_match("#^[0-2][0-9]:[0-5][0-9]$#is", $value) ) {
                        $result->setData($field, $this->filter($value . ':00', $config));
                    } else {
                        $result->setError($k, $config['error_max_message'] ? $config['error_max_message'] : "$upper_label không hợp lệ" );
                    }
                    break;

                case 'DURATION':
                    if ( preg_match("#^(\d+(?:m|h|d|w|mo))(\s*\d+(?:m|h|d|w|mo)+?)*$#is", $value) ) {
                        $result->setData($field, $value);
                    } else {
                        $result->setError($k, $config['error_format_message'] ? $config['error_format_message'] : "$upper_label không đúng định dạng" );
                    }
                    break;

                case 'INT':
                    if ( preg_match("#[-+]?([0-9]+)#", $value, $matches) ) {
                        //check value in range set
                        $num = intval($value);
                        if ( isset($config['max']) ) {
                            if ( $num > $config['max'] ) {
                                $result->setError($k, $config['error_max_message'] ? $config['error_max_message'] : "$upper_label không thể lớn hơn {$config['max']}"
                                );
                                break;
                            }
                        }

                        if ( isset($config['min']) ) {
                            if ( $num < $config['min'] ) {
                                $result->setError($k, $config['error_min_message'] ? $config['error_min_message'] : "$upper_label không thể nhỏ hơn {$config['min']}"
                                );
                                break;
                            }
                        }

                        $result->setData($field, $this->filter($value, $config));
                    } else {
                        $result->setError($k, $config['error_format_message'] ? $config['error_format_message'] : "$upper_label phải là một số nguyên"
                        );
                    }
                    break;

                case 'FLOAT':
                    if ( preg_match("#[-+]?([0-9]*\.[0-9]+|[0-9]+)#", $value, $matches) ) {
                        //check value in range set
                        $num = intval($value);
                        if ( isset($config['max']) ) {
                            if ( $num > $config['max'] ) {
                                $result->setError($k, $config['error_max_message'] ? $config['error_max_message'] : "$upper_label  không được lớn hơn {$config['max']}"
                                );
                                break;
                            }
                        }

                        if ( isset($config['min']) ) {
                            if ( $num < (int) $config['min'] ) {
                                $result->setError($k, $config['error_min_message'] ? $config['error_min_message'] : "$upper_label không thể nhỏ hơn."
                                );
                                break;
                            }
                        }
                        $result->setData($field, $this->filter($value, $config));
                    } else {
                        $result->setError($k, $config['error_format_message'] ? $config['error_format_message'] : "$upper_label phải là một số thực"
                        );
                    }
                    break;

                case 'PHONE':
                    //'\(?[2-9][0-8][0-9]\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}'
                    if ( preg_match("/^(\+?\d+)?(\d+)$/i", $value) ) {
                        $result->setData($field, $this->filter($value, $config));
                    } else {
                        $result->setError($k, $config['error_phone_message'] ? $config['error_phone_message'] : "$upper_label không hợp lệ"
                        );
                    }
                    break;

                case 'NUMBER':
                    if ( preg_match("/^(\d+)$/i", $value) ) {
                        $result->setData($field, $this->filter($value, $config));
                    } else {
                        $result->setError($k, $config['error_number_message'] ? $config['error_number_message'] : "$upper_label chỉ gồm số"
                        );
                    }
                    break;

                case 'PASSWORD':
                    if ( is_null($this->indexItem) ) {
                        $value = $REQUEST[$k];
                        $re_pass = $REQUEST['re_' . $k];
                        $old_pass = $REQUEST['old_' . $k];
                    } else {
                        $value = $REQUEST[$k][$this->indexItem];
                        $re_pass = $REQUEST['re_' . $k][$this->indexItem];
                        $old_pass = $REQUEST['old_' . $k][$this->indexItem];
                    }

                    if ( isset($config['old_value']) && empty($old_pass) ) {
                        $result->setError($k, isset($config['error_empty_oldpass']) ? $config['error_empty_oldpass'] : "$upper_label cũ chưa có"
                        );
                        break;
                    }

                    if ( empty($re_pass) ) {
                        $result->setError($k, isset($config['error_empty_repass']) ? $config['error_empty_repass'] : "Xác nhận $lower_label chưa có"
                        );
                        break;
                    }

                    if ( isset($config['old_value']) && md5($old_pass) != $config['old_value'] ) {

                        $result->setError($k, $config['error_current_pass'] ? $config['error_current_pass'] : "$upper_label cũ không đúng"
                        );
                        break;
                    }

                    if ( $value != $re_pass ) {

                        $result->setError($k, $config['error_match_message'] ? $config['error_match_message'] : "$upper_label không đồng bộ"
                        );
                        break;
                    }

                    $result->setData($field, $this->filter(md5($value), $config));

                    break;

                //a field as code of record, always is required and unique
                case 'CODE':
                    if ( array_key_exists('sql_check', $config) ) {
                        $sql = str_ireplace('@value', $value, $config['sql_check']);
                        if ( Zone_Base::$Model->fetchRow($sql) ) {
                            $result->setError($k, $config['error_code_message'] ? $config['error_code_message'] : "$upper_label đã tồn tại");
                            break;
                        }
                    }
                    $result->setData($field, $this->filter($value, $config));
                    break;
                default:
            }
        }

        $result = $this->validFile($result);

        /**
         * Compare field value
         */
        foreach ( $this->fields as $field => $config ) {
            if ( isset($config['compare_with_field']) ) {
                $value = $result->data[$field];
                $upper_label = utf8_ucfirst($config['label']);
                $lower_label = utf8_lcfirst($config['label']);

                //compare width other field
                $t = $config['compare_with_field'];
                $operator = $t[0];
                $comp_field = $t[1];
                $comp_field_value = $result->data[$t[1]];

                $comp_field_config = null;
                foreach ( $this->fields as $f => $conf ) {
                    if ( $comp_field != $f )
                        continue;
                    $comp_field_config = $conf;
                    break;
                }

                $comp_field_lower_label = utf8_lcfirst($comp_field_config['label']);
                $rcomp = false;
                if ( $conf['type'] == $config['type'] ) {
                    if ( in_array($conf['type'], array('DATE', 'DATETIME', 'TIME')) ) {
                        $rcomp = $this->validCompare($operator, strtotime($value), strtotime($comp_field_value));
                    } else if ( in_array($conf['type'], array('INT', 'FLOAT')) ) {
                        $rcomp = $this->validCompare($operator, $value, $comp_field_value);
                    }
                }

                $trans = array(
                    '<' => 'nhỏ hơn',
                    '>' => 'lớn hơn',
                    '<=' => 'nhỏ hơn hoặc bằng',
                    '>=' => 'lớn hơn hoặc bằng'
                );

                $trans = $trans[$operator];

                if ( is_null($comp_field_config) || !$rcomp ) {
                    $result->setError($k, $config['error_compare_message'] ? $config['error_compare_message'] : "$upper_label phải $trans $comp_field_lower_label");
                }
            }
        }

        if ( !$this->validator ) {
            return $result;
        }

        /*
          note: data get form DB or file store
          its structure may be diffrent with data get from user post
         */
        if ( is_array($this->oldData) ) {
            $result->setOldData($this->oldData);
        }

        $result = $this->validator->validate($result);

        if ( !($result instanceof ZDataResult) ) {
            throw new Exception("The validator must return a instanceof ZdataResult");
        }

        /*
          auto clean if devloper forget
         */
        if ( count($result->error) > 0 ) {
            $this->removeCache();
        }
        return $result;

    }

    protected function validFile( $result ) {

        if ( empty($this->indexRequest) ) {
            $FILES = $_FILES;
        } else {
            $FILES = (array) $_FILES[$this->indexRequest];
        }

        foreach ( $this->fields as $field => $config ) {
            $k = $config['name'];
            $type = strtoupper($config['type']);

            if ( !in_array($type, array('FILE', 'IMAGE', 'BINARY')) )
                continue;

            switch ($type) {

                case 'IMAGE':
                    $config['file_type'] = array('gif', 'png', 'jpg', 'jpeg');

                case 'FILE':
                    if ( $this->indexItem === null ) {
                        $check = $this->checkFile($FILES[$k], $config);
                    } else {
                        $check = $this->checkFile($FILES[$k][$this->indexItem], $config);
                    }

                    if ( !is_array($check) ) {
                        $this->removeCache();
                        return $result->setError($k, $check);
                    } else {
                        $this->caches[] = str_replace('//', '/', $config['path'] . '/' . $check[0]);
                        $result->setData($field, $config['return_name'] ? $check[0] : $check);
                    }
                    break;

                case 'BINARY':
                    $check = $this->checkBinary($FILES[$k], $config);
                    if ( !is_array($check) ) {
                        $this->removeCache();
                        return $result->setError($k, $check);
                    } else {
                        $result->setData($field, $config['return_name'] ? $check[0] : $check);
                    }
                    break;
                default:
            }
        }

        return $result;

    }

    //we try to upload whether is successful return error or $file_name
    public function checkFile( $tmp, $config = array() ) {
        $config['file'] = $tmp;
        if ( !isset($config['path']) )
            $config['path'] = "files/";
        return upload($config);

    }

    public function checkBinary( $tmp, $config = array() ) {
        if ( $config['file_type'] ) {
            if ( !is_array($config['file_type']) )
                $config['file_type'] = explode(',', $config['file_type']);
            $exts = array();
            $mime = include get_include_path() . '/Data/Mime.php';
            $mime = (array) $mime;
            foreach ( $config['file_type'] as $k ) {
                if ( isset($mime[$k]) ) {
                    $exts = array_merge($exts, $mime[$k]);
                }
            }
            if ( !in_array($tmp['type'], $exts) ) {
                return "File " . utf8_lcfirst($config['label']) . " chỉ chấp nhận định dạng " . implode(',.', $config['file_type']);
            }
        }
        $max_size = (int) $config['max_size'];
        if ( $max_size > 0 && $tmp['size'] > $max_size ) {
            return "Dung lượng file " . utf8_lcfirst($config['label']) . " quá lớn";
        }

        $f = fopen($tmp['tmp_name'], "rb");
        $data = fread($f, $tmp['size']);
        fclose($f);

        return array(array($data, $tmp['type'], $tmp['size'], $tmp['name']));

    }

    public function filter( $value, $config ) {
        if ( !isset($config['filter']) || !function_exists($config['filter']) )
            return $value;
        $f = $config['filter'];
        return $f($value);

    }

    public function validCompare( $o, $v1, $v2 ) {
        switch ($o) {
            case '<':
                return $v1 < $v2;
            case '>':
                return $v1 > $v2;
            case '>=':
                return $v1 >= $v2;
            case '<=':
                return $v1 <= $v2;
            case '=':
                return $v1 == $v2;
        }
        return false;

    }

}

?>
