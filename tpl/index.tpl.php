<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title><?php echo $this->eprint($this->title); ?></title>
	</head>
	<body>
		<p><?php echo $this->eprint($this->introduction); ?></p>

		<?php foreach ($this->songlist AS $entry): ?>
			<p><b><?php echo $this->eprint($entry['title']); ?></b><br />
			<?php echo $this->eprint($entry['description']); ?><br />
			<br />
			MUSIC PLAYER HERE</p>
		<?php endforeach; ?>

	</body>
</html>
