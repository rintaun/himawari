<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $this->eprint($this->title); ?></title>

		<meta charset="utf-8" />
		<link rel="stylesheet" href="<?php echo $this->tpldir?>style.css" />

		<!-- <script type="text/javascript" src="../lib/ -->
		<script type="text/javascript" src="../lib/swfobject.js"></script>
		<script type="text/javascript" src="../lib/audio-player/audio-player-uncompressed.js"></script>
		<script type="text/javascript">AudioPlayer.setup("../lib/audio-player/player.swf",{width:"290",animation:"yes",encode:"yes",initialvolume:"60",remaining:"no",noinfo:"no",buffer:"5",checkpolicy:"no",rtl:"no",bg:"dddddd",text:"666666",leftbg:"eeeeee",lefticon:"666666",volslider:"666666",voltrack:"FFFFFF",rightbg:"cccccc",rightbghover:"999999",righticon:"666666",righticonhover:"ffffff",track:"FFFFFF",loader:"666666",border:"666666",tracker:"DDDDDD",skip:"666666",pagebg:"FFFFFF",transparentpagebg:"yes"});</script> 
		<!--[if lt IE 9]>
		<script src="lib/html5.js"></script>
		<![endif]-->
		<script type="text/javascript" src="../lib/jquery/jquery-1.6.2.min.js"></script>
		<script type="text/javascript" src="../lib/jquery/jquery-ui-1.8.14.custom.min.js"></script>
		<script type="text/javascript" src="../lib/jquery/jquery.fileupload.js"></script>
		
		<script type="text/javascript">
			$().ready(function(){
				setInterval("checkAnchor()", 300);
			});
			var currentAnchor = "a";

			var originals = {
				introtitle: '',
				introtext: '',
				abouttitle: '',
				abouttext: ''
			};

			function disableButton(button){
				$(button).parent().bind('click', false);
				$(button).parent().css('cursor', 'default');
			}
			function enableButton(button){
				$(button).parent().unbind('click', false);
				$(button).parent().css('cursor', 'pointer');
			}
			
			function checkAnchor(){
				if(currentAnchor == document.location.hash) return;
				currentAnchor = document.location.hash;
				switch (currentAnchor)
				{
					case '#editintro':
						$('#intro').hide();
						$('#introedit').show();
						originals.introtitle=$('#introtitleedit').attr("value");
						originals.introtext=$('#introtextedit').attr("value");
						break;
					case '#editintro-accept':
						$('#introacceptbutton').attr("src","<?php echo $this->tpldir?>/adm/img/loading.gif");
						disableButton('#introacceptbutton');
						$.ajax({
							url: '/adm/ajax.php',
							dataType: 'json',
							data: {
								action: 'editintro',
								introtitle: $('#introtitleedit').attr("value"),
								introtext: $('#introtextedit').attr("value")
							},
							success: function(intro){
								$('#introtitle').text(intro.title);
								$('#introtext').html(intro.text);
								$('#introedit').hide();
								$('#intro').show();
								$('#introacceptbutton').attr("src","<?php echo $this->tpldir?>/adm/img/accept.png");
								enableButton('#introacceptbutton');
							},
							error: function(){
								alert("Edit failed!");
								$('#introacceptbutton').attr("src","<?php echo $this->tpldir?>/adm/img/accept.png");
								enableButton('#introacceptbutton');
								window.location.hash="editintro";
							}
						});
						window.location.hash="";
						break;
					case '#editintro-reject':
						$('#introedit').hide();
						$('#intro').show();
						$('#introtitleedit').attr("value",originals.introtitle);
						$('#introtextedit').attr("value",originals.introtext);
						window.location.hash="";
						break;

					case '#editabout':
						$('#about').hide();
						$('#aboutedit').show();
						originals.abouttitle=$('#abouttitleedit').attr("value");
						originals.abouttext=$('#abouttextedit').attr("value");
						break;
					case '#editabout-accept':
						$('#aboutacceptbutton').attr("src","<?php echo $this->tpldir?>/adm/img/loading.gif");
						disableButton('#aboutacceptbutton');
						abouttitle = $('#abouttitleedit').attr("value");
						$.ajax({
							url: '/adm/ajax.php',
							dataType: 'json',
							data: {
								action: 'editabout',
								abouttitle: $('#abouttitleedit').attr("value"),
								abouttext: $('#abouttextedit').attr("value")
							},
							success: function(about){
								$('#abouttitle').text(about.title);
								$('#abouttext').html(about.text);
								$('#aboutedit').hide();
								$('#about').show();
								$('#aboutacceptbutton').attr("src","<?php echo $this->tpldir?>/adm/img/accept.png");
								enableButton('#aboutacceptbutton');
							},
							error: function(){
								alert("Edit failed!");
								$('#aboutacceptbutton').attr("src","<?php echo $this->tpldir?>/adm/img/accept.png");
								enableButton('#aboutacceptbutton');
								window.location.hash="editabout";
							}
						});
						window.location.hash="";
						break;
					case '#editabout-reject':
						$('#aboutedit').hide();
						$('#about').show();
						$('#abouttitleedit').attr("value",originals.abouttitle);
						$('#abouttextedit').attr("value",originals.abouttext);
						window.location.hash="";
						break;
						

					case '#uploadsong':
						break;
					case '#editsongs':
						break;
					//case '#removesong':
					//	break;
					
					case '#addlink':
						break;
					case '#editlinks':
						break;
					//case '#removelink':
					//	break;
					
					case '#':
					case '':
					case null:
						// I'm not sure I actually need this,
						// but for the moment anyway,
						// I'll leave it here to be safe.
						break; 
				}
			}
		</script>
	</head>
	<body style="background-image: url(<?php echo $this->tpldir?>img/bg.png);">
		<div id="preload"><img src="<?php echo $this->tpldir?>/adm/img/loading.gif" width="30" height="30" alt="Loading..." /></div>
		<header>
			<img src="<?php echo $this->tpldir?>img/logo.png" alt="<?php echo $this->eprint($this->title); ?>" />
		</header>	
		<nav id="sidebar">
			<ul>
				<li>
					<a href="#addlink">Add a Link <img src="<?php echo $this->tpldir?>/adm/img/add.png" width="16" height="16" alt="Add a Link" class="inlineicon" /></a>
					<br />
					<a href="#editlinks">Edit Links <img src="<?php echo $this->tpldir?>/adm/img/edit.png" width="16" height="16" alt="Edit Links" class="inlineicon" /></a>
				</li>
				<?php foreach ($this->links AS $entry): ?>
				<li>
					<a href="<?php echo $this->eprint($entry['url']); ?>" title="<?php echo $this->eprint($entry['alt']); ?>"><?php echo $this->eprint($entry['name']); ?></a>
				</li>
				<?php endforeach; ?>
			</ul>			
		</nav>

		<div id="content">
			<section id="intro" class="box">
				<hgroup>
					<h1><span id="introtitle"><?php echo $this->eprint($this->config['lang_intro']); ?></span><a href="#editintro"><img src="<?php echo $this->tpldir?>/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a></h1>
				</hgroup>
				<div id="introtext">
					<?php echo $this->markdown($this->introduction); ?>
				</div>
			</section>
			<section id="introedit" class="box hidden">
				<hgroup>
					<h1><input type="text" name="introtitle" id="introtitleedit" value="<?php echo $this->config['lang_intro']; ?>" /></h1>
				</hgroup>
				<textarea name="introtext" id="introtextedit" rows="11"><?php echo trim($this->introduction); ?></textarea>
				<a href="#editintro-reject"><img src="<?php echo $this->tpldir?>/adm/img/reject.png" width="30" height="30" alt="Reject Changes" class="icon" /></a>
				<a href="#editintro-accept"><img src="<?php echo $this->tpldir?>/adm/img/accept.png" id="introacceptbutton" width="30" height="30" alt="Accept Changes" class="icon" /></a>
			</section>
	
			<br />
	
			<section id="songs" class="box">
				<hgroup>
					<a href="#upload">
						<img src="<?php echo $this->tpldir?>/adm/img/add.png" width="30" height="30" alt="Upload a Song" class="icon" />
					</a>
					
					<h1><?php echo $this->eprint($this->config['lang_songs']); ?><a href="#editsongs"><img src="<?php echo $this->tpldir?>/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a></h1>
				</hgroup>
				<?php foreach ($this->songlist AS $num => $entry): ?>
					<h2><?php echo $this->eprint($entry['artist']); ?> - <?php echo $this->eprint($entry['title']); ?></h2>
					<?php echo $this->markdown($entry['descr']); ?>
					<p class="audioplayer_container"><span class="audioplayer" id="audioplayer_<?php echo $num; ?>">Audio clip: Adobe Flash Player (version 9 or above) is required to play this audio clip. Download the latest version <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" title="Download Adobe Flash Player">here</a>. You also need to have JavaScript enabled in your browser.</span></p>
				<?php endforeach; ?>
			</section>
	
			<br />
	
			<section id="about" class="box">
				<hgroup>
					<h1><span id="abouttitle"><?php $this->eprint($this->config['lang_about']); ?></span><a href="#editabout"><img src="<?php echo $this->tpldir?>/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a></h1>
				</hgroup>
				<div id="abouttext">
					<?php echo $this->markdown($this->about); ?>
				</div>
			</section>
			<section id="aboutedit" class="box hidden">
				<hgroup>
					<h1><input type="text" name="abouttitle" id="abouttitleedit" value="<?php echo $this->config['lang_about']; ?>" /></h1>
				</hgroup>
				<textarea name="abouttext" id="abouttextedit" rows="11"><?php echo trim($this->about); ?></textarea>
				<a href="#editabout-reject"><img src="<?php echo $this->tpldir?>/adm/img/reject.png" width="30" height="30" alt="Reject Changes" class="icon" /></a>
				<a href="#editabout-accept"><img src="<?php echo $this->tpldir?>/adm/img/accept.png" id="aboutacceptbutton" width="30" height="30" alt="Accept Changes" class="icon" /></a>
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
