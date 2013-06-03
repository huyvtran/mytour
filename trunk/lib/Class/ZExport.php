<?php

/**
  Last modified 28/10/2011
 */
class ZExport {

    private $data;
    private $type;
    private $fields;
    private $columns;
    private $order;
    private $title;
    private $file;

    function __constructor() {
        $this->file = 'Export';

    }

    public function set( $fields, $columns, $data, $type, $order = 'yes' ) {
        $this->data = (array) $data;
        $this->type = $type;
        $this->fields = $fields;
        $this->columns = $columns;
        $this->order = $order;

    }

    public function setTitle( $title ) {
        $this->title = $title;

    }

    public function setFile( $file ) {
        $this->file = $file;

    }

    public static function htmlOptions( $name = 'filetype', $class = 'x-select' ) {
        return "<select name='$name' class='$class'>
			<option value='xml'>Exel XML</option>
			<option value='html'>Exel HTML</option>
		</select>";

    }

    public function export() {
        $type = $this->type;
        if ( $type == 'xml' ) {
            $this->exportExelXML();
        } else {
            $this->exportHTML();
        }

    }

    public function exportExelXML() {
        header('content-type: text/xml');
        header('Content-disposition: attachment; filename="' . $this->file . '.xml"');

        list($data, $type, $fields, $columns, $order) = array($this->data, $this->type, $this->fields, $this->columns, $this->order);

        $ncolumn = 0;
        if ( $order ) {
            $ncolumn++;
        }
        foreach ( $columns as $c ) {
            if ( !isset($fields[$c]) ) {
                continue;
            }
            $ncolumn++;
        }

        $html = '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Created>2006-09-16T00:00:00Z</Created>
  <LastSaved>2011-11-02T15:27:29Z</LastSaved>
  <Version>12.00</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <RemovePersonalInformation/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8010</WindowHeight>
  <WindowWidth>14805</WindowWidth>
  <WindowTopX>240</WindowTopX>
  <WindowTopY>105</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Sheet1">
  <Table ss:ExpandedColumnCount="' . $ncolumn . '" ss:ExpandedRowCount="' . (count($data) + 1) . '" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15"><Row>';

        if ( $order ) {
            $html .= "<Cell><Data ss:Type=\"String\">TT</Data></Cell>";
        }

        foreach ( $columns as $c ) {
            if ( !isset($fields[$c]) ) {
                continue;
            }
            $label = $fields[$c][0];
            $html .= '<Cell><Data ss:Type="String">' . $label . '</Data></Cell>';
        }

        $html .= '</Row>';

        foreach ( $data as $k => $row ) {
            $html .= '<Row>';
            if ( $order ) {
                $html .= "<Cell><Data ss:Type=\"String\">" . ($k + 1) . "</Data></Cell>";
            }
            foreach ( $columns as $c ) {
                if ( !isset($fields[$c]) ) {
                    continue;
                }

                $label = preg_replace("#\<br\/?\>#i", "\n", $row[$c]);
                $label = strip_tags($label);

                $html .= '<Cell><Data ss:Type="String">';
                $html .= $label;
                $html .= '</Data></Cell>';
            }
            $html .= '</Row>';
        }

        $html .= '</Table>
			  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
			   <PageSetup>
				<Header x:Margin="0.3"/>
				<Footer x:Margin="0.3"/>
				<PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
			   </PageSetup>
			   <Selected/>
			   <Panes>
				<Pane>
				 <Number>3</Number>
				 <ActiveRow>12</ActiveRow>
				 <ActiveCol>8</ActiveCol>
				</Pane>
			   </Panes>
			   <ProtectObjects>False</ProtectObjects>
			   <ProtectScenarios>False</ProtectScenarios>
			  </WorksheetOptions>
			 </Worksheet>
			</Workbook>';
        die($html);

    }

    protected function exportHTML() {
        list($data, $type, $fields, $columns, $order) = array($this->data, $this->type, $this->fields, $this->columns, $this->order);

        header("Content-type: text/html; charset=utf8");
        header('Content-disposition: attachment; filename="' . $this->file . '.html"');
        $html = "<html>
			<head>
				<title>" . $this->title . "</title>
				<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"/>
				<style>
					body{
						padding:10px
					}

					body,td,th{
						font-family:Arial;
						font-size:13px;
						text-align:left
					}
					table{
						border-collapse:collapse;
					}
				</style>
			</head>
			<body>
			<table border='1' cellpadding='5' align='center'>";
        $html .= "<tr>";

        if ( $order ) {
            $html .= "<th>TT</th>";
        }

        foreach ( $columns as $c ) {
            if ( !isset($fields[$c]) ) {
                continue;
            }
            $label = $fields[$c][0];
            $html .= "<th>$label</th>";
        }
        $html .= "</tr>";

        foreach ( $data as $k => $d ) {
            $html .= "<tr>";

            if ( $order ) {
                $html .= "<td>" . ($k + 1) . "</td>";
            }

            foreach ( $columns as $c ) {
                if ( !isset($fields[$c]) ) {
                    continue;
                }
                $label = $d[$c];
                $html .= "<td>$label</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</table></body></html>";
        die($html);

    }

}

?>