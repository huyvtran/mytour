<?php

class ConfigPartnersController extends Zone_Action {

    public function indexAction() {
        
        $hotel_id = get_hotel_id();
        
        loadClass('ZList');
        $list = new ZList();

        $list->setVar(array(
            
        ));
        $list->setPageLink('#Config/Partners');
        $list->setSqlCount("SELECT COUNT(*) FROM `partners`");
        $list->setSqlSelect("SELECT * FROM `partners`");
        $list->setWhere("`hotel_id` = '$hotel_id'");
        $list->setOrder("`title`");
        
        $list->addFieldOrder(array(
            '`title`' => 'title',
            '`desc`' => 'desc'
        ));
        
        $list->addFieldText(array(
            '`title`' => 's'
        ));

        $list->run();
        self::set(array(
            post => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));
    }

    protected function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Tên đối tác'
            ),
            desc => array(
                type => 'CHAR',
                label => 'Mô tả'
            )
        );
        return $data;
    }

    public function addAction() {
        self::removeLayout();
        $hotel_id = get_hotel_id();
        
        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = $f->getData();
            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }
            self::$Model->insert('partners', $data);
            $last_id = self::$Model->lastId();
            
            self::$Model->update('partners', array(
                hotel_id => $hotel_id
            ), "`ID`='$last_id'");
            
            self::setJSON(array(
                close => true,
                redirect => '#Config/Partners'
            ));
        }
    }

    public function editAction() {
        $id = getInt('ID');
        self::removeLayout();

        $post = self::$Model->fetchRow("SELECT * FROM `partners` WHERE `ID`='$id'");
//        if (!$post) {
//            self::setJSON(array(
//                message => error('Bản ghi không tồn tại hoặc đã bị xóa')
//            ));
//        }
        self::set(array(
            post => $post
        ));

        if (isPost()) {
            $id = getInt('ID');
            loadClass('ZData');
            $f = new ZData();
            $f->addFields(self::fields());
            $data = $f->getData();
            
            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }
            
            self::$Model->update('partners', $data, "`ID`='$id'");
            self::setJSON(array(
                close => true,
                redirect => '#Config/Partners'
            ));
            
        }
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('partners', "`ID` IN ($cond)");
        }

        self::setJSON(array(
            reload => true
        ));
    }

}
