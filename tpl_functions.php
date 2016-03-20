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
function _tpl_sidebar() {
    global $lang;
    global $conf;
    global $ID;

    if(!defined('DOKU_LF')) define('DOKU_LF',"\n");
    $pname = 'sidebar';
    
    $tpl = $conf['template'];

    if($conf['tpl'][$tpl]['sidebar']== 'file')  {
        $ns_sb = _tpl_getNsSb($ID);
        if($ns_sb && auth_quickaclcheck($ns_sb) >= AUTH_READ) {
            echo '<div class="sidebar_box">', DOKU_LF;
            echo _tpl_sidebar_xhtml($ns_sb), DOKU_LF;
            echo '</div>', DOKU_LF;
         } elseif(@file_exists(wikiFN($pname)) && auth_quickaclcheck($pname) >= AUTH_READ) {
            echo '<div class="sidebar_box">', DOKU_LF;
            echo _tpl_sidebar_xhtml($pname), DOKU_LF;
            echo '</div>', DOKU_LF;
        } else {
            echo '<div class="sidebar_box">', DOKU_LF;
            echo '&nbsp;', DOKU_LF;
            echo '</div>', DOKU_LF;
			   }
   } else {
    			echo '<div class="sidebar_box">', DOKU_LF;
    			echo '  ', _tpl_index_xhtml($ID), DOKU_LF;
    			echo '</div>', DOKU_LF;
	 }	
}

/**
 * searches for namespace sidebars
 */
function _tpl_getNsSb($id) {
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
function _tpl_sidebar_xhtml($sb) {
  global $conf;
  $tpl = $conf['template'];
  $data = p_wiki_xhtml($sb,'',false);
  if(auth_quickaclcheck($sb) >= AUTH_EDIT and $conf['tpl'][$tpl]['sidebaredit']) {
    $data .= '<div class="secedit">'.html_btn('secedit',$sb,'',array('do'=>'edit','rev'=>'','post')).'</div>';
  }
  // strip TOC
  $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);
  // replace headline ids for XHTML compliance
  $data = preg_replace('/(<h.*?><a.*?id=")(.*?)(">.*?<\/a><\/h.*?>)/','\1sb_left_\2\3', $data);
  return ($data);
}

/**
 * Renders the Index
 *
 */
function _tpl_index_xhtml($ns) {
  require_once(DOKU_INC.'inc/search.php');
  global $conf;
  global $ID;
  $dir = $conf['datadir'];
  $tpl = $conf['template'];
  if(isset($conf['start'])) {
    $start = $conf['start'];
  } else {
    $start = 'start';
  }

  $ns  = cleanID($ns);
# fixme use appropriate function
  if(empty($ns)){
    $ns = dirname(str_replace(':','/',$ID));
    if($ns == '.') $ns ='';
  }
  $ns  = utf8_encodeFN(str_replace(':','/',$ns));

  $data = array();
  $data2 = array();
  search($data,$conf['datadir'],'search_index',array('ns' => $ns));
  $i = 0;
  $cleanindexlist = array();
  if($conf['tpl'][$tpl]['cleanindexlist']) {
   	$cleanindexlist = explode(',', $conf['tpl'][$tpl]['cleanindexlist']);
   	$i = 0;
   	foreach($cleanindexlist as $tmpitem) {
   		$cleanindexlist[$i] = trim($tmpitem);
   		$i++;
   	}
	}
  $i = 0;
	foreach($data as $item) {
    if($conf['tpl'][$tpl]['cleanindex']) {
      if($item['id'] == 'playground' or $item['id'] == 'wiki') {
        continue;
      }
      if(count($cleanindexlist)) {
      	if(strpos($item['id'], ':')) {
      		list($tmpitem) = explode(':',$item['id']);
      	} else {
      		$tmpitem = $item['id'];
      	}
	      if(in_array($tmpitem, $cleanindexlist)) {
	        continue;
	      }
			}
    }
    if($item['id'] == 'sidebar' or $item['id'] == $start 
       or preg_match('/:'.$start.'$/',$item['id'])
       or preg_match('/(\w+):\1$/',$item['id'])) {
      continue;
    }
    if($item['type'] == 'f' and array_key_exists($item['id'], $data2)) {
      $data2[$item['id']]['type'] = 'd';
      $data2[$item['id']]['ns'] = $item['id'];
      continue;
    } 
    $data2[$item['id']] = $item;
  }  
# echo index with empty items removed  
  echo html_buildlist($data2,'idx','_tpl_html_list_index','_tpl_html_li_index');
}

/**
 * Index item formatter
 *
 * User function for html_buildlist()
 *
 */
function _tpl_html_list_index($item){
  global $conf;
  $ret = '';
  if($item['type']=='d'){
    if(@file_exists(wikiFN($item['id'].':'.$conf['start']))) {
      $ret .= html_wikilink($item['id'].':'.$conf['start'], $item['title']);
    } elseif(@file_exists(wikiFN($item['id'].':'.$item['id']))) {
      $ret .= html_wikilink($item['id'].':'.$item['id'], $item['title']);
    } else {
      $ret .= html_wikilink($item['id'].':', $item['title']);
    }
  } else {
    $ret .= html_wikilink(':'.$item['id'], $item['title']);
  }
  return $ret;
}
/**
 * Index List item
 *
 * This user function is used in html_buildlist to build the
 * <li> tags for namespaces when displaying the page index
 * it gives different classes to opened or closed "folders"
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param array $item
 * @return string html
 */
function _tpl_html_li_index($item){
  global $INFO;

  $class = '';
  $id = '';

  if($item['type'] == "f"){
    // scroll to the current item
    return '<li class="level'.$item['level'].$class.'" '.$id.'>';
  } elseif($item['open']) {
    return '<li class="open">';
  } else {
    return '<li class="closed">';
  }
}


# dokucms modified version of pageinfo 
function _tpl_pageinfo(){
  global $conf;
  global $lang;
  global $INFO;
  global $ID;
  
  // return if we are not allowed to view the page
  if (!auth_quickaclcheck($ID)) { return; }
  
  // prepare date and path
  $date = dformat($INFO['lastmod']);

  // echo it
  if($INFO['exists']){
    echo $lang['lastmod'], ': ', $date;
    if($_SERVER['REMOTE_USER']) {
      if($INFO['editor']) {
        echo ' ', $lang['by'], ' ', $INFO['editor'];
      } else {
        echo ' (', $lang['external_edit'], ')';
      }
      if($INFO['locked']){
        echo ' &middot; ', $lang['lockedby'], ': ', $INFO['locked'];
      }
    }
    return true;
  }
  return false;
}
?>
