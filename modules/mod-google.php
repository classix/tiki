<?php
// Google menu item for e107
// This menu item will display a search box for Google

if(isset($_POST['google'])){
echo "<script type=\"text/javascript\">
<!--
  window.open ('http://www.google.com/search?hl=en&ie=UTF-8&amp;oe=UTF-8&amp;q=".$_POST['sgoogle']."&amp;btnG=Google+Search','Google');
-->
</SCRIPT>";
$smarty->assign('ownurl',/*httpPrefix().*/$_SERVER["REQUEST_URI"]);
}
?>
