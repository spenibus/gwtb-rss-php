<?php
/*******************************************************************************
gwtb-rss
version: 20190411-1945
spenibus.net
*******************************************************************************/


error_reporting(0);
mb_internal_encoding('utf-8');

$CFG_TIME = time();

$CFG_HOST        = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
$CFG_SELF        = $_SERVER['SCRIPT_NAME'];
$CFG_REQUEST_URI = $_SERVER['REQUEST_URI'];

$CFG_HOST_SELF        = $CFG_HOST.$CFG_SELF;
$CFG_HOST_REQUEST_URI = $CFG_HOST.$CFG_REQUEST_URI;

$CFG_URL_SOURCE = 'http://www.blastwave-comic.com/index.php';

$CFG_URL_STRIP = 'http://www.blastwave-comic.com/index.php?p=comic&nro=';

$CFG_CACHE_FILE = './cache/items.xml';


//******************************************************************************
function hsc($str='') {
    return htmlspecialchars($str);
}


//**************************************************************** rebuild cache
if(
    // no cache
    !file_exists($CFG_CACHE_FILE)
    // empty cache
    || filesize($CFG_CACHE_FILE) == 0
    // old cache
    || $CFG_TIME - filemtime($CFG_CACHE_FILE) > 3600
) {
    $src = file_get_contents($CFG_URL_SOURCE);

    // build DOM
    $doc = new DOMDocument();
    $doc->loadHTML($src);

    $xpath = new DOMXPath($doc);

    // https://stackoverflow.com/a/5582811/3512867
    // https://stackoverflow.com/a/33411630/3512867
    $items = $xpath->query("//select[@name='nro']/option[string-length(@value)!=0][position()<=30]");

    $rss_items = '';

    foreach($items as $item) {

        $rss_items .= '
            <item>
                <title>'.hsc($item->nodeValue).'</title>
                <link>'.hsc($CFG_URL_STRIP.$item->getAttribute('value')).'</link>
            </item>';
    }

    // save cache
    file_put_contents($CFG_CACHE_FILE, $rss_items);
}

// serve cache
header('content-type: application/xml');

$rss_items = file_get_contents($CFG_CACHE_FILE);

exit('<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>Gone with the Blastwave</title>
        <description>Gone with the Blastwave unofficial rss feed</description>
        <pubDate>'.gmdate(DATE_RSS).'</pubDate>
        <link>'.hsc($CFG_HOST_REQUEST_URI).'</link>'.
        $rss_items.'
    </channel>
</rss>');
?>