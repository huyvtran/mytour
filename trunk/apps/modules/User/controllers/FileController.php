<?php

/**
 * Upload file to cache
 *
 * @author Nguyen Duc Minh
 * @date created Jul 14, 2012
 */
class UserFileController extends Zone_Action {

    const CACHE_FOLDER = 'upload';

    public function init() {
        $path = get('path', self::CACHE_FOLDER);
        $store_path = "files/$path";

        if ( !file_exists($store_path) ) {
            @mkdir($store_path);
        }

        if ( !is_writable($store_path) ) {
            self::setJSON(array(
                alert => error(translate('default.system_not_allow_to_upload'))
            ));
        }

        //remove all file upload over two days
        $files = self::$Model->fetchAll("SELECT * FROM `cache_files`
                WHERE `created_by_id`='$user_id'
                    AND (`date_created` IS NULL
                        OR ADDTIME(`date_created`,'24:00:00') < NOW())");

        if ( count($files) > 0 ) {
            $ids = array();
            foreach ( $files as $f ) {
                $ids[] = $f['ID'];
                @unlink($store_path . '/' . $f['name']);
            }
            $ids = implode(',', $ids);
            self::$Model->delete('cache_files', "`ID` IN ($ids)");
        }

    }

    public function uploadAction() {
        $path = get('path', self::CACHE_FOLDER);
        $store_path = "files/$path";

        self::removeLayout();
        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();

            $f->addField(array(
                file => array(
                    type => 'FILE',
                    path => $store_path,
                    no_empty => true
                )
            ));

            $data = $f->getData();

            if ( !is_array($data) ) {
                self::setJSON(array(
                    alert => $data
                ));
            }

            $file = $data['file'];
            self::$Model->insert('cache_files', array(
                name => $file[0],
                type => $file[1],
                size => $file[2],
                filename => $file[3],
                path => $path,
                created_by_id => get_user_id(),
                date_created => new Model_Expr('NOW()')
            ));

            $file_id = self::$Model->lastId('cache_files');
            $file_name = $file[3];
            $file_size = smart_file_size($file[2]);

            $input_name = get('name');

            self::setJSON(array(
                content => "<div class='x-file-info'>
                    <input checked type='checkbox' name='$input_name' value='$file_id'/>
						$file_name [$file_size]
						</div>"));
        }
        self::setContent('');
    }
}