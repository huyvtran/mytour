<?php

function create_dir( $dir, $chmod = '0777' ) {
    if ( mkdir($dir) ) {
        @chmod($dir, $chmod);
        return true;
    } else {
        return false;
    }

}

function create_file( $root, $name, $data = NULL, $chmod = '0777' ) {
    $path = $root . DIRECTORY_SEPARATOR . $name;
    $f = fopen($path, 'w+');
    if ( !$f )
        return false;
    if ( $data )
        fputs($f, $data);
    @chmod($path, $chmod);
    fclose($f);
    return true;

}

/*
  This is a function that can get max size of file upload
  Base on server and application
 */

function get_max_size_upload() {
    $max_upload = (int) (ini_get('upload_max_filesize'));
    $max_post = (int) (ini_get('post_max_size'));
    $memory_limit = (int) (ini_get('memory_limit'));
    return min($max_upload, $max_post, $memory_limit);

}

function get_file_type( $filename, $name = null ) {
    if ( is_null($name) )
        $name = $filename;
    if ( function_exists('finfo_open') ) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        $a = explode(';', $mimetype);
        return trim($a[0]);
    } else
    if ( function_exists('mime_content_type') ) {
        $bool = mime_content_type($filename);
        if ( $bool ) {
            return $bool;
        }
    }
    //no way to check mime type and must check from name
    $mime = include get_include_path() . '/Data/Mime.php';
    $mime = (array) $mime;
    $matches = array();
    if ( preg_match("/\.([a-z0-9]{2,4})$/i", $name, $matches) ) {
        $ext = strtolower($matches[1]);
        if ( isset($mime[$ext]) ) {
            return $mime[$ext][0];
        }
    }
    return 'application/octet-stream';

}

/*
  Upload a image-file
 * 	0: Invalid filetype
 * 	1: Exceed size
 *  -1: Unable to upload file
 *  -2 : Path is not config
 * 	2: Not a file
 * 	  $config is a array contains properties:
  {
  path =>
  file => in $_FILES,
  old_file  =>
  is_unique => true|false,
  max_size  => value in KB,

  }
 */

function uploadImage( $config = array() ) {
    $config['file_type'] = array('gif', 'jpg', 'png');
    return upload($config);

}

function upload( $config = array() ) {
    if ( !isset($config['path']) || !is_dir($config['path']) )
        return 'Thư mục chứa file không tồn tại';
    //relative or absolute path to save file
    $path = $config['path'];

    if ( !isset($config['path']) ) {
        return 'Chưa thiết lập đường dẫn thư mục lưu file';
    }

    //file in $_FILES
    $file = $config['file'];

    if ( !$file || !isset($file['tmp_name']) )
        return 'File upload không hợp lệ';

    //die(var_dump($file));
    //$config['cmod'] ='777';
    $file_types = (array) $config['file_type'];
    $accepts = array('zip', 'rar', 'txt', 'html', 'jpg', 'gif', 'png', 'bmp',
        'doc', 'docx', 'xls', 'xlsx', 'css', 'ptt', 'pttx', 'ptts', 'pdf', 'xml', 'ppt', 'pptx');

    if ( count($file_types) > 0 ) {
        foreach ( $file_types as $j ) {
            if ( !isset($accepts[$j]) ) {
                unset($file_types[$j]);
            }
        }
    } else {
        $file_types = $accepts;
    }

    if ( is_file($file['tmp_name']) ) {
        $filetype = $file['type'];
        $filesize = $file['size'] / (1000 * 1024); //MB
        $filename = create_alias($file['name']);

        //Check max size
        if ( !isset($config['max_size']) OR !is_numeric($config['max_size']) )
            $config['max_size'] = 0;

        if ( $config['max_size'] > 0 ) {
            if ( $filesize > $config['max_size'] ) {
                return 'File upload có dung lượng quá lớn';
            }
        }

        if ( $filesize > get_max_size_upload() ) {
            return 'File upload có dung lượng quá lớn';
        }

        if ( defined('APP_MAX_SIZE_UPLOAD') ) {
            if ( $filesize > (int) APP_MAX_SIZE_UPLOAD ) {
                return 'File upload có dung lượng quá lớn';
            }
        }

        if ( defined('APP_FREE_SIZE_UPLOAD') ) {
            if ( $filesize > (int) APP_FREE_SIZE_UPLOAD ) {
                return 'Bộ nhớ lưu trữ của bạn không đủ';
            }
        }

        //octet-stream is ambigous
        if ( $filetype == 'application/octet-stream' ) {
            $filetype = get_file_type($file['tmp_name'], $file['name']);
        }

        //check file type
        if ( count($file_types) > 0 ) {
            $exts = array();
            $mime = include get_include_path() . '/Data/Mime.php';
            $mime = (array) $mime;

            foreach ( $file_types as $k ) {
                if ( isset($mime[$k]) ) {
                    $exts = array_merge($exts, $mime[$k]);
                }
            }
            if ( !in_array($filetype, $exts) ) {
                return "Chỉ chấp nhận file có định dạng ." . implode(',.', $file_types);
                ;
            }
        }

        //combine from data config
        if ( isset($config["default_value"]) ) {
            $config["old_file"] = $config["default_value"];
        }
        //whether or not old_file
        if ( !isset($config["old_file"]) || ($config["old_file"] === "") ) {
            if ( !isset($config["is_unique_name_upload"]) OR $config["is_unique_name_upload"] === true ) {
                if ( file_exists("$path/$filename") ) {
                    $info = pathinfo($filename);
                    $ext = $info['extension'];
                    $fname = $info['filename'];
                    $i = 1;
                    while (file_exists($path . "/" . $fname . "($i)." . $ext))
                        $i++;
                    $name = $fname . "($i)." . $ext;
                } else {
                    $name = $filename;
                }
            } else {
                $name = $filename;
            }
        } else {
            if ( $config["delete_old_file"] === true ) {
                @unlink($path . '/' . $config['old_file']);
            }
            $name = $config["old_file"];
        }

        $uploaded_file_full_path = "$path/$name";

        //$plugins = new Plugins();

        if ( @move_uploaded_file($file['tmp_name'], $uploaded_file_full_path) ) {
            if ( isset($config['resize']) && @filesize($uploaded_file_full_path) ) {
                loadClass('PHPThumb');
                $thumb = PhpThumbFactory::create($uploaded_file_full_path);
                $thumb->resize($config['resize'][0], $config['resize'][1]);
                $thumb->save($uploaded_file_full_path, 'png');

                //try{
                //$plugins->updateMememory( filesize($uploaded_file_full_path) );
                //}catch(Exception $e){
                //	throw new App_Exception($e->getMessage());
                //}

                return array($name, 'image/png', filesize($uploaded_file_full_path), $filename);
            }

            //try{
            //$plugins->updateMememory( filesize($uploaded_file_full_path) );
            //}catch(Exception $e){
            //	throw new App_Exception($e->getMessage());
            //}

            return array($name, $filetype, $filesize, $filename);
        } else {
            return 'Không thể upload file lên';
        }
    } else {
        return 'File upload không hợp lệ';
    }

}

