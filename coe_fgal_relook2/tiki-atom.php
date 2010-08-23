<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

$datenow = htmlspecialchars($tikilib->iso_8601(date("U")));

$url = $_SERVER["REQUEST_URI"];
$url = substr($url, 0, strpos($url."?", "?")); // strip all parameters from url
$urlarray = parse_url($url);

$pagename = substr($urlarray["path"], strrpos($urlarray["path"], '/') + 1);

$home = htmlspecialchars($tikilib->httpPrefix().str_replace($pagename, $prefs['tikiIndex'], $urlarray["path"]));
$img = htmlspecialchars($tikilib->httpPrefix().str_replace($pagename, "img/tiki.jpg", $urlarray["path"]));

$read = $tikilib->httpPrefix().str_replace($pagename, "$readrepl", $urlarray["path"]);

$url = htmlspecialchars($tikilib->httpPrefix().$url);
$title = preg_replace("/RSS/","Atom", $title);
$title = htmlspecialchars($title);
$desc = htmlspecialchars($desc);
$url = htmlspecialchars($url);
$css = htmlspecialchars($tikilib->httpPrefix().str_replace($pagename, "lib/rss/atom-style.css", $urlarray["path"]));

$atom_use_css = false; // default is: do not use css
if (isset($_REQUEST["css"])) {
	$atom_use_css = true;
}

// --- output starts here 
header("content-type: text/xml");

print '<?xml version="1.0" encoding="UTF-8" ?>';
print '<!--  ATOM generated by Tiki Wiki CMS Groupware (tiki.org) on '.$datenow.' -->';

if ($atom_use_css) {
	print '<?xml-stylesheet href="'.$css.'" type="text/css"?>'."\n";
}

if (!isset($output)) $output="";

if ($output == "")
{
	$output .= '<feed version="0.3" xmlns="http://purl.org/atom/ns#">';

  $output .= "<title>".$title."</title>";
	$output .= '<link rel="alternate" type="text/html" href="'.$url.'" />';
  $output .= "<modified>".$datenow."</modified>";
  $output .= "<generator>".$tikiId."</generator>";

  // LOOP collecting last changes to image galleries
  foreach ($changes["data"] as $chg) {

		$date = htmlspecialchars($tikilib->iso_8601($chg["$dateId"]));
  	$about = $read.$chg["$id"];
  	// blogs have posts, add those to the url:
  	if ($id == "blogId") { $about .= "&postId=".$chg["postId"]; }		
  	// forums have threads, add those to the url:
  	if ($id == "forumId") { $about .= "&comments_parentId=".$chg["threadId"]; }
    $about = htmlspecialchars($about);

    $title = $chg["$titleId"];
  	// titles for blogs are dates, so make them readable:
  	if ($titleId == "created") { $title = htmlspecialchars($tikilib->iso_8601($title)); }
    $title = htmlspecialchars($title);

    $description = htmlspecialchars($tikilib->parse_data($chg["$descId"]));

		$author = "unknown";
		if (array_key_exists("author",$chg)) $author = $chg["author"]; // for articles
		if (array_key_exists("user",$chg)) $author = $chg["user"]; // for wiki pages/blogs/galleries
		if (array_key_exists("username",$chg)) $author = $chg["username"]; // for forums

 		$output .= "<entry>";
		$output .= '<id>'.$about.'</id>';
    $output .= '<link rel="alternate" type="text/html" href="'.$about.'" />';

	  //  <link rel="service.post" type='application/x.atom+xml' href="/blog/1630.atom-new-comment" title="Add a comment."/>
	  //  <link rel="comments" type='application/x.atom+xml' title="Comments on Entry 1630" href="/blog/1630.atom" />
	  //  <link rel="service.edit" type='application/x.atom+xml" href="/blog/1630.atomapi" />

  	$output .= '  <title>'.$title.'</title>';
		$output .= "<author><name>".$author."</name></author>";
		$output .= "<issued>".$date."</issued>";
		$output .= "<modified>".$date."</modified>";
  
    $output .= '<content type="application/xhtml+xml" mode="xml">';
    $output .= '<div xmlns="http://www.w3.org/1999/xhtml">';
    $output .= $description;
    $output .= '</div>';
    $output .= '</content>';

  	$output .= '</entry>';
  }

 	$output .= "</feed>";
}
