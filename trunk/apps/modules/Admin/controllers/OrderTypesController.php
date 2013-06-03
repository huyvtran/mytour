<?php
class AdminOrderTypesController extends Zone_Action {
    
    public function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                label => 'Hình thức đặt phòng',
                no_empty => true
            ),
            desc => array(
                type => 'CHAR',
                label => 'Mô tả',
            ),
        );
        return $data;

    }
    public function indexAction() {
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Admin/OrderTypes');
        $list->setSqlCount("SELECT COUNT(*) FROM `order_types` as `a`");
        $list->setSqlSelect("SELECT `a`.* FROM `order_types` as `a`");
        $list->setOrder("`a`.`title` DESC");
        
        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
        ));
        
        $list->addFieldText(array(
           '`a`.`title`' => 's',
        ));
        
        $list->run();
       
        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));
    }
    
    public function addAction(){
        self::removeLayout();
        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = $f->getData();
            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }
            self::$Model->insert('order_types', $data);
            self::setJSON(array(
                 close => true,
                 redirect => "#Admin/OrderTypes/"
            ));
        }
        
    }
    public function editAction() {
        self::removeLayout();
        
        $post_id = getInt('ID', 0);        
        $post    = self::$Model->fetchRow("SELECT * FROM `order_types` WHERE `ID`='$post_id'");
        if ( !$post ) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }
        self::set('post', $post);
        
        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields($post));
            $data = $f->getData();
            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            self::$Model->update('order_types', $data, "`ID`='$post_id'");
            self::setJSON(array(
                close => true,
                 redirect => "#Admin/OrderTypes/"
            ));
        }

    }
    
    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if ( count($ids) > 0 ) {
            $cond = implode(',', $ids);
            self::$Model->delete('order_types', "`ID` IN ($cond)");
        }
        self::setJSON(array(
            redirect => "#Admin/OrderTypes/"
        ));

    }
    
}