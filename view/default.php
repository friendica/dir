<!DOCTYPE html >
<html>
<head>
  <title><?php echo isset($page['title']) ? $page['title'] : '' ?></title>
  <?php echo isset($page['htmlhead']) ? $page['htmlhead'] : '' ?>
</head>
<body>
	<header><?php echo isset($page['header']) ? $page['header'] : '' ?></header>
	<nav><?php echo isset($page['nav']) ? $page['nav'] : '' ?></nav>
	<aside><?php echo isset($page['aside']) ? $page['aside'] : '' ?></aside>
	<section><?php echo isset($page['content']) ? $page['content'] : '' ?></section>
	<footer><?php echo isset($page['footer']) ? $page['footer'] : '' ?></footer>
</body>
</html>

