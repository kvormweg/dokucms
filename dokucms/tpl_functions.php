<?php
/**
 * DokuWiki Template DokuCMS Functions - adapted from arctic template
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author  Michael Klier <chi@chimeric.de>
 * @author Klaus Vormweg <klaus.vormweg@gmx.de>


 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

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

    if(!defined('DOKU_LF')) define('DOKU_LF',"\n");
    $pname = 'sidebar';

    $svID  = $ID;   // save current ID
//    $svREV = $REV;  // save current REV 

    switch($conf['tpl']['dokucms']['sidebar']) {

      case 'file':
        $ns_sb = _getNsSb($svID);
        if($ns_sb && auth_quickaclcheck($ns_sb) >= AUTH_READ) {
            print '<div class="sidebar_box">' . DOKU_LF;
            print p_sidebar_xhtml($ns_sb,$pos) . DOKU_LF;
            print '</div>' . DOKU_LF;
         } elseif(@file_exists(wikiFN($pname)) && auth_quickaclcheck($pname) >= AUTH_READ) {
            print '<div class="sidebar_box">' . DOKU_LF;
            print p_sidebar_xhtml($pname,$pos) . DOKU_LF;
            print '</div>' . DOKU_LF;
        }
        break;   
			default:
    			print '<div class="sidebar_box">' . DOKU_LF;
    			print '  ' . p_index_xhtml($svID,$pos) . DOKU_LF;
    			print '</div>' . DOKU_LF;
	 }	
    // restore ID and REV
    $ID  = $svID;
//    $REV = $svREV;
}

/**
 * searches for namespace sidebars
 */
function _getNsSb($id) {
    $pname = 'sidebar';
    $ns_sb = '';
    $path  = explode(':', $id);
    
    while(count($path) > 0) {
        $ns_sb = implode(':', $path).':'.$pname;
        if(@file_exists(wikiFN($ns_sb))) return $ns_sb;
        array_pop($path);
    }
    
    // nothing found
    return false;
}

/**
 * Removes the TOC of the sidebar pages and 
 * shows a edit button if the user has enough rights
 *
 */
function p_sidebar_xhtml($sb,$pos) {
  global $conf;
  $data = p_wiki_xhtml($sb,'',false);
  if(auth_quickaclcheck($sb) >= AUTH_EDIT and $conf['tpl']['dokucms']['sidebaredit']) {
    $data .= '<div class="secedit">'.html_btn('secedit',$sb,'',array('do'=>'edit','rev'=>'','post')).'</div>';
  }
  // strip TOC
  $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);
  // replace headline ids for XHTML compliance
  $data = preg_replace('/(<h.*?><a.*?id=")(.*?)(">.*?<\/a><\/h.*?>)/','\1sb_'.$pos.'_\2\3', $data);
  return ($data);
}

/**
 * Renders the Index
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
  $data2 = array();
  foreach($data as $item) {
    if($conf['tpl']['dokucms']['cleanindex']) {
      if($item['id'] == 'playground' or $item['id'] == 'wiki') {
        continue;
      }
    }
    if($item['id'] == 'sidebar' or $item['id'] == $conf['start']) {
      continue;
    }
    $data2[] = $item;
  }  
  $data = $data2;
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
    $ret .= html_wikilink(':'.$item['id']);
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
