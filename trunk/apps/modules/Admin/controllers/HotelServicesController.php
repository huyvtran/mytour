<?php
class AdminHotelServicesController extends Zone_Action {
    
    public function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                label => translate('default.admin.hotel.title'),
                no_empty => true
            ),
            desc => array(
                type => 'CHAR',
                label => translate('default.admin.hotel.desc'),
            ),
        );
        return $data;

    }
    public function indexAction() {
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Admin/HotelServices');
        $list->setSqlCount("SELECT COUNT(*) FROM `hotel_service_types` as `a`");
        $list->setSqlSelect("SELECT `a`.* FROM `hotel_service_types` as `a`");
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
            self::$Model->insert('hotel_service_types', $data);
            self::setJSON(array(
                 close => true,
                 redirect => "#Admin/HotelServices/"
            ));
        }
        
    }
    public function editAction() {
        self::removeLayout();
        
        $post_id = getInt('ID', 0);        
        $post    = self::$Model->fetchRow("SELECT * FROM `hotel_service_types` WHERE `ID`='$post_id'");
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

            self::$Model->update('hotel_service_types', $data, "`ID`='$post_id'");
            self::setJSON(array(
                close => true,
                 redirect => "#Admin/HotelServices/"
            ));
        }

    }
    
    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if ( count($ids) > 0 ) {
            $cond = implode(',', $ids);
            self::$Model->delete('hotel_service_types', "`ID` IN ($cond)");
        }
        self::setJSON(array(
            redirect => "#Admin/HotelServices/"
        ));

    }
    
}