<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Himawari Installer</title>

		<meta charset="utf-8" />
		<link rel="stylesheet" href="<?php echo $this->tpldir?>style.css" />
		<!--[if lt IE 9]>
			<script src="../lib/html5.js"></script>
		<![endif]-->
	</head>
	<body style="background-image: url(<?php echo $this->tpldir?>img/bg.png);">
		<header>
			<img src="<?php echo $this->tpldir?>img/logo.png" alt="Himawari" />
		</header>	

                <div id="content">
			<section class="box" style="width: 770px">
				<hgroup><h1>Installation Completed!</h1></hgroup>
				<?php if (isset($this->configtext)): ?>
					Himawari is installed, but unfortunately, the main directory isn't writable!
					There's no real problem with that, but it means that I wasn't able to create
					the configuration file. Once you put the following text in ".config.php" in the
					main directory, everything will be good to go!
					<code>
<?php echo $this->configtext; ?>
					</code>
					After that, you can <a href="index.php">manage your rotation</a>.
				<?php else: ?>
					Himawari is up and ready to go! You can now <a href="index.php">manage your rotation</a>.
				<?php endif; ?>
			</section>

			<footer>
				<small>
					powered by <a href="http://himawari.projectxero.net/">himawari</a>
				</small>
			</footer>
		</div>
	</body>
</html>
