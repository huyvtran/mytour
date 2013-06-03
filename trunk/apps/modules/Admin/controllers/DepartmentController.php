<?php

class AdminDepartmentController extends Zone_Action {

    public function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                label => 'Tên phòng',
                no_empty => true
            ),
            ord => array(
                type => 'INT',
                label => 'Thứ tự',
            ),
            parent_id => array(
                type => 'INT',
                label => 'Trực thuộc',
            ),
            desc => array(
                type => 'TEXT',
                label => 'Mô tả tài liệu'
            )
        );
        return $data;

    }

    public function indexAction() {
        $posts = self::$Model->fetchAll("SELECT * FROM `departments` ORDER BY `ord`");
        self::set('departments', $posts);

    }

    public function addAction() {
        self::removeLayout();
        $posts = Plugins::getOptions("departments");
        self::set('departments', $posts);

        if ( isPost() ) {
            loadClass('ZData');
            $form = new ZData();
            $form->addField(self::fields());
            $data = $form->getData();
            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            self::$Model->insert("departments", $data);
            removeCache('departments');

            self::setJSON(array(
                close => true,
                redirect => "#Admin/Department"
            ));
        }

    }

    public function editAction() {
        self::removeLayout();
        $posts = Plugins::getOptions("departments");
        self::set('departments', $posts);

        $department_id = getInt('ID', 0);
        $post = self::$Model->fetchRow("SELECT * FROM `departments` WHERE `ID`='$department_id'");

        if ( !$post ) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }

        self::set('post', $post);

        if ( isPost() ) {
            loadClass('ZData');
            $form = new ZData();
            $form->addField(self::fields($post));
            $data = $form->getData();

            if ( get('parent_id') == $department_id ) {
                self::setJSON(array(
                    message => error('Phòng ban trực thuộc không hợp lệ')
                ));
            }

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            self::$Model->update("departments", $data, "`ID`='$department_id'");
            removeCache('departments');
            self::setJSON(array(
                close => true,
                redirect => "#Admin/Department/"
            ));
        }

    }

    public function deleteAction() {
        $department_id = getInt("ID", 0);
        $post = self::$Model->fetchRow("SELECT * FROM `departments` WHERE `ID`='$department_id'");
        if ( !$post ) {
            self::setJSON(array(
                alert => error(translate('default.delete.post_not_found'))
            ));
        }

        self::$Model->update("users", array("department_id" => $post['parent_id']), "`department_id`='$department_id'");
        self::$Model->update("departments", array("parent_id" => $post['parent_id']), "`parent_id`='$department_id'");

        self::$Model->delete("departments", " `ID`='$department_id' ");
        removeCache('departments');

        self::setJSON(array(
            redirect => "#Admin/Department"
        ));

    }

    public function ordAction() {
        if ( isPost() ) {
            $ids = getInt('ID', array(), 2);
            $ords = getInt('ord', array(), 2);
            foreach ( $ids as $k => $id ) {
                self::$Model->update('departments', array(
                    ord => $ords[$k]
                        ), " `ID`='$id'");
            }
        }
        self::setJSON(array(
            redirect => '#Admin/Department'
        ));

    }

}

?>