<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $this->eprint($this->title); ?></title>
		
		<meta charset="utf-8" />
		<link rel="stylesheet" href="../style/style.css" />
				
		<script type="text/javascript" src="../lib/swfobject.js"></script>
		<script type="text/javascript" src="../lib/audio-player/audio-player-uncompressed.js"></script>
		<script type="text/javascript">AudioPlayer.setup("../lib/audio-player/player.swf",{width:"290",animation:"yes",encode:"yes",initialvolume:"60",remaining:"no",noinfo:"no",buffer:"5",checkpolicy:"no",rtl:"no",bg:"dddddd",text:"666666",leftbg:"eeeeee",lefticon:"666666",volslider:"666666",voltrack:"FFFFFF",rightbg:"cccccc",rightbghover:"999999",righticon:"666666",righticonhover:"ffffff",track:"FFFFFF",loader:"666666",border:"666666",tracker:"DDDDDD",skip:"666666",pagebg:"FFFFFF",transparentpagebg:"yes"});</script>
		 
		<!--[if lt IE 9]><script src="../lib/html5.js"></script><![endif]-->
		
		<script type="text/javascript" src="../lib/jquery/jquery-1.6.2.min.js"></script>
		<script type="text/javascript" src="../lib/jquery/jquery-ui-1.8.14.custom.min.js"></script>
		<script type="text/javascript" src="../lib/jquery/jquery.fileupload.js"></script>
		<script type="text/javascript" src="../lib/jquery/jquery.iframe-transport.js"></script>
		
		<script type="text/javascript" src="../style/adm/js/admin.js"></script>
	</head>
	<body>
		<div id="container">
			<header id="logo"><img src="../style/img/logo.png" alt="<?php echo $this->eprint($this->title); ?>" /></header>
			
			<nav id="sidebar">
				<h1>Links</h1>
				<h2 id="addlink"><a href="#addlink">Add a Link <img src="../style/adm/img/add.png" alt="Add a Link" width="16" height="16" title="Add a Link" class="inlineicon" /></a></h2>
				<?php if (!empty($this->links)): ?>
					<?php foreach ($this->links AS $entry): ?>
						<h2 id="link<?php echo $this->eprint($entry['id']); ?>">
							<span style="float:left"><a href="#editlink:<?php $this->eprint($entry['id']); ?>"><img src="../style/adm/img/edit.png" alt="Edit Link" width="16" height="16" title="Edit Link" class="inlineicon" id="linkedit<?php echo $this->eprint($entry['id']); ?>" /></a><a href="#removelink:<?php $this->eprint($entry['id']); ?>"><img src="../style/adm/img/remove.png" alt="Remove Link" width="16" height="16" title="Remove Link" class="inlineicon" id="linkremove<?php echo $this->eprint($entry['id']); ?>" /></a></span>
							<a href="<?php echo $this->eprint($entry['url']); ?>" title="<?php echo $this->eprint($entry['title']); ?>" class="link"><?php echo $this->eprint($entry['name']); ?></a>
						</h2>
					<?php endforeach; ?>
				<?php endif; ?>
			</nav>
			
			<div id="content">
				<section id="intro" class="box">
					<hgroup>
						<h1><span id="introtitle"><?php echo $this->eprint($this->config['lang_intro']); ?></span><a href="#editintro"><img src="../style/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a></h1>
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
					<a href="#editintro-reject"><img src="../style/adm/img/reject.png" width="30" height="30" alt="Reject Changes" class="icon" /></a>
					<a href="#editintro-accept"><img src="../style/adm/img/accept.png" id="introacceptbutton" width="30" height="30" alt="Accept Changes" class="icon" /></a>
				</section>
				
				<section id="songs" class="box">
					<hgroup>
						<h1>
							<form action="upload.php" method="POST" enctype="multipart/form-data" style="margin:0;padding:0;display:inline;">
								<label>
									<img src="../style/adm/img/add.png" width="30" height="30" alt="Upload a Song" class="icon" style="cursor:pointer" />
									<input type="hidden" name="MAX_FILE_SIZE" value="67108864" />
					                <input type="file" name="files[]" multiple style="visibility:hidden;width:0;height:0;margin:0;padding:0;">
					            </label> 
							</form>
							<?php echo $this->eprint($this->config['lang_songs']); ?><a href="#editsongs"><img src="../style/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a>
						</h1>
						<h6>
							<?php if (!isset($_GET['chunk']) && !isset($_GET['nochunk'])): ?>
								<strong>Max Filesize:</strong>
								<?php echo round(($this->maxUpload / 1048576), 2); ?>mb
								::
								
								<?php if ($this->maxUpload < 8388608): ?>
									
									Warning: Max upload size is less than 8mb.
									Chunked file uploads have been automatically enabled.
									This will break uploads in Internet Explorer (and possibly other browsers).
									<a href="?nochunk" title="Disable chunked uploads">Click here to disable.</a>
								<?php else: ?>
									If you need to upload larger files, <a href="?chunk" title="Enable chunked uploads">enable chunked uploads</a>.
								<?php endif; ?>
							<?php elseif (isset($_GET['chunk'])): ?>
								<strong>Max Filesize:</strong>
								<?php echo round(($this->maxUpload / 1048576), 2); ?>mb
 								:: 
 								Chunked uploads are enabled.
 								This will break uploads in Internet Explorer (and possibly other browsers).
								(<a href="?nochunk" title="Disable chunked uploads">Click here to disable chunked uploads..</a>)
							<?php elseif (isset($_GET['nochunk'])): ?>
								<strong>Max Filesize:</strong>
								<?php echo round(($this->maxUpload / 1048576), 2); ?>mb
								
								<?php if ($this->maxUpload < 8388608): ?>
									This is less than 8mb, which may be too low.
								<?php endif; ?>
								:: 
								If you need to upload larger files, <a href="?chunk" title="Enable chunked uploads">enable chunked uploads</a>.
							<?php endif;?>
							
							
						</h6>
					</hgroup>
					<?php foreach ($this->songlist AS $num => $entry): ?>
						<h2><?php echo $this->eprint($entry['artist']); ?> - <?php echo $this->eprint($entry['title']); ?></h2>
						<?php echo $this->markdown($entry['descr']); ?>
						<p class="audioplayer_container"><span class="audioplayer" id="audioplayer_<?php echo $num; ?>">Audio clip: Adobe Flash Player (version 9 or above) is required to play this audio clip. Download the latest version <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" title="Download Adobe Flash Player">here</a>. You also need to have JavaScript enabled in your browser.</span></p>
					<?php endforeach; ?>
				</section>
				
				<section id="about" class="box">
					<hgroup>
						<h1><span id="abouttitle"><?php $this->eprint($this->config['lang_about']); ?></span><a href="#editabout"><img src="../style/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a></h1>
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
					<a href="#editabout-reject"><img src="../style/adm/img/reject.png" width="30" height="30" alt="Reject Changes" class="icon" /></a>
					<a href="#editabout-accept"><img src="../style/adm/img/accept.png" id="aboutacceptbutton" width="30" height="30" alt="Accept Changes" class="icon" /></a>
				</section>
			</div>
			
			<footer id="footer">
				<small>
					<a href="../">main</a> :: powered by <a href="http://himawari.projectxero.net/">himawari</a>
				</small>
			</footer>
		</div>
		
		<script type="text/javascript">
		$(function () {
			var setColor = function(p){
			    var red = p<50 ? 255 : Math.round(256 - (p-50)*5.12);
			    var green = p>50 ? 255 : Math.round((p)*5.12);
			    return "rgb(" + red + "," + green + ",0)";
			}

			// Initialize the jQuery File Upload widget:
			$('#songs').fileupload({
				dropZone: $('body'),
				sequentialUploads: true,
				autoUpload: false,
				url: 'upload.php',
				<?php if ($this->maxUpload < 8388608): ?>
				maxChunkSize: <?php echo $this->maxUpload; ?>,
				multipart: false
				<?php endif; ?>
			})
			.bind('fileuploadadd', function(e, data){
				$.each(data.files, function (index, file) {
					$('#songs').append('<div><h2 style="margin-bottom:2px;">'+file.name+'</h2><div class="progress" style="padding:0;width:100%;height:10px;border:1px solid #000"><span style="width:0%;height:100%;display:inline-block;margin:0;position:relative;top:-5px;" id="blah"></span></div><div class="edit"><input type="text" placeholder="Artist" name="newsongartist'+index+'" id="newsongartist'+index+'" class="newsong"/> - <input type="text" placeholder="Title" name="newsongtitle'+index+'" id="newsongtitle'+index+'" class="newsong"/><br /><textarea name="newsongdesc'+index+'" id="newsongdesc'+index+'" placeholder="Description" rows="7"></textarea><br/><span style="width:100%;text-align:right;display:inline-block"><a href="#editnewsong-accept"><img src="../style/adm/img/accept.png" width="30" height="30"/></a><a href="#editnewsong-reject"><img src="../style/adm/img/reject.png" width="30" height="30"/></a></span></div></div>');
					file.div = $('#songs div:last-child');
					file.progressbar = $('#songs div:last-child div.progress'); 
					file.progress = $('#songs div:last-child div.progress span')
					file.editpart = $('#songs div:last-child div.edit');
					file.head = $('#songs div:last-child h2')
					file.editpart.hide();
				});
			})
			.bind('fileuploadfail', function(e, data){
				data.files[0].editpart.effect("highlight", {color: "#F00", mode: "hide"}, 500);
				data.files[0].head.append(' - <span style="color:#F00;">Filetype not supported</span>');
				//data.files[0].editpart.fadeOut(function(){$(this).remove();});
			})
			.bind('fileuploadsend', function(e, data){
				//alert(data.files[0].name);
				//if (!data.files[0].name.match(new RegExp(/\.(mp3|mp4|m4a|ogg|wav)$/i))) return false;
				return true;
			})
			.bind('fileuploadprogress', function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
			    data.files[0].progress.css("width", progress+"%").css("background-color", setColor(progress));
			})
			.bind('fileuploaddone', function(e, data){
				var result = $.parseJSON(data.result)[0];
				if (result.error) {
					data.files[0].head.effect("highlight", {color: "#F00"}, 500);
					data.files[0].head.append(' - <span style="color:#F00;">Upload failed</span>');
				}
				else {
					data.files[0].head.effect("highlight", {color: "#0F0"}, 500);
					data.files[0].head.append(' - <span style="color:#0F0;">Upload successful</span>');
					data.files[0].editpart.slideDown();
				}
				data.files[0].progressbar.slideUp(function(){$(this).remove();});
			});
		});
		</script>
		
		<?php if (!empty($this->songlist)): ?>
			<script type="text/javascript">
				<?php foreach ($this->songlist AS $num => $entry): ?>
					AudioPlayer.embed("audioplayer_<?php echo $num; ?>", {soundFile:"<?php echo $entry['url']; ?>"});
				<?php endforeach; ?>
			</script>
		<?php endif; ?>
	</body>
</html>
