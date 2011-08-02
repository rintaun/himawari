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

function checkAnchor(){
	if(currentAnchor == document.location.hash) return;
	currentAnchor = document.location.hash;
	
	var anchor;
	if (anchor = currentAnchor.split(':'))
		id = (anchor[1]) ? anchor[1] : null, anchor = anchor[0];
		
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
			

		case '#uploadsong':
			break;
		case '#editsongs':
			break;
		case '#removesong':
			break;
		
		case '#addlink':
			if ($('#addlinkform').attr("id")) return;
			$('#addlink').after('<h2 id="addlinkform" style="display:none;"><label>Name:<br /><input type="text" name="newlinkname" id="newlinkname" /></label><br /><label>Title Text:<br /><input type="text" name="newlinktitle" id="newlinktitle" /></label><br /><label>URL:<br /><input type="url" name="newlinkurl" id="newlinkurl" /></label><br /><a href="#addlink-accept"><img src="../style/adm/img/accept.png" alt="Add Link" width="20" height="20" id="linkaccept" /></a><a href="#addlink-reject"><img src="../style/adm/img/reject.png" alt="Cancel" width="20" height="20" /></a></h2>');
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

var asdf = "";

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
		url: 'ajax.php?action=uploadsong'
	})
	.bind('fileuploadadd', function(e, data){
		$.each(data.files, function (index, file) {
			$('#songs').append('<div><h2 style="margin-bottom:2px;">'+file.name+'</h2><div class="progress" style="padding:0;width:100%;height:10px;border:1px solid #000"><span style="width:0%;height:100%;display:inline-block;margin:0;position:relative;top:-5px;" id="blah"></span></div><div class="edit"><input type="text" placeholder="Artist" name="newsongartist'+index+'" id="newsongartist'+index+'" class="newsong"/> - <input type="text" placeholder="Title" name="newsongtitle'+index+'" id="newsongtitle'+index+'" class="newsong"/><br /><textarea name="newsongdesc'+index+'" id="newsongdesc'+index+'" placeholder="Description" rows="7"></textarea><br/><span style="width:100%;text-align:right;display:inline-block"><a href="#editnewsong-accept"><img src="../style/adm/img/accept.png" width="30" height="30"/></a><a href="#editnewsong-reject"><img src="../style/adm/img/reject.png" width="30" height="30"/></a></span></div></div>');
			file.div = $('#songs div:last-child');
			file.progressbar = $('#songs div:last-child div.progress'); 
			file.progress = $('#songs div:last-child div.progress span')
			file.editpart = $('#songs div:last-child div.edit');
			file.head = $('#songs div:last-child h2')
		});
	})
	.bind('fileuploadfail', function(e, data){
		data.files[0].editpart.effect("highlight", {color: "#F00", mode: "hide"}, 500);
		data.files[0].head.append(' - <span style="color:#F00;">Filetype not supported</span>');
		//data.files[0].editpart.fadeOut(function(){$(this).remove();});
	})
	.bind('fileuploadsend', function(e, data){
		//alert(data.files[0].name);
		if (!data.files[0].name.match(new RegExp(/\.(mp3|mp4|m4a|ogg|wav)$/i))) return false;
		return true;
	})
	.bind('fileuploadprogress', function (e, data) {
		var progress = parseInt(data.loaded / data.total * 100, 10);
	    data.files[0].progress.css("width", progress+"%").css("background-color", setColor(progress));
	})
	.bind('fileuploaddone', function(e, data){
		var result = $.parseJSON(data.result)[0];
		if (result.error) {
			alert(result.error);
			data.files[0].editpart.effect("highlight", {color: "#F00", mode: "hide"}, 500);
			data.files[0].head.append(' - <span style="color:#F00;">Filetype not supported</span>');
		}
		data.files[0].progressbar.slideUp(function(){$(this).remove();});
	});
});