function smart_file_size( $size ) {
    //format size
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;
    $size = $size * 1024 * 1024;
    for ( $i = 0; $i < count($units); $i++ ) {
        if ( $size > 1024 ) {
            $size /= 1024;
        } else {
            $unit = $i;
            break;
        }
    }

    return round($size, 2) . " <small>{$units[$unit]}</small>";

}

function getCache( $prefix, $file ) {
    $path = implode(DIRECTORY_SEPARATOR, array(ROOT, 'cache', $prefix, $file));
    if ( file_exists($path) ) {
        return file_get_contents($path);
    }
    return null;

}

function removeCache( $prefix, $file = null ) {
    if ( $file ) {
        $path = implode(DIRECTORY_SEPARATOR, array(ROOT, 'cache', $prefix, $file));
        if ( file_exists($path) ) {
            return @unlink($path);
        }
        return false;
    } else {
        $dir = implode(DIRECTORY_SEPARATOR, array(ROOT, 'cache', $prefix));
        if ( is_dir($dir) ) {
            $dir_handle = opendir($dir);
            if ( !$dir_handle )
                return false;
            while ($file = readdir($dir_handle)) {
                if ( $file != '.' && $file != '..' ) {
                    @unlink($dir . '/' . $file);
                }
            }
            closedir($dir_handle);
            @rmdir($dir);
        }
    }

}

/**
 * Save data to file for cache
 *
 * @author Nguyen Duc Minh
 * @date Jun 07, 2012
 *
 * @param String $prefix
 * @param String $file
 * @param String $data
 * @return String
 */
function setCache( $prefix, $file, $data ) {
    $path = implode(DIRECTORY_SEPARATOR, array(ROOT, 'cache', $prefix));
    @mkdir($path);

    $file = implode(DIRECTORY_SEPARATOR, array($path, $file));
    $fp = fopen($file, 'w');
    fwrite($fp, $data);
    fclose($fp);
    return $data;

}

/**
 * Show a file
 *
 * @param type $file_path
 * @param type $file
 */
function show_file( $file, $folder, $width = 60, $height = 60, $type = 0 ) {
    $file_path = "files/$folder/{$file['filename']}";
    $img_types = array('image/gif', 'image/png', 'image/x-png', 'image/jpeg', 'image/x-jpeg', 'image/jpg');
    if ( get('type') == 'icon' ) {
        if ( in_array($file['type'], $img_types) ) {
            loadClass('PHPThumb');
            $thumb = PhpThumbFactory::create($file_path);
            if ( $type == 2 ) {
                $thumb->adaptiveResize($width, $height);
            } else if ( $type == 1 ) {
                $thumb->cropFromCenter($width, $height);
            } else {
                $thumb->resize($width, $height);
            }

            $thumb->show();
        } else {
            @readfile("files/static/icon.gif");
        }
    } else {
        if ( get('type') == 'view' ) {
            if ( in_array($file['type'], $img_types) ) {
                loadClass('PHPThumb');
                $thumb = PhpThumbFactory::create($file_path);
                $thumb->show();
            } else {
                @readfile("files/static/icon.gif");
            }
        } else {
            @header("Content-Disposition: attachment; filename={$file['name']}");
            @header("Content-Type:{$file['type']}");
            @readfile($file_path);
        }
    }
    //fix unreadable content
    exit;

}

?>