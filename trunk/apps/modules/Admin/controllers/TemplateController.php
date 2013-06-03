<?php

/**
 * Description of TemplateController
 *
 * @author Nguyen Duc Minh
 * @date created Jul 12, 2012
 */
class AdminTemplateController extends Zone_Action {

    private $modules = array(
        'Nhân sự' => array(
            'PERSONNEL' => 'Hồ sơ nhân sự',
            'PERSONNEL_CONTRACT' => 'Hợp đồng nhân sự'
        ),
        'Tài sản' => array(
            'ASSETS_ASSIGNS' => 'Biên bản bàn giao'
        )
    );

    private function fields() {
        return array(
            title => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Tiêu đề'
            ),
            module => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Phân hệ'
            )
        );

    }

    public function init() {
        self::set('modules', $this->modules);

    }

    public function indexAction() {
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Admin/Template');
        $list->setSqlCount("SELECT COUNT(*) FROM `template_exports` as `a`");
        $list->setSqlSelect("SELECT `a`.* FROM `template_exports` as `a`");

        $list->addFieldEqual(array(
            '`a`.`module`' => 'module'
        ));

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`a`.`module`' => 'module',
            '`a`.`size`' => 'size',
            '`a`.`filename`' => 'filename'
        ));

        $list->run();
        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));

    }

    public function viewAction() {
        $post_id = getInt('ID', 0);
        $file = self::$Model->fetchID("template_exports", $post_id);

        $file_path = "files/upload/{$file['name']}";

        if ( !file_exists($file_path) ) {
            self::setError('404');
        }

        @header("Content-Disposition: attachment; filename={$file['name']}");
        @readfile($file_path);

    }

    public function addAction() {
        self::removeLayout();
        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields());
            $data = $f->getData();

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $file_id = getInt('cache_file');
            $file = get_file_upload($file_id);
            if ( !$file ) {
                self::setJSON(array(
                    message => error(translate('default.file_uplload_not_found'))
                ));
            }

            foreach ( array('filename', 'name', 'size', 'type') as $k ) {
                $data[$k] = $file[$k];
            }

            remove_file_upload($file_id);
            self::$Model->insert('template_exports', $data);

            self::setJSON(array(
                close => true,
                reload => true
            ));
        }

    }

    public function editAction() {
        self::removeLayout();
        $post_id = getInt('ID');

        $post = self::$Model->fetchID('template_exports', $post_id);
        if ( !$post ) {
            self::setJSON(array(
                alert => error(translate('default.edit.post_not_found'))
            ));
        }

        self::set('post', $post);
        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields());
            $data = $f->getData();

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $file_id = getInt('cache_file');
            if( $file_id ){
                $file = get_file_upload($file_id);
                if ( !$file ) {
                    self::setJSON(array(
                        message => error(translate('default.file_uplload_not_found'))
                    ));
                }

                foreach ( array('filename', 'name', 'size', 'type') as $k ) {
                    $data[$k] = $file[$k];
                }

                remove_file_upload($file_id);
                @unlink("files/upload/{$post['name']}");
            }

            self::$Model->update('template_exports', $data, "`ID`='$post_id'");

            self::setJSON(array(
                close => true,
                reload => true
            ));
        }

    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        $id = getInt('ID', 0);
        if ( $id ) {
            $ids[] = $id;
        }

        if ( count($ids) > 0 ) {
            $files = self::$Model->fetchID("template_exports", $ids);
            $ids = implode(',', $ids);
            self::$Model->delete('template_exports', "`ID` IN($ids)");

            foreach ( $files as $file ) {
                @unlink("files/upload/{$file['name']}");
            }
        }

        self::setJSON(array(
            redirect => '#Admin/Template'
        ));

    }

    public function keyAction(){


    }

}