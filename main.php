<?php
/**
 * DokuWiki DokuCMS Template
 *
 * @link   http://wiki.splitbrain.org/wiki:tpl:templates
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Klaus Vormweg <klaus.vormweg@gmx.de>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

// include custom template functions stolen from arctic template
require_once(dirname(__FILE__).'/tpl_functions.php');

echo '
<!DOCTYPE html>
<html lang="', $conf['lang'], '" dir="', $lang['direction'], '">
<head>
  <meta charset="utf-8" />
  <title>',"\n";
tpl_pagetitle();
echo '[', strip_tags($conf['title']), ']
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />',"\n";
tpl_metaheaders();
echo tpl_favicon(array('favicon', 'mobile'));
echo '
<!--[if lt IE 7]>
   <style type="text/css">
      div.page { width: 55em !important; }
   </style>
<![endif]-->
</head>

<body>
<div class="dokuwiki">',"\n";
html_msgarea();
echo '  <header class="stylehead">
    <div class="header">
      <div class="pagename">',"\n";
tpl_link(wl(),$conf['title'],'name="dokuwiki__top" id="dokuwiki__top" accesskey="h" title="[ALT+H]"');
echo '      </div>
      <div class="clearer"></div>
    </div>',"\n";
if($conf['breadcrumbs']){
  echo '    <div class="breadcrumbs">',"\n";
  tpl_breadcrumbs();
  echo '  </div>',"\n";
}

if($conf['youarehere']){
  echo '    <div class="breadcrumbs">',"\n";
  tpl_youarehere();
  echo '    </div>',"\n";
}
echo '  </header>',"\n";
tpl_flush();

if($ACT != 'diff' and $ACT != 'edit' and $ACT != 'preview' and $ACT != 'admin' and 
   $ACT != 'login' and $ACT != 'logout' and $ACT != 'profile' and $ACT != 'revisions') {
  echo '  <div class="wrap">
     <nav class="sidebar">
     <input type="checkbox" id="hamburger" class="hamburger" />
     <label for="hamburger" class="hamburger" title="Menu">&#9776; <span class="vishelp">Menu</span></label>',"\n";
  _tpl_sidebar(); 
  echo '   </nav>
     <div class="page">',"\n";
  tpl_content(); 
  echo '   </div>
  </div>',"\n";
} else {
  echo '<div class="wrap" style="background-color: #fff;">
     <div class="page" style="margin-left: 0; max-width: 78em;">',"\n";
  tpl_content();
  echo '   </div>
  </div>',"\n";
}
tpl_flush();
echo '  <footer class="stylefoot">',"\n";
if($ACT != 'diff' and $ACT != 'edit' and $ACT != 'preview' and $ACT != 'admin' 
   and  $ACT != 'login' and $ACT != 'logout' and $ACT != 'profile' and $ACT != 'revisions') {
  echo '     <div class="homelink">
        <a href="http://wiki.splitbrain.org/wiki:dokuwiki" title="Driven by DokuWiki">',
        '<img src="', DOKU_TPL, 'images/button-dw.png" width="80" height="15" alt="Driven by DokuWiki" /></a>
        <a href="', DOKU_BASE, 'feed.php" title="Recent changes RSS feed">',
        '<img src="', DOKU_TPL, 'images/button-rss.png" width="80" height="15" alt="Recent changes RSS feed" /></a>
      </div>

    <div class="meta">',"\n"; 
  _tpl_pageinfo();
  echo '  </div>',"\n";
} else {
	echo '
    <div class="meta">
    </div>',"\n";
}
echo '    <div class="bar" id="bar__bottom">
       <div class="bar-left" id="bar__bottomleft">',"\n";
tpl_button('admin');
if($ACT != 'login' and $ACT != 'logout') { 
  tpl_button('login');
  echo '&nbsp;';
}
if($_SERVER['REMOTE_USER']){
  tpl_button('subscribe');
	tpl_button('profile');
	tpl_button('history');
  tpl_button('revert');
}
if($conf['tpl']['dokucms']['showbacklinks']) {
  tpl_button('backlink');
  echo '&nbsp;';
}
echo '         &nbsp;
       </div>
       <div class="bar-right" id="bar__bottomright">',"\n";
if(!$_SERVER['REMOTE_USER']){ 
  tpl_searchform();
  echo '&nbsp';
  if($conf['tpl']['dokucms']['showmedia'] and $ACT != 'login' and $ACT != 'logout') {   
    tpl_button('media');
  }
} else {
  if($ACT != 'login' and $ACT != 'logout'){
    if($conf['tpl']['dokucms']['showsearch']) {  
      tpl_searchform();
      echo '&nbsp';
    }
    tpl_button('media');
  }
}
tpl_button('edit');
echo '&nbsp;
      </div>
      <div class="clearer"></div>
    </div>
  </footer>',"\n";
tpl_license(false);
echo '
</div>
<div class="no">';
/* provide DokuWiki housekeeping, required in all templates */ 
tpl_indexerWebBug();
echo '</div>
</body>
</html>',"\n";
?>
