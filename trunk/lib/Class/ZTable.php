<?php

/**
 * Make a table list width scroll
 *  _____________________________________________
 * | | ................................| actions |
 * | | ................................|         |
 * | | ................................|         |
 * | | ................................|         |
 * -> scroll
 *
 * @author Nguyen Duc Minh
 * @date created Nov 6, 2012
 */
class ZTable {

    private $rows = array();
    private $header = array();
    private $att_rows = array();
    private $action_rows = array();
    private $column_colspan = 1;
    private $action_colspan = 1;
    private $page = null;

    public function addHeader( $columns = array() ) {
        $this->column_colspan = max(count($columns), $this->column_colspan);
        $this->header = $columns;

    }

    public function addRow( $columns = array(), $actions = array(), $atts = array() ) {
        $this->action_colspan = max(count($actions), $this->action_colspan);
        $this->column_colspan = max(count($columns), $this->column_colspan);
        $this->action_rows[] = $actions;
        $this->att_rows[] = $atts;
        $this->rows[] = $columns;

    }

    public function addPage( $page = null ) {
        $this->page = $page;

    }

    public function getHTML() {
        if ( count($this->rows) == 0 )
            return 'Chưa có bản ghi nào được tạo';
        $id = getId();
        $action_width = 10 * $this->action_colspan;
        $row_html = $this->getRowHTML();
        $action_html = $this->getActionHTML();

        $html = "<table class='list' id='$id' width='100%' border='0' cellpadding='0' cellspacing='0'>";
        $html .= "<tr>";
        $html .= "<td valign='top'>
            <div class='list-rows scroll'>
            {$row_html}
        </div></td>";
        $html .= "<td width='$action_width' valign='top'>
                <div class='list-action'>
                $action_html
            </div></td>";
        $html .= "</tr>";

        if ( $this->page ) {
            $colspan = 2;
            $html .="<tr><td class='page' colspan='$colspan'>" . $this->page . "</td></tr>";
        }

        $html .="</table>";
        $html .="<script>
            $('#$id').each(function(){
                var width = $(this).width() - $(this.rows[0].cells[1]).width();
                $(this).find('.list-rows').css({width: width +'px',visibility:'visible'});
            });
        </script>";
        return $html;

    }

    protected function getHeaderHTML() {
        $html = '';
        for ( $i = 0; $i < $this->column_colspan; $i++ ) {
            $a = $this->header[$i];
            $html .= "<th nowrap='nowrap'>{$a}</th>";
        }

        $html = "<tr>$html</tr>";
        return $html;

    }

    protected function getActionHTML() {
        $html = "<table class='mini' width='100%' border='0' cellpadding='0' cellspacing='0'>";
        $html .="<th colspan='$this->action_colspan' align='center'>Tác vụ</th>";
        foreach ( $this->action_rows as $columns ) {
            $html .="<tr>";
            for ( $i = 0; $i < $this->action_colspan; $i++ ) {
                $col = $columns[$i];
                $html .= "<td width='10'>{$col}</td>";
            }
            $html .="</tr>";
        }
        $html .="</table>";
        return $html;

    }

    protected function getRowHTML() {
        $header_html = $this->getHeaderHTML();
        $html = "<table class='mini' width='100%' border='0' cellpadding='0' cellspacing='0'>";
        $html .=$header_html;
        foreach ( $this->rows as $k => $cols ) {
            $html .="<tr>";
            for ( $i = 0; $i < $this->column_colspan; $i++ ) {
                $att = array();
                if ( is_array($this->att_rows[$k][$i]) ) {
                    foreach ( $this->att_rows[$k][$i] as $j => $v ) {
                        $att[] = $j . '="' . addslashes($v) . '"';
                    }
                }
                $at = '';
                if ( count($att) > 0 )
                    $at = ' ' . implode(' ', $att);

                $col = $cols[$i];
                $html .= "<td nowrap='nowrap'{$at}>{$col}</td>";
            }
            $html .="</tr>";
        }
        $html .="</table>";
        return $html;

    }

    public function show() {
        _e($this->getHTML());

    }

}

?>
