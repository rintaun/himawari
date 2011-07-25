<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $this->eprint($this->title); ?></title>

		<meta charset="utf-8" />
		<link rel="stylesheet" href="<?php echo $this->tpldir?>style.css" />

		<script type="text/javascript" src="../lib/swfobject.js"></script>
		<script type="text/javascript" src="../lib/audio-player/audio-player-uncompressed.js"></script>
		<script type="text/javascript">AudioPlayer.setup("../lib/audio-player/player.swf",{width:"290",animation:"yes",encode:"yes",initialvolume:"60",remaining:"no",noinfo:"no",buffer:"5",checkpolicy:"no",rtl:"no",bg:"dddddd",text:"666666",leftbg:"eeeeee",lefticon:"666666",volslider:"666666",voltrack:"FFFFFF",rightbg:"cccccc",rightbghover:"999999",righticon:"666666",righticonhover:"ffffff",track:"FFFFFF",loader:"666666",border:"666666",tracker:"DDDDDD",skip:"666666",pagebg:"FFFFFF",transparentpagebg:"yes"});</script> 
		<!--[if lt IE 9]>
			<script src="lib/html5.js"></script>
		<![endif]-->
	</head>
	<body style="background-image: url(<?php echo $this->tpldir?>img/bg.png);">
		<header>
			<img src="<?php echo $this->tpldir?>img/logo.png" alt="<?php echo $this->eprint($this->title); ?>" />
		</header>	
		<nav id="sidebar">
			<ul>
				<li><a href="#">Upload a song</a></li>
			</ul>			
		</nav>

		<div id="content">
			<section class="box">
				<hgroup>
					<h1>Introduction</h1>
				</hgroup>
				<?php $this->markdown($this->introduction); ?>
			</section>
	
			<br />
	
			<section id="songs" class="box">
				<hgroup>
					<h1>Songs</h1>
				</hgroup>
				<?php foreach ($this->songlist AS $num => $entry): ?>
					<h2><?php echo $this->eprint($entry['artist']); ?> - <?php echo $this->eprint($entry['title']); ?></h2>
					<?php $this->markdown($entry['descr']); ?>
					<p class="audioplayer_container"><span class="audioplayer" id="audioplayer_<?php echo $num; ?>">Audio clip: Adobe Flash Player (version 9 or above) is required to play this audio clip. Download the latest version <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" title="Download Adobe Flash Player">here</a>. You also need to have JavaScript enabled in your browser.</span></p>
				<?php endforeach; ?>
			</section>
	
			<br />
	
			<section class="box">
				<hgroup>
					<h1>About Me</h1>
				</hgroup>
				<?php $this->markdown($this->about); ?>
			</section>
		</div>

		<footer>
			<small>
				powered by <a href="http://himawari.projectxero.net/">himawari</a>
			</small>
		</footer>

		<script type="text/javascript">
			<?php foreach ($this->songlist AS $num => $entry): ?>
				AudioPlayer.embed("audioplayer_<?php echo $num; ?>", {soundFile:"<?php echo $entry['url']; ?>"});
			<?php endforeach; ?>
		</script>
	</body>
</html>
