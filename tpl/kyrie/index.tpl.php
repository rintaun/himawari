<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $this->eprint($this->title); ?></title>
		<script type="text/javascript" src="lib/swfobject.js"></script>
		<script type="text/javascript" src="lib/audio-player/audio-player-uncompressed.js"></script>
		<script type="text/javascript">AudioPlayer.setup("lib/audio-player/player.swf",{width:"290",animation:"yes",encode:"yes",initialvolume:"60",remaining:"no",noinfo:"no",buffer:"5",checkpolicy:"no",rtl:"no",bg:"dddddd",text:"666666",leftbg:"eeeeee",lefticon:"666666",volslider:"666666",voltrack:"FFFFFF",rightbg:"cccccc",rightbghover:"999999",righticon:"666666",righticonhover:"ffffff",track:"FFFFFF",loader:"666666",border:"666666",tracker:"DDDDDD",skip:"666666",pagebg:"FFFFFF",transparentpagebg:"yes"});</script> 
		<style type="text/css">
			body {
				background-image: url(<?php echo $this->tpldir?>img/bg.png);
			}
			#main {
				margin-left: -8px;
				margin-top: -8px;
				width: 800px;
				background-image: url(<?php echo $this->tpldir?>img/bg.png);
			}
			#sidebar {
				text-align: right;
				padding-top: 10px;
				padding-left: 10px;
				width: 180px;
				display: inline-block;
				vertical-align: top;
				text-transform: uppercase;
				letter-spacing: 0.2em;
				font-size: 0.9em;
			}
			#sidebar ul {
				list-style-type:none;
				margin:0;
				padding:0;
			}
			#sidebar ul a {
				color: #000000;
				text-decoration: none;
				display: block;
				margin-bottom: 30px;
				opacity: 0.5;
			}
			#sidebar ul a:hover {
				opacity: 0.9;
				text-shadow: #ACACAC 2px 2px 0;
			}
			#content {
				width: 600px;
				display: inline-block;
				vertical-align: top;
			}
			#content p {
				margin-top: 0px;
			}
			#introduction {
				background-color: #F7F4D3;
				padding: 10px;
				padding-bottom: 0;
				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
			}
			span.title {
				color: #F21947;
				font-size: 1.5em;
				font-family: sans-serif;
				opacity: 0.8;
				letter-spacing: 0.5em;
				text-shadow: #F21947 0px 0px 2px;
			}
			#mawari {
				margin-top: 25px;
				margin-bottom: 15px; 
				background-color: #F7F4D3;
				padding: 10px;
				padding-bottom: 0px;
				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
			}
		</style>
	</head>
	<body>
		<div id="main">
			<img src="<?php echo $this->tpldir?>img/kyrie.png" alt="" />

			<div id="sidebar">
				<ul>
					<li><a href="#">a thing</a></li>
					<li><a href="#">another thing</a></li>
					<li><a href="#">yet another thing</a></li>
					<li><a href="#">a thing with a much longer name</a></li>
				</ul>
			</div>
			<div id="content">
				<div id="introduction"><span class="title">INTRODUCTION</span>
				<?php echo $this->tprint($this->introduction); ?></div>
				<div id="mawari"><span class="title">SONGS</span>
		<?php foreach ($this->songlist AS $num => $entry): ?>
			<p><b><?php echo $this->eprint($entry['title']); ?></b></p>
			<?php echo $this->tprint($entry['description']); ?>

			<p class="audioplayer_container"><span class="audioplayer" id="audioplayer_<?php echo $num; ?>">Audio clip: Adobe Flash Player (version 9 or 
above) is required to play this audio clip. Download the latest version <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" 
title="Download Adobe Flash Player">here</a>. You also need to have JavaScript enabled in your browser.</span></p>

		<?php endforeach; ?>
				</div>
			</div>
		</div>


		<script type="text/javascript">
			<?php foreach ($this->songlist AS $num => $entry): ?>
				AudioPlayer.embed("audioplayer_<?php echo $num; ?>", {soundFile:"<?php echo $entry['url']; ?>"});
			<?php endforeach; ?>
		</script>
	</body>
</html>
