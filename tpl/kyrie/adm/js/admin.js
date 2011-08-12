$('<img>').attr("src", "../style/adm/img/loading.gif");

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

jQuery.exists = function(selector) {return ($(selector).length > 0);}

function checkAnchor(){
	if(currentAnchor == document.location.hash) return;
	currentAnchor = document.location.hash;
	
	var anchor, id;
	if (anchor = currentAnchor.split(':'))
		id = (anchor[1]) ? anchor[1] : null, anchor = anchor[0];
	else 
		id = null;
		
	switch (anchor)
	{
		case '#editintro':
			$('#intro').hide();
			$('#introedit').show();
			originals.introtitle=$('#introtitleedit').attr("value");
			originals.introtext=$('#introtextedit').attr("value");
			break;
		case '#editintro-accept':
			$('#introacceptbutton').attr("src","../style/adm/img/loading.gif");
			disableButton('#introacceptbutton');
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'editintro',
					introtitle: $('#introtitleedit').attr("value"),
					introtext: $('#introtextedit').attr("value")
				},
				success: function(intro){
					if ($.isEmptyObject(intro)){
						this.error();
						return;
					}
					$('#introtitle').text(intro.title);
					$('#introtext').html(intro.text);
					$('#introedit').hide();
					$('#intro').show();
					$('#introacceptbutton').attr("src","../style/adm/img/accept.png");
					enableButton('#introacceptbutton');
				},
				error: function(){
					alert("Edit failed!");
					$('#introacceptbutton').attr("src","../style/adm/img/accept.png");
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
			$('#aboutacceptbutton').attr("src","../style/adm/img/loading.gif");
			disableButton('#aboutacceptbutton');
			abouttitle = $('#abouttitleedit').attr("value");
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'editabout',
					abouttitle: $('#abouttitleedit').attr("value"),
					abouttext: $('#abouttextedit').attr("value")
				},
				success: function(about){
					if ($.isEmptyObject(about)){
						this.error();
						return;
					}
					$('#abouttitle').text(about.title);
					$('#abouttext').html(about.text);
					$('#aboutedit').hide();
					$('#about').show();
					$('#aboutacceptbutton').attr("src","../style/adm/img/accept.png");
					enableButton('#aboutacceptbutton');
				},
				error: function(){
					alert("Edit failed!");
					$('#aboutacceptbutton').attr("src","../style/adm/img/accept.png");
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
			
		case '#editsong':
			if (!id) break;
			if ($('#editsong' + id).length) break;
						
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'getsongdescr',
					songid: id
				},
				success: function(song){
					if ($.isEmptyObject(song)){
						this.error();
						return;
					}
					$('#song' + id).slideUp(function(){
						$("#songEditTemplate").tmpl({
							id: song.id,
							artist: $('#songartist' + song.id).text(),
							title: $('#songtitle' + song.id).text(),
							desc: song.descr
						}).insertBefore("#song" + id);
						$('#editsong' + id).slideDown();
					});
				},
				error: function(){
					alert("Song info retrieve failed!");
					window.location.hash="";
				}
			});
			break;
		case '#editsong-accept':
			if (!id) break;
			$('#editsongaccept' + id).attr("src","../style/adm/img/loading.gif");
			disableButton('#editsongaccept' + id);
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'editsong',
					songid: id,
					songartist: $('#editsongartist' + id).attr("value"),
					songtitle: $('#editsongtitle' + id).attr("value"),
					songdesc: $('#editsongdesc' + id).attr("value")
				},
				success: function(song){
					if ($.isEmptyObject(song)){
						this.error();
						return;
					}
					if ($('#filename' + song.id).length>0){
						$('#songNewTemplate').tmpl(song).appendTo('#songs').slideDown();
						$('#filename' + song.id).parent().slideUp(function(){$(this).remove()});
						
						AudioPlayer.embed("audioplayer_" + song.id, {soundFile:song.url});
					}
					else {
						$('#songartist' + song.id).text(song.artist);
						$('#songtitle' + song.id).text(song.title);
						$('#songdesc' + song.id).html(song.desc);
						$('#song' + song.id).slideDown();
					}
					$('#editsong' + song.id).slideUp(function(){$(this).remove()});
					window.location.hash="";
				},
				error: function(){
					alert("Song edit failed!");
					$('#editsongaccept'+id).attr("src","../style/adm/img/accept.png");
					enableButton('#editsongaccept'+id);
					window.location.hash="editsong:"+id;
				}
			});
			break;
		case '#editsong-reject':
			if (!id) break;
			$('#editsong' + id).slideUp(function(){
				$('#song' + id).slideDown();
				$('#editsong' + id).remove();
			});
			break;
		case '#archivesong':
			if (!id) break;
			$('#archivesong' + id).attr("src","../style/adm/img/loading.gif");
			disableButton('#archivesong' + id);
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'archivesong',
					songid: id
				},
				success: function(song){
					if ($.isEmptyObject(song)){
						this.error();
						return;
					}
					$('#archivesong' + song.id).parent().attr("href", "#removesong:"+song.id);
					enableButton('#archivesong'+id);
					$('#archivesong' + song.id).attr("alt", "Remove this Song").attr("src","../style/adm/img/remove.png").attr("id", "#removesong" + song.id);
					$('#song' + song.id).fadeTo('slow', 0.5,function(){$(this).addClass("archived")});
					window.location.hash="";
				},
				error: function(){
					alert("Song archive failed!");
					$('#archivesong'+id).attr("src","../style/adm/img/remove.png");
					enableButton('#archivesong'+id);
					window.location.hash="";
				}
			});
		case '#removesong':
			if (!id) break;
			break;
			
		case '#addlink':
			if ($('#addlinkform').attr("id")) return;
			$('#addlinkh').after('<h2 id="addlinkform" style="display:none;"><label>Name:<br /><input type="text" name="newlinkname" id="newlinkname" /></label><br /><label>Title Text:<br /><input type="text" name="newlinktitle" id="newlinktitle" /></label><br /><label>URL:<br /><input type="url" name="newlinkurl" id="newlinkurl" /></label><br /><a href="#addlink-accept"><img src="../style/adm/img/accept.png" alt="Add Link" width="20" height="20" id="linkaccept" /></a><a href="#addlink-reject"><img src="../style/adm/img/reject.png" alt="Cancel" width="20" height="20" /></a></h2>');
			$('#addlinkform').slideDown();
			break;
		case '#addlink-accept':
			$('#linkaccept').attr("src","../style/adm/img/loading.gif");
			disableButton('#linkaccept');
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'addlink',
					linkname: $('#newlinkname').attr("value"),
					linktitle: $('#newlinktitle').attr("value"),
					linkurl: $('#newlinkurl').attr("value")
				},
				success: function(link){
					if ($.isEmptyObject(link)){
						this.error();
						return;
					}
					$('#addlinkform').slideUp(function(){$(this).remove();})
					$('#sidebar').append('<h2 id="link'+link.id+'"><span style="float:left"><a href="#editlink:'+link.id+'"><img src="../style/adm/img/edit.png" alt="Edit Link" width="16" height="16" title="Edit Link" class="inlineicon" id="linkedit'+link.id+'" /></a><a href="#removelink:'+link.id+'"><img src="../style/adm/img/remove.png" alt="Remove Link" width="16" height="16" title="Remove Link" class="inlineicon" id="linkremove'+link.id+'" /></a></span><a href="'+link.url+'" title="'+link.title+'" class="link">'+link.name+'</a></h2>')
					window.location.hash="";
				},
				error: function(){
					alert("Link add failed!");
					$('#linkaccept').attr("src","../style/adm/img/accept.png");
					enableButton('#linkaccept');
					window.location.hash="addlink";
				}
			});
			break;
		case '#addlink-reject':
			$('#addlinkform').slideUp(function(){$(this).remove()});
			window.location.hash="";
			break;
		case '#editlink':
			var link = $('#link'+id);
			var id = link.attr("id").substr(4);
			if ($('#editlinkform'+id).attr("id")) return;
			var url = link.children('.link').attr("href");
			var title = link.children('.link').attr("title");
			var name = link.children('.link').text();
				
			link.after('<h2 id="editlinkform'+id+'" style="display:none;"><label>Name:<br /><input type="text" name="editlinkname'+id+'" id="editlinkname'+id+'" value="'+name+'" /></label><br /><label>Title Text:<br /><input type="text" name="editlinktitle'+id+'" id="editlinktitle'+id+'" value="'+title+'" /></label><br /><label>URL:<br /><input type="url" name="editlinkurl'+id+'" id="editlinkurl'+id+'" value="'+url+'" /></label><br /><a href="#editlink-accept:'+id+'"><img src="../style/adm/img/accept.png" alt="Accept Edits" width="20" height="20" id="linkeditaccept'+id+'" /></a><a href="#editlink-reject:'+id+'"><img src="../style/adm/img/reject.png" alt="Cancel" width="20" height="20" /></a></h2>');

			$('#link'+id).slideUp();
			$('#editlinkform'+id).slideDown();
			break;
		case '#editlink-accept':
			$('#linkeditaccept'+id).attr("src","../style/adm/img/loading.gif");
			disableButton('#linkeditaccept'+id);
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'editlink',
					id: id,
					linkname: $('#editlinkname'+id).attr("value"),
					linktitle: $('#editlinktitle'+id).attr("value"),
					linkurl: $('#editlinkurl'+id).attr("value")
				},
				success: function(link){
					if ($.isEmptyObject(link)){
						this.error();
						return;
					}
					$('#link'+link.id).slideDown().children('.link').attr("href",link.url).attr("title",link.title).text(link.name);
					$('#editlinkform'+link.id).slideUp(function(){$(this).remove();})
					window.location.hash="";
				},
				error: function(){
					alert("Link edit failed!");
					$('#linkeditaccept'+id).attr("src","../style/adm/img/accept.png");
					enableButton('#linkeditaccept'+id);
					window.location.hash="editlink:"+id;
				}
			});
			break;
		case '#editlink-reject':
			$('#editlinkform'+id).slideUp(function(){$(this).remove()});
			$('#link'+id).slideDown();
			window.location.hash="";
			break;
		case '#removelink':
			$('#linkremove'+id).attr("src","../style/adm/img/loading.gif");
			disableButton('#linkremove'+id);
			$.ajax({
				url: 'ajax.php',
				dataType: 'json',
				data: {
					action: 'removelink',
					id: id
				},
				success: function(link){
					if ($.isEmptyObject(link)){
						this.error();
						return;
					}
					$('#link'+link.id).slideUp(function(){$(this).remove();});
					window.location.hash="";
				},
				error: function(){
					alert("Link removal failed!");
					$('#linkremove'+id).attr("src","../style/adm/img/remove.png");
					enableButton('#linkremove'+id);
					window.location.hash="";
				}
			});
			break;
		
		case '#':
		case '':
		case null:
			// I'm not sure I actually need this,
			// but for the moment anyway,
			// I'll leave it here to be safe.
			break; 
	}
}