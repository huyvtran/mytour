<?php

class ZFeed {

    protected $items = array();
    //static
    protected $xmlVersion = "1.0";
    protected $rssVersion = "2.0";
    protected $rssTitle = "";
    protected $rssDesc = "";
    protected $rssLink = "";
    protected $datePublic = NULL;
    protected $dateBuild = NULL;

    function __construct() {
        $this->datePublic = date('today');
        $this->dateBuild = date('today');

    }

    //add one or many
    public function addItem( $item ) {
        $this->items[] = $item;

    }

    //show all source
    public function getSource() {
        $code = '<?xml version="' . $this->xmlVersion . '" encoding="utf-8"?>
<rss version="' . $this->rssVersion . '">
<channel>
	<title>' . $this->ressTitlte . '</title>
	<description>' . $this->rssDesc . '</description>
	<link>' . $this->rssLink . '</link>
	<copyright>' . $this->copyright . '</copyright>
	<generator>' . $this->generator . '</generator>
	<pubDate>' . $this->datePublic . '</pubDate>
	<lastBuildDate>' . $this->dateBuild . '</lastBuildDate>';

        //join item
        foreach ( $this->items as $item ) {
            $code .= '	<item>
		<title><![CDATA[ ' . $item['title'] . ' ]]></title>
		<description><![CDATA[ ' . $item['description'] . ' ]]></description>
		<link>' . $item['link'] . '</link>
		<pubDate>' . $item['date'] . '</pubDate>
	</item>';
        }

        $code .= '</channel>
</rss>';
        return $code;

    }

}
