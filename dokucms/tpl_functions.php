<?php
/**
 * DokuWiki Template DokuCMS Functions - adapted from arctic template
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author  Michael Klier <chi@chimeric.de>
 * @author Klaus Vormweg <klaus.vormweg@gmx.de>
 */

if(!defined('DOKU_LF')) define('DOKU_LF',"\n");

/**
 * Prints the sidebars
 *
 */
function tpl_sidebar($pos) {
    global $lang;
    global $conf;
    global $ID;
    global $REV;
    global $INFO;

    $sb = 'index';
    $svID  = $ID;   // save current ID
    $svREV = $REV;  // save current REV 

    print '<div class="sidebar_box">' . DOKU_LF;
    print '  ' . p_index_xhtml($svID,$pos) . DOKU_LF;
    print '</div>' . DOKU_LF;

    // restore ID and REV
    $ID  = $svID;
    $REV = $svREV;
}

/**
 * Renders the Index
 *
 * copy of html_index located in /inc/html.php
 *
 * TODO update to new AJAX index possible?
 *
 */
function p_index_xhtml($ns,$pos) {
  require_once(DOKU_INC.'inc/search.php');
  global $conf;
  global $ID;
  $dir = $conf['datadir'];
  $ns  = cleanID($ns);
  #fixme use appropriate function
  if(empty($ns)){
    $ns = dirname(str_replace(':','/',$ID));
    if($ns == '.') $ns ='';
  }
  $ns  = utf8_encodeFN(str_replace(':','/',$ns));

// extract only the headline
//  preg_match('/<h1>.*?<\/h1>/', p_locale_xhtml('index'), $match);
//  print preg_replace('#<h1(.*?id=")(.*?)(".*?)h1>#', '<h1\1sidebar_'.$pos.'_\2\3h1>', $match[0]);

  $data = array();
  search($data,$conf['datadir'],'search_index',array('ns' => $ns));
# print index with empty items removed  
  print preg_replace('#<li class="level[0-9]" style="list-style-type:none;"><div class="li"></div></li>#','',preg_replace('/li class="level(\d)"/', 'li class="level$1" style="list-style-type:none;"', html_buildlist($data,'idx','_html_list_index','html_li_index')));
}

/**
 * Index item formatter
 *
 * User function for html_buildlist()
 *
 */
function _html_list_index($item){
  global $ID;
  global $conf;
  $ret = '';
  $base = ':'.$item['id'];
  $base = substr($base,strrpos($base,':')+1);
  if($item['type']=='d'){
    if(@file_exists(wikiFN($item['id'].':'.$conf['start']))) {
      $ret .= '<a href="'.wl($item['id'].':'.$conf['start']).'" class="idx_dir">';
      $ret .= $base;
      $ret .= '</a>';
    } else {
      $ret .= '<a href="'.wl($ID,'idx='.$item['id']).'" class="idx_dir">';
      $ret .= $base;
      $ret .= '</a>';
    }
  } else {
# Do not show start page in menu    
    $match = '/' . $conf['start'] . '$/';
    if(!preg_match($match, $item['id'])) {
      $ret .= html_wikilink(':'.$item['id']);
	 }
  }
  return $ret;
}

# dokucms modified version of pageinfo 
function dokucms_pageinfo(){
  global $conf;
  global $lang;
  global $INFO;
  global $REV;
  global $ID;
  
  // return if we are not allowed to view the page
  if (!auth_quickaclcheck($ID)) { return; }
  
  // prepare date and path
  $date = strftime($conf['dformat'],$INFO['lastmod']);

  // print it
  if($INFO['exists']){
    print $lang['lastmod'];
    print ': ';
    print $date;
    if($_SERVER['REMOTE_USER']){
      if($INFO['editor']){
        print ' '.$lang['by'].' ';
        print $INFO['editor'];
      }else{
        print ' ('.$lang['external_edit'].')';
      }
      if($INFO['locked']){
        print ' &middot; ';
        print $lang['lockedby'];
        print ': ';
        print $INFO['locked'];
      }
    }
    return true;
  }
  return false;
}

//Setup vim: ts=4 sw=4:
?>
