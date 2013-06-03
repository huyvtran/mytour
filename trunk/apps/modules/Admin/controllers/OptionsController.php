<?php

class AdminOptionsController extends Zone_Action {

    private function modules() {
        return array(
            general => array(
                positions => 'Chức vụ công việc'
            ),
            project => array(
                projects_types => 'Loại dự án'
            ),
            task => array(
                tasks_types => 'Loại công việc'
            ),
            diploma => array(
                diploma_types => 'Loại công văn'
            ),
            crm => array(
                crm_customer_types => 'Nhóm khách hàng',
                crm_contract_types => 'Loại hợp đồng',
                crm_trade_types => 'Loại ngành nghề',
                crm_transaction_types => 'Loại giao dịch',
                crm_opportunity_types => 'Giai đoạn bán hàng'
            ),
            asset => array(
                asset_groups => 'Nhóm tài sản',
                asset_types => array('Loại tài sản', 'asset_groups'),
                assets_assign_deposits => 'Tài sản đặt cọc',
                asset_status => 'Trạng thái tài sản',
                asset_use_status => 'Tình trạng tài sản',
            ),
            document => array(
                document_types_parent => 'Loại tài liệu cha',
                document_types => array('Loại tài liệu con', 'document_types_parent')
            ),
            personnel => array(
                personnel_recruiment_types => 'Hình thức công việc tuyển dụng',
                personnel_specializations => 'Chuyên ngành đào tạo',
                personnel_institutions => 'Cơ sở đào tạo',
                position_titles => 'Chức danh công việc',
                personnel_gov_positions => 'Chức danh đảng viên',
                personnel_union_positions => 'Vị trí đoàn viên',
                personnel_army_positions => 'Chức danh quân sự',
                personnel_marriage_status => 'Tình trạng hôn nhân',
                personnel_religions => 'Tôn giáo',
                personnel_nations => 'Dân tộc',
                personnel_contact_relations => 'Quan hệ cá nhân',
                personnel_jobleave_reasons => 'Lý do nghỉ việc',
                personnel_blood_groups => 'Nhóm máu',
                personnel_salary_methods => 'Hình thức trả lương',
                personnel_train_types => 'Xếp loại bằng cấp',
                personnel_work_types => 'Hình thức làm việc',
                personnel_work_places => 'Nơi làm việc',
                personnel_contract_deadlines => 'Thời hạn hợp đồng',
                personnel_training_types => 'Loại đào tạo',
                personnel_degrees => 'Bậc đào tạo',
                personnel_contract_types => 'Hợp đồng nhân sự',
                personnel_recruiment_salarys => 'Lương tuyển dụng',
                personnel_leave_types => 'Lý do đơn xin nghỉ',
               /* personnel_meeting_types => 'Lý do vắng mặt',*/
                personnel_lunch_levels => 'Tầng',
                personnel_lunch_rows => 'Dãy',
                personnel_premium_places => 'Nơi khám chữa bệnh',
                personnel_premium_codes => 'Mã tỉnh cấp',
                personnel_premium_modes => 'Chế độ',
            ),
            finance => array(
                finance_bills => 'Loại phiếu chi',
                //finance_moneys => 'Loại tiền',
                finance_vats => 'VAT(%)'
            ),
        );

    }

    private function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                label => 'Tiêu đề'
            )
        );
        return $data;

    }

    public function indexAction() {
        $tab = get('tab', 'general');
        $modules = $this->modules();
        $tbs = isset($modules[$tab]) ? $modules[$tab] : $modules['general'];
        $options = array();

        foreach ( $tbs as $k => $m ) {
            if ( !is_array($m) ) {
                $posts = self::$Model->fetchAll("SELECT * FROM `$k` ORDER BY `title`");
            } else {
                $ids = array(1);
                $posts = array();
                $ts = self::$Model->fetchAll("SELECT * FROM `{$m[1]}` ORDER BY `title`");
                foreach ( $ts as $k1 => $a ) {
                    $ts[$k1]['posts'] = self::$Model->fetchAll("SELECT * FROM `$k`
                            WHERE `parent_id`='{$a['ID']}' ORDER BY `title`");
                    $ids[] = $a['ID'];
                }
                $ids = implode(',', $ids);
                $posts['items'] = $ts;
                $posts['other_items'] = self::$Model->fetchAll("SELECT * FROM `$k` WHERE `parent_id` NOT IN ($ids) ORDER BY `title`");
            }

            $options[] = array(
                module => $k,
                title => $m,
                posts => $posts
            );
        }

        self::set(array(
            options => $options,
            modules => $modules
        ));

    }

    public function addAction() {
        self::removeLayout();
        $tb = get('m', '');
        $module = null;
        foreach ( $this->modules() as $a ) {
            foreach ( $a as $t => $s ) {
                if ( $t == $tb ) {
                    $module = $s;
                    break;
                }
            }
        }

        if ( !$module ) {
            self::setJSON(array(
                alert => error('Bảng không được chấp nhận')
            ));
        }

        if ( is_array($module) ) {
            self::set(array(
                parents => self::$Model->fetchAll("SELECT *
                        FROM `{$module[1]}` ORDER BY `title`")
            ));
        }

        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $fields = self::fields();

            if ( is_array($module) ) {
                $fields['parent_id'] = array(
                    type => 'INT'
                );
            }

            $f->addField($fields);
            $data = $f->getData();
            if ( !is_array($data) ) {
                self::setJSON(array(
                    alert => $data
                ));
            } else {
                self::$Model->insert($tb, $data);
                self::$Model->removeCache($tb);

                self::SetJSON(array(
                    close => true,
                    reload => true
                ));
            }
        }

    }

    public function editAction() {
        self::removeLayout();
        $tb = get('m', '');
        $id = get('ID', 0);

        $module = null;
        foreach ( $this->modules() as $a ) {
            foreach ( $a as $t => $s ) {
                if ( $t == $tb ) {
                    $module = $s;
                    break;
                }
            }
        }

        if ( !$module ) {
            self::setJSON(array(
                alert => error('Bảng không được chấp nhận')
            ));
        }

        if ( is_array($module) ) {
            self::set(array(
                parents => self::$Model->fetchAll("SELECT *
                        FROM `{$module[1]}` ORDER BY `title`")
            ));
        }

        $post = self::$Model->fetchRow("SELECT * FROM `$tb` WHERE `ID`='$id'");
        if ( !$post ) {
            self::setJSON(array(
                alert => 'Bản ghi không tồn tại hoặc đã bị xóa'
            ));
        }
        self::set('post', $post);

        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $fields = self::fields();

            if ( is_array($module) ) {
                $fields['parent_id'] = array(
                    type => 'INT'
                );
            }
            $f->addField($fields);

            $data = $f->getData();
            if ( !is_array($data) ) {
                self::setJSON(array(
                    alert => $data
                ));
            } else {
                self::$Model->update($tb, $data, "`ID`='$id'");
                self::$Model->removeCache($tb);

                self::setJSON(array(
                    close => true,
                    reload => true
                ));
            }
        }

    }

    public function deleteAction() {
        $tb = get('m', '');
        $id = get('ID', 0);

        self::$Model->delete($tb, "`ID`='$id'");
        self::$Model->removeCache($tb);

        self::SetJSON(array(
            reload => true
        ));

    }

}

?>