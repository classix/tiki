<?php
  require_once('tiki-setup.php');
  require_once('lib/tikilib.php');

  if($rss_forum != 'y') {
    die;
  }
  if(!isset($_REQUEST["forumId"])) {
    die;
  }

  if($tiki_p_admin_forum != 'y' && $tiki_p_forum_read != 'y') {
      $smarty->assign('msg',tra("You dont have permission to use this feature"));
      $smarty->display("styles/$style_base/error.tpl");
      die;
  }

  header("content-type: text/xml");
  $foo = parse_url($_SERVER["REQUEST_URI"]);
  $foo1=str_replace("tiki-forum_rss.php",$tikiIndex,$foo["path"]);
  $foo2=str_replace("tiki-forum_rss.php","img/tiki.jpg",$foo["path"]);
  $foo3=str_replace("tiki-forum_rss","tiki-view_forum_thread",$foo["path"]);
  $foo4=str_replace("tiki-forum_rss.php","lib/rss/rss-style.css",$foo["path"]);
  $home = httpPrefix().$foo1;
  $img = httpPrefix().$foo2;
  $read = httpPrefix().$foo3;
  $css = httpPrefix().$foo4;

  $now = date("U");
  $changes = $tikilib->list_forum_topics($_REQUEST["forumId"],0,$max_rss_forum,'commentDate_desc', '');
  $info = $tikilib->get_forum($_REQUEST["forumId"]);
  $forumname = $info["name"];
  $forumdesc = $info["description"];

  $title = "Tiki RSS feed for forum:";
  $desc = "Last topics in forum:";

  print '<?xml version="1.0" encoding="UTF-8"?>'."\n";
  print '<?xml-stylesheet href="'.$css.'" type="text/css"?>'."\n";
?>
<rdf:RDF xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
<channel rdf:about="<?php echo $home; ?> <?php echo htmlspecialchars($forumname); ?>">
  <title><?php echo $title; ?></title>
  <link><?php echo $home; ?></link>
  <description>
    <?php echo $desc; ?> <?php echo htmlspecialchars($forumdesc); ?>
  </description>
  <image rdf:about="<?php echo $img; ?>">
    <title><?php echo $title; ?> <?php echo htmlspecialchars($forumname); ?></title>
    <link><?php echo $home?></link>
  </image>
  
    <items>
    <rdf:Seq>
      <?php
        // LOOP collecting last topics in forum (index)
        foreach($changes["data"] as $chg) {
          print('        <rdf:li resource="'.$read.'?forumId='.$chg["forumId"].'&amp;comments_parentId='.$chg["threadId"].'" />'."\n");
        }        
      ?>
    </rdf:Seq>  
  </items>
</channel>
  
<?php
  // LOOP collecting last topics in forum
  foreach($changes["data"] as $chg) {
    print('<item rdf:about="'.$read.'?forumId='.$chg["forumId"].'&amp;comments_parentId='.$chg["threadId"].'">'."\n");
    print('<title>'.htmlspecialchars($chg["title"]).': '.
    $tikilib->date_format($tikilib->get_short_datetime_format(),$chg["commentDate"]).'</title>'."\n");
    print('<link>'.$read.'?forumId='.$chg["forumId"].'&amp;comments_parentId='.$chg["threadId"].'</link>'."\n");
    $data = $tikilib->date_format($tikilib->get_short_datetime_format(),$chg["commentDate"]);
    print('<description>'.htmlspecialchars($chg["data"]).'</description>'."\n");
    print('</item>'."\n\n");
  }        
?>

</rdf:RDF>