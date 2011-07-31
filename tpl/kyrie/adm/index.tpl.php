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
		
		<script id="template-upload" type="text/x-jquery-tmpl">
    <tr class="template-upload{{if error}} ui-state-error{{/if}}">
        <td class="preview"></td>
        <td class="name">${name}</td>
        <td class="size">${sizef}</td>
        {{if error}}
            <td class="error" colspan="2">Error:
                {{if error === 'maxFileSize'}}File is too big
                {{else error === 'minFileSize'}}File is too small
                {{else error === 'acceptFileTypes'}}Filetype not allowed
                {{else error === 'maxNumberOfFiles'}}Max number of files exceeded
                {{else}}${error}
                {{/if}}
            </td>
        {{else}}
            <td class="progress"><div></div></td>
            <td class="start"><button>Start</button></td>
        {{/if}}
        <td class="cancel"><button>Cancel</button></td>
    </tr>
		</script>
		<script id="template-download" type="text/x-jquery-tmpl">
    <tr class="template-download{{if error}} ui-state-error{{/if}}">
        {{if error}}
            <td></td>
            <td class="name">${name}</td>
            <td class="size">${sizef}</td>
            <td class="error" colspan="2">Error:
                {{if error === 1}}File exceeds upload_max_filesize (php.ini directive)
                {{else error === 2}}File exceeds MAX_FILE_SIZE (HTML form directive)
                {{else error === 3}}File was only partially uploaded
                {{else error === 4}}No File was uploaded
                {{else error === 5}}Missing a temporary folder
                {{else error === 6}}Failed to write file to disk
                {{else error === 7}}File upload stopped by extension
                {{else error === 'maxFileSize'}}File is too big
                {{else error === 'minFileSize'}}File is too small
                {{else error === 'acceptFileTypes'}}Filetype not allowed
                {{else error === 'maxNumberOfFiles'}}Max number of files exceeded
                {{else error === 'uploadedBytes'}}Uploaded bytes exceed file size
                {{else error === 'emptyResult'}}Empty file upload result
                {{else}}${error}
                {{/if}}
            </td>
        {{else}}
            <td class="preview">
                {{if thumbnail_url}}
                    <a href="${url}" target="_blank"><img src="${thumbnail_url}"></a>
                {{/if}}
            </td>
            <td class="name">
                <a href="${url}"{{if thumbnail_url}} target="_blank"{{/if}}>${name}</a>
            </td>
            <td class="size">${sizef}</td>
            <td colspan="2"></td>
        {{/if}}
        <td class="delete">
            <button data-type="${delete_type}" data-url="${delete_url}">Delete</button>
        </td>
    </tr>
		</script>
		<script src="//ajax.aspnetcdn.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>
		<script src="../lib/jquery/jquery.iframe-transport.js"></script>
		<script src="../lib/jquery/jquery.fileupload-ui.js"></script>
		
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
					                <input type="file" name="files[]" multiple style="visibility:hidden;width:0;height:0;">
					            </label>
					            <button type="submit" class="start">Start upload</button> 
							</form>
							<?php echo $this->eprint($this->config['lang_songs']); ?><a href="#editsongs"><img src="../style/adm/img/edit.png" width="20" height="20" alt="Edit this Section" class="inlineicon" /></a>
						</h1>
					</hgroup>
					<div class="fileupload-content">
						<table class="files"></table>
						<div class="fileupload-progressbar"></div>
					</div>
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
		
		<?php if (!empty($this->songlist)): ?>
			<script type="text/javascript">
				<?php foreach ($this->songlist AS $num => $entry): ?>
					AudioPlayer.embed("audioplayer_<?php echo $num; ?>", {soundFile:"<?php echo $entry['url']; ?>"});
				<?php endforeach; ?>
			</script>
		<?php endif; ?>
	</body>
</html>
