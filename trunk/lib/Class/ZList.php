<?php

/**
 * Get a list records in controller
 *
 * @author Nguyen Duc Minh
 * @date modified Nov 27, 2012
 */
class ZList {

    private $vars = array();
    private $where = array();
    private $field_orders = array();
    private $field_equals = array();
    private $field_texts = array();
    private $field_groups = array();
    private $order_by = '';
    private $group_by = '';
    private $having = array();
    private $sqlCount = '';
    private $sqlSelect = '';
    private $posts = array();
    private $total = 0;
    private $total_page = 1;
    private $pageLink = '';
    private $page = '';
    private $page_type = 'ajax';
    private $page_range = null;
    private $current_page = null;
    private $has_limit = true;

    public function setGroupBy( $cond ) {
        $this->group_by = $cond;

    }

    public function setHaving( $cond ) {
        $this->having[] = preg_match("#( AND | OR )#is", $cond, $m) ? "($cond)" : $cond;

    }

    public function setLimit( $bool = true ) {
        $this->has_limit = $bool;

    }

    public function setWhere( $cond ) {
        $this->where[] = preg_match("#( AND | OR )#is", $cond, $m) ? "($cond)" : $cond;

    }

    public function setOrder( $f ) {
        $this->order_by = "ORDER BY $f";

    }

    public function setVar( $a ) {
        foreach ( $a as $k => $v )
            $this->vars[$k] = $v;

    }

    public function setPageLink( $url ) {
        $this->pageLink = $url;

    }

    public function setSqlCount( $sql ) {
        $this->sqlCount = $sql;

    }

    public function setSqlSelect( $sql ) {
        $this->sqlSelect = $sql;

    }

    public function setPageType( $type ) {
        $this->page_type = $type;

    }

    public function setPageRange( $int ) {
        $this->page_range = $int;

    }

    public function getPageRange() {
        return $this->page_range;

    }

    public function setCurrentPage( $int ) {
        $this->current_page = $int;

    }

    public function getCurrentPage() {
        return $this->current_page;

    }

    public function getTotalPage() {
        return $this->total_page;

    }

    public function run() {
        foreach ( $this->field_orders as $f => $r ) {
            if ( get('order_by') == $r ) {
                $order_type = (get('order_type') == 'desc' ? 'desc' : 'asc');
                $this->order_by = "ORDER BY $f $order_type";
                $this->vars['order_by'] = $r;
                $this->vars['order_type'] = $order_type;
                break;
            }
        }

        $search = array();
        foreach ( $this->field_texts as $k => $f ) {
            $word = get($f, '');
            if ( $word == '' )
                continue;
            if ( !isset($search[$f]) ) {
                $search[$f] = "$k LIKE '%$word%'";
            } else {
                $search[$f] .= " OR $k LIKE '%$word%'";
            }
            $this->vars[$f] = $_REQUEST[$f];
        }

        foreach ( $search as $a ) {
            $this->setWhere($a);
        }

        foreach ( $this->field_equals as $k => $f ) {
            $word = get($f, '');
            if ( $word == '' )
                continue;
            $this->setWhere("$k='$word'");
            $this->vars[$f] = $_REQUEST[$f];
        }

        foreach ( $this->field_groups as $k => $f ) {
            if ( is_array($_REQUEST[$f]) ) {
                $s = implode(',', get($f, array(), true));
                $vr = implode(',', $_REQUEST[$f]);
            } else {
                $s = get($f, '');
                $vr = $_REQUEST[$f];
            }
            if ( $s != '' ) {
                $this->setWhere(app_search_sql_group($k, $s));
                $this->vars[$f] = $vr;
            }
        }

        $where = '';
        if ( count($this->where) > 0 )
            $where = ' WHERE ' . implode(' AND ', $this->where);

        $group_by = $this->group_by != "" ? " GROUP BY " . $this->group_by : '';
        $having = count($this->having) > 0 ? " HAVING " . implode(' AND ', $this->having) : "";

        $cal = Zone_Base::$Model->fetchAll(implode(' ', array(
                    $this->sqlCount,
                    $where,
                    $group_by,
                    $having,
                )));

        $this->total = $group_by ? count($cal) : $cal[0]['COUNT(*)'];

        $range_page = $this->page_range ? $this->page_range : get_range_page();

        $current_page = $this->current_page ? $this->current_page : get_current_page(ceil($this->total / $range_page));

        $order = $this->order_by != "" ? $this->order_by : "";

        $limit = '';
        if ( $this->has_limit ) {
            $limit = " LIMIT " . (($current_page - 1) * $range_page) . ",$range_page";
        }

        $this->posts = Zone_Base::$Model->fetchAll(implode(' ', array(
                    $this->sqlSelect,
                    $where,
                    $group_by,
                    $having,
                    $order,
                    $limit
                )));

        $a = $this->vars;
        unset($a['p']);

        $j = to_query_configs($a, false);
        $j = $j ? $j . '&p=' : 'p=';

        $this->total_page = ceil($this->total / $range_page);

        if ( $this->page_type == "ajax" ) {
            $this->page = page_ajax($this->pageLink . "?" . $j, $this->total, $range_page, $current_page);
        } else {
            $this->page = page_simple($this->pageLink . "?" . $j, $this->total, $range_page, $current_page);
        }

        return array(
            page => $this->page,
            total => $this->total,
            posts => $this->posts,
            range_page => $range_page,
            current_page => $current_page,
            vars => $this->vars
        );

    }

    public function getPosts() {
        return $this->posts;

    }

    public function getVars() {
        return $this->vars;

    }

    public function getPage() {
        return $this->page;

    }

    public function addFieldEqual( $a ) {
        $this->field_equals = array_merge($this->field_equals, (array) $a);

    }

    public function addFieldOrder( $a ) {
        $this->field_orders = array_merge($this->field_orders, (array) $a);

    }

    public function addFieldGroup( $a ) {
        $this->field_groups = array_merge($this->field_groups, (array) $a);

    }

    public function addFieldText( $a ) {
        $this->field_texts = array_merge($this->field_texts, (array) $a);

    }

}