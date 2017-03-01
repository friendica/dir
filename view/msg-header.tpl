
<script language="javascript" type="text/javascript" src="$baseurl/tinymce/jscripts/tiny_mce/tiny_mce_src.js"></script>
<script language="javascript" type="text/javascript">

tinyMCE.init({
	theme : "advanced",
	mode : "specific_textareas",
	editor_selector: /(profile-jot-text|prvmail-text)/,
	plugins : "bbcode",
	theme_advanced_buttons1 : "bold,italic,underline,undo,redo,link,unlink,image,forecolor",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "center",
	theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle",
	content_css : "bbcode.css",
	entity_encoding : "raw",
	add_unload_trigger : false,
	remove_linebreaks : false,
	convert_urls: false,
	content_css: "$baseurl/view/custom_tinymce.css",
	     //Character count
	theme_advanced_path : false,
	setup : function(ed) {
		ed.onKeyUp.add(function(ed, e) {
			var txt = tinyMCE.activeEditor.getContent();
			var text = txt.length;
			if(txt.length <= 140) {
				$('#character-counter').removeClass('red');
				$('#character-counter').removeClass('orange');
				$('#character-counter').addClass('grey');
			}
			if((txt.length > 140) && (txt .length <= 420)) {
				$('#character-counter').removeClass('grey');
				$('#character-counter').removeClass('red');
				$('#character-counter').addClass('orange');
			}
			if(txt.length > 420) {
				$('#character-counter').removeClass('grey');
				$('#character-counter').removeClass('orange');
				$('#character-counter').addClass('red');
			}
			$('#character-counter').text(text);
    		});
     	}
});

</script>
<script type="text/javascript" src="include/ajaxupload.js" ></script>
<script>
	$(document).ready(function() {
		var uploader = new window.AjaxUpload(
			'prvmail-upload',
			{ action: 'wall_upload',
				name: 'userfile',
				onSubmit: function(file,ext) { $('#profile-rotator').show(); },
				onComplete: function(file,response) {
					tinyMCE.execCommand('mceInsertRawHTML',false,response);
					$('#profile-rotator').hide();
				}				 
			}
		);

	});

	function jotGetLink() {
		reply = prompt("Please enter a link URL:");
		if(reply && reply.length) {
			$('#profile-rotator').show();
			$.get('parse_url?url=' + reply, function(data) {
				tinyMCE.execCommand('mceInsertRawHTML',false,data);
				$('#profile-rotator').hide();
			});
		}
	}

	function linkdropper(event) {
		var linkFound = event.dataTransfer.types.contains("text/uri-list");
		if(linkFound)
			event.preventDefault();
	}

	function linkdrop(event) {
		var reply = event.dataTransfer.getData("text/uri-list");
		event.target.textContent = reply;
		event.preventDefault();
		if(reply && reply.length) {
			$('#profile-rotator').show();
			$.get('parse_url?url=' + reply, function(data) {
				tinyMCE.execCommand('mceInsertRawHTML',false,data);
				$('#profile-rotator').hide();
			});
		}
	}

</script>

