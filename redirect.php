<?php
         header('Content-Type:text/html; charset=UTF-8');
	$url  = $_GET['url'];
	$blog = urldecode($_GET['blog']);
?>

<html>
<header>
<meta http-equiv="refresh" content="10; url=<?php echo $url; ?>">
</header>
<body style="margin:0">
<p style="font:12px Arial,Helvetica,sans-serif;color:#000;margin:99px 0;text-align:center;padding:50px;border:1px dotted #00F;background-color:#EEE">
<?php
         if ($blog == "") {
?>
Note: you click on an external link and leave our pages.<br>
<?php
         } else {
?>
Note: you click on an external link and leave the pages of <b><?php echo $blog; ?></b>.<br>
<br>
The next pages are not part of <b><?php echo $blog; ?></b>!<br>
<?php
         }
?>
<br>
We are not responsible for the content of this page. If you're not automatically redirected, please click <a href="<?php echo $url; ?>">here</a>.<br>
</p>
</body>
</html>