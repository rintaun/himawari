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
			<?php if (isset($this->installerror)): ?>
				<section class="box" style="width: 770px">
					<hgroup><h2>Error</h2></hgroup>
					<?php $this->markdown($this->installerror); ?>
				</section>
			<?php endif; ?>
			<section class="box" style="width: 770px">
				<hgroup><h1>Installation</h1></hgroup>
				<form action="install.php" method="post">
					<section>
						<hgroup><h2>Manager Account</h2></hgroup>
						<details>
							<p>Himawari has only one user account: the <strong>manager account</strong>. This is the username
							and password you'll use to manage the songs on your rotation. Make sure you choose a secure
							password! Dictionary words are your birthday are very insecure passwords, and could lead to your
							page being hijacked. A password of at least 8 characters with at least a few numbers and maybe
							some symbols is usually best.</p>
						</details>
						<table>
							<tr>
								<td><label for="manageruser">Username</label></td>
								<td><input placeholder="manager" value="<?php echo $this->manageruser; ?>" name="manageruser" id="manageruser" /></td>
							</tr>
							<tr>
								<td><label for="managerpass">Password</label></td>
								<td><input type="password" value="<?php echo $this->managerpass; ?>" name="managerpass" id="managerpass" /></td>
							</tr>
							<tr>
								<td><label for="managerpassrep">Password (again)</label></td>
								<td><input type="password" value="<?php echo $this->managerpassrep; ?>" name="managerpassrep" id="managerpassrep" /></td>
							</tr>
						</table>
					</section>
					<section>
						<hgroup><h2>Content</h2></hgroup>
						<details>
							<p>All of the text on Himawari is customizable. Two main sections, the Introduction and About Me
							sections, will probably stay pretty much the same. You can customize these now, or leave them
							blank. They won't show up unless you write something.</p>
						</details>
						<table>
							<tr>
								<td><label for="sitename">Site Name</label></td>
								<td><input placeholder="Himawari" value="<?php echo $this->sitename; ?>" name="sitename" id="sitename" /></td>
							</tr>
							<tr>
								<td><label for="intro">Introduction</label></td>
								<td><textarea rows="10" cols="50" name="intro" id="intro"><?php echo $this->intro; ?></textarea></td>
							</tr>
							<tr>
								<td><label for="aboutme">About Me</label></td>
								<td><textarea rows="10" cols="50" name="aboutme" id="aboutme"><?php echo $this->aboutme; ?></textarea></td>
							</tr>
						</table>	
					</section>

					<br />

					<input type="hidden" name="action" value="execute" />
					<input type="submit" value="Install" />
				</form>
			</section>

			<footer>
				<small>
					powered by <a href="http://himawari.projectxero.net/">himawari</a>
				</small>
			</footer>
		</div>
	</body>
</html>
