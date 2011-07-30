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
	switch (currentAnchor)
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
		//case '#removesong':
		//	break;
		/*
		 * 	
		 */
		case '#addlink':
			if ($('#addlinkform').attr("id")) return;
			$('#addlink').after('<h2 id="addlinkform"><label>Name:<br /><input type="text" name="newlinkname" id="newlinkname" /></label><br /><label>Title Text:<br /><input type="text" name="newlinktitle" id="newlinktitle" /></label><br /><label>URL:<br /><input type="url" name="newlinkurl" id="newlinkurl" /></label><br /><a href="#addlink-accept"><img src="../style/adm/img/accept.png" alt="Add Link" width="20" height="20" id="linkaccept" /></a><a href="#addlink-reject"><img src="../style/adm/img/reject.png" alt="Cancel" width="20" height="20" /></a></h2>');
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
					$('#addlinkform').remove();
					$('#sidebar').append('<h2><a href="'+link.url+'" title="'+link.title+'" class="link">'+link.name+'</a></h2>')
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
			$('#addlinkform').remove();
			window.location.hash="";
			break;
		case '#editlinks':
			alert($('.link'));
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
