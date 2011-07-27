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
				<hgroup><h1>Login</h1></hgroup>
				<?php if ($this->error): ?>
				<h2 class="error">Invalid username or password.</h2>
				<?php endif; // $this->error ?>
				
				<form action="login.php" method="post">
					<table>
						<tr>
							<td><label for="username">Username: </label></td>
							<td><input type="text" name="username" id="username" placeholder="username" /></td>
						</tr>
						<tr>
							<td><label for="password">Password: </label></td>
							<td><input type="password" name="password" id="password" placeholder="password" /></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="hidden" name="action" value="execute" /><input type="submit" value="Login" /></td>
						</tr>
					</table>
					
					<br />
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
