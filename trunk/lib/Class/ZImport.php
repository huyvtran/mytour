<?php

/**
 * This class read data from a excel 2007 file quickly
 *
 *
 * @author Nguyen Duc Minh
 * @date created Sep 29, 2012
 * @Helper
 *  http://php.net/manual/en/function.simplexml-load-file.php
 *  http://fuelyourcoding.com/reading-xml-with-php/
 *  http://msdn.microsoft.com/en-us/library/office/gg278316.aspx
 */
class ZImport {

    const SHARED_STRING_PATH = 'xl/sharedStrings.xml';
    const WORKSHEET_PATH = 'xl/worksheets';
    const COMMENT_PATH = 'xl';

    private $words;
    private $comments = array();
    private $sheet;

    public function load( $file, $worksheet = 1 ) {
        //get_file_type($file);
        //'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        //load shared strings

        $f = $file;
        if ( array_key_exists('tmp_name', $file) ) {
            $f = $file['tmp_name'];
            $file_type = get_file_type($file['name']);
        } else {
            //a file path
            $file_type = get_file_type($file);
        }

        if ( $file_type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ) {
            throw new Exception('File must be an excel 2007 file');
        }

        $z = new ZipArchive();
        if ( $z->open($f) ) {
            $fp = $z->getStream(self::SHARED_STRING_PATH);
            $str = '';
            if ( $fp )
                while (!feof($fp)) {
                    $str .= fread($fp, 2);
                }

            try {
                $this->words = simplexml_load_string($str);
            } catch (Exception $e) {
                throw new Exception('Can not parse file');
            }

            //load worksheet
            $str = '';
            $fp = $z->getStream(self::WORKSHEET_PATH . "/sheet{$worksheet}.xml");
            if ( $fp )
                while (!feof($fp)) {
                    $str .= fread($fp, 2);
                }

            try {
                $this->sheet = simplexml_load_string($str);
            } catch (Exception $e) {
                throw new Exception('Can not parse file');
            }

            //load comments may be empty
            $str = '';
            $fp = $z->getStream(self::COMMENT_PATH . "/comments1.xml");
            if ( $fp )
                while (!feof($fp)) {
                    $str .= fread($fp, 2);
                }

            if ( !empty($str) ) {
                try {
                    $results = array();
                    $comments = simplexml_load_string($str);
                    $comments = $comments->commentList->comment;
                    foreach ( $comments as $com ) {
                        $item = (string) $com->attributes()->ref;
                        $text = $com->text[0];
                        $results[$item] = (string) $text->r[1]->t[0];
                    }
                    $this->comments = $results;
                } catch (Exception $e) {
                    throw new Exception('Can not parse file');
                }
            }
        }
        @$z->close();

    }

    public function getWord( $index, $escape_html = true ) {
        $words = $this->words->si;
        $ts = $words[$index]->t;
        $s = (string) $ts[0];
        return $escape_html ? html_escape($s) : $s;

    }

    public function count() {
        return count($this->sheet->sheetData[0]->row);

    }

    public function getColumn( $name, $escape_html = true ) {
        $rows = $this->sheet->sheetData[0]->row;
        $row_start = $rows[0];
        if ( is_null($row_start) )
            return null;

        $att_starts = $row_start->attributes();
        $lag = (int) $att_starts->r;
        $lag = $lag > 0 ? $lag : 0;

        $matches = array();
        if ( preg_match("#^([A-Z]+)(\d+)$#is", $name, $matches) ) {
            //max row can include
            $row = (int) $matches[2];
            foreach ( $rows[$row - $lag]->c as $r ) {
                if ( $name == $r->attributes()->r ) {
                    if ( !property_exists($r->attributes(), 't') )
                        return $r->v[0];
                    //data type
                    return $this->getWord((int) $r->v[0], $escape_html);
                }
            }
        } else {
            return null;
        }

    }

    public function getColumnComment( $name ) {
        return $this->comments[$name];

    }

}