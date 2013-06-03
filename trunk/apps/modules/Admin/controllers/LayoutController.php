<?php

class AdminLayoutController extends Zone_Action {

    public function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                label => 'Tiêu đề'
            ),
            desc => array(
                type => 'CHAR',
                label => 'Sologan'
            ),
            time_notice => array(
                type => 'INT',
                min => 5,
                no_empty => true,
                label => 'Độ trễ thông báo'
            ),
            time_chat => array(
                type => 'INT',
                min => 5,
                no_empty => true,
                label => 'Độ trễ chat'
            ),
            book_latency => array(
                type => 'INT',
                 min => 1,
                no_empty => true,
                label => 'Độ trễ book phòng'
            ),
            security_code => array(
                type => 'CHAR',
                no_empty => true
            ),
            email_host => array(
                type => 'CHAR'
            ),
            email_port => array(
                type => 'INT'
            ),
            email_account => array(
                type => 'CHAR'
            ),
            email_password => array(
                type => 'CHAR'
            ),
            email_name => array(
                type => 'CHAR'
            )
        );
        return $data;

    }

    public function indexAction() {

        if ( isPost() ) {
            self::removeLayout();
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields());
            $data = $f->getData();
            if ( !is_array($data) ) {
                self::setJSON(array(
                    alert => $data
                ));
            }

            $configs = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");
            if ( $configs ) {
                self::$Model->update('configs', $data);
            } else {
                self::$Model->insert('configs', $data);
            }


            self::setJSON(array(
                reload => true
            ));
        }

        $configs = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");
        self::set('configs', $configs);

    }

    public function logoAction() {
        self::removeLayout();
        if ( !isPost() ) {
            self::setJSON(array(
                error => 'Truy cập không được chấp nhận'
            ));
        }

        loadClass('ZData');
        $f = new ZData();
        $f->addField(array(
            logo => array(
                type => 'FILE',
                return_name => true,
                path => 'files/configs',
                default_value => 'logo.png'
            )
        ));

        $data = $f->getData();
        if ( !is_array($data) ) {
            self::setJSON(array(
                error => $data
            ));
        }

        self::setJSON(array(
            callback => "(function(){
						window.location.reload();
					})()"
        ));

    }

}

?>