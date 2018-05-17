var currentRequest = null;

function getMails(from , page, sortby, orderby){
  
  $("#loader").show();
  $("#inbox-content").html("");
  
  currentRequest = $.ajax({
    method: "POST",
    data: {folder:from, current_page:page, sortby:sortby, orderby:orderby},
    url: "../../mail/getMails",
	headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
    beforeSend : function()    {           
        if(currentRequest != null) {
            currentRequest.abort();
        }
    }
  })
  .done(function(data) {
  	$("#loader").hide();
    $("#inbox-content").html(data);
    	initListeners();
    	$("#current_page").val(page);
  });
}

function deleteMail(ids, folder){
  $.ajax({
    method: "POST",
    data: {folder:folder, ids:ids},
    url: "../../mail/deleteMail", 
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
    beforeSend : function()    {           
        if(currentRequest != null) {
            currentRequest.abort();
        }
    }
  })
  .done(function(data) {
  		getMails(folder , 1, 'date', 'desc');
  		UIAlertDialogApi.showAlert('Mail(s) has been deleted.',"success");
  });
}

function addFlag(ids, folder, flag){
  $.ajax({
    method: "POST",
   	headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
	data: {folder:folder, ids:ids, flag:flag},
    url: "../../mail/addFlag"
  })
  .done(function(data) {
  	UIAlertDialogApi.showAlert("Flag "+flag+" has been added.","success");
  });
}

function clearFlag(ids, folder, flag){
  $.ajax({
    method: "POST",
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
	data: {folder:folder, ids:ids, flag:flag},
    url: "../../mail/clearFlag"
  })
  .done(function(data) {
  	UIAlertDialogApi.showAlert("Flag "+flag+" has been cleared.","success");
  });
}


function moveMail(ids, folder, destination){
  $.ajax({
    method: "POST",
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	},
	data: {folder:folder, ids:ids, destination:destination},
    url: "../../mail/moveMail"
  })
  .done(function(data) {
  	getMails(folder , 1, 'date', 'desc');
  	UIAlertDialogApi.showAlert("Mail(s) has been moved to "+ destination +".","success");
  });
}

var initListeners = function(){
	$('.view-message').click(function(e){
		  $("#loader").show();
		  $("#inbox-content").html('');
		  
		  currentRequest = $.ajax({
		    method: "GET",
		    url: "../../mail/showMail/"+  $(this).parent().data("folder") +"/" + $(this).parent().data("messageid"),
		    beforeSend : function()    {           
		        if(currentRequest != null) {
		            currentRequest.abort();
		        }
		    }
		  })
		  .done(function(data) {
		    $("#inbox-content").html(data);
		    $("#loader").hide();
		    initListeners();
		  });
	});

	$("#mark").click(function(){
		$(".mail-checkbox").attr('checked',true)
	});

	$("#unmark").click(function(){
		$(".mail-checkbox").attr('checked',false)
	});

	$("#read").click(function(){
		var folder = '';
		var ids = [];

		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			$(".mail-checkbox:checkbox:checked").eq(index).parent().parent().children().eq(3).html('<i class="fa fa-eye"></i>');
			$(".mail-checkbox:checkbox:checked").eq(index).parent().parent().removeClass("unread");
		});
		addFlag(ids,folder,'Seen');
	});

	$("#important").click(function(){
		var folder = '';
		var ids = [];

		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			$(".mail-checkbox:checkbox:checked").eq(index).parent().parent().children().eq(2).html('<i class="fa fa-star inbox-started"></i>');
		});
		addFlag(ids,folder,'Flagged');
	});

	$("#unread").click(function(){
		var folder = '';
		var ids = [];

		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			$(".mail-checkbox:checkbox:checked").eq(index).parent().parent().children().eq(3).html('');
			$(".mail-checkbox:checkbox:checked").eq(index).parent().parent().addClass("unread");
		});

		clearFlag(ids,folder,'Seen');
	});

	$("#delete").click(function(){
		var folder = '';
		var ids = [];

		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
		});

		deleteMail(ids,folder);
	});

	$("#moveinbox").click(function(){
		var folder = '';
		var ids = [];
		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
		});
		moveMail(ids,folder, 'Inbox');
	});

	$("#movesent").click(function(){
		var folder = '';
		var ids = [];
		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
		});
		moveMail(ids,folder, 'Sent');
	});	
	$("#movedraft").click(function(){
		var folder = '';
		var ids = [];
		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
		});
		moveMail(ids,folder, 'Drafts');
	});	
	$("#movetrash").click(function(){
		var folder = '';
		var ids = [];
		$(".mail-checkbox:checkbox:checked").each(function( index ) {
			ids.push($(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("messageid"));
			folder = $(".mail-checkbox:checkbox:checked").eq(index).parent().parent().data("folder");
		});
		moveMail(ids,folder, 'Trash');
	});

};

function sendMail(t){
	t.setAttribute("style","display:none");
	if($(".ckeditor").eq(0).val() == '')
		message = CKEDITOR.instances.editor1.getData();
	else{
		message = $(".ckeditor").eq(0).val();
	}

	subject = document.getElementById("subject").value;

	to_list = document.getElementsByClassName("to");
	var to = [];

	for(x=0; x<to_list.length;x++){
		if(to_list[x].value.length>0){
			if(isEmail(to_list[x].value) === false){
				alert(to_list[x].value + ' is invalid email address.');
				t.setAttribute("style","");
				return;
			}
			else{
				to.push(to_list[x].value);
			}
		}
	}

	if(to.length == 0){
		alert('Insert atleast 1 recipient in TO: field.');
		t.setAttribute("style","");
		return;
	}

	cc_list = document.getElementsByClassName("cc");
	var cc = [];

	for(x=0; x<cc_list.length;x++){
		if(cc_list[x].value.length>0){
			if(isEmail(cc_list[x].value) === false){
				alert(cc_list[x].value + ' is invalid email address.');
				t.setAttribute("style","");
				return;
			}
			else{
				cc.push(cc_list[x].value);
			}
		}
	}

	bcc_list = document.getElementsByClassName("bcc");
	var bcc = [];

	for(x=0; x<bcc_list.length;x++){
		if(bcc_list[x].value.length>0){
			if(isEmail(bcc_list[x].value) === false){
				alert(bcc_list[x].value + ' is invalid email address.');
				t.setAttribute("style","");
				return;
			}
			else{
				bcc.push(bcc_list[x].value);
			}
		}
	}

	if(hasPendingUpload()==true){
		alert("Has pending upload.");
		t.setAttribute("style","");
		return;
	}

	var dataString = {subject: subject,message:message,to:to,cc:cc,bcc:bcc,files:getFilenames()};
	$.ajax({
      type: "POST",
      data: dataString,
	  headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
	  url: '../../../mail/sendMail'
    })
    
    .done(function(data) 
    {
    	if(data == 'ok'){
    		UIAlertDialogApi.showAlert("Email has been sent.","success")
    		refreshForm();
    	}
    	else{
    		UIAlertDialogApi.showAlert("Could not send Email","warning")
    	}
    	t.setAttribute("style","");
    	
  	});
}


$(document).ready(function(){
	getMails('Inbox', 1, 'date', 'desc');


	//folder selector
	$('.inbox-nav li').click(function(e){
		$(".inbox-nav li").each(function( index ) {
			$(this ).removeClass("active");
		});

		$(this).addClass("active");

		if($(this).data("action") != 'COMPOSE'){
			getMails($(this).data("action") , 1, 'date', 'desc');
			$("#composer_div").hide(40);
		}
		else{
			if(currentRequest != null) {
	            currentRequest.abort();
	            $("#loader").hide();
	        }
			$("#inbox-content").html('');
			$("#composer_div").show(40);

		}
	});

	//tags input
});

//http://stackoverflow.com/questions/2507030/email-validation-using-jquery
function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

function hasPendingUpload(){
	var table = document.getElementsByClassName("files")[0];
	for(x=0; x<table.children.length; x++){
		row = table.children[x];
		if(row.children[3].className=='cancel'){
			return true;
		}
	}

	return false;
}

function getFilenames(){
	var files = [];
	var table = document.getElementsByClassName("files")[0];
	for(x=0; x<table.children.length; x++){
		row = table.children[x];
		files.push(row.children[0].children[0].innerHTML);
	}

	return files;
}

function refreshForm(){
	var to = document.getElementsByClassName("to")[0].parentElement.parentElement.parentElement;
	if(to.children.length > 2){
		for(x=0;x<to.children.length - 1;x++){
			to.children[x].remove();
		}
	}
	to.children[0].children[0].children[0].value='';	

	var cc = document.getElementsByClassName("cc")[0].parentElement.parentElement.parentElement;
	if(cc.children.length > 2){
		for(x=0;x<cc.children.length - 1;x++){
			cc.children[x].remove();
		}
	}
	cc.children[0].children[0].children[0].value='';	

	var bcc = document.getElementsByClassName("bcc")[0].parentElement.parentElement.parentElement;
	if(bcc.children.length > 2){
		for(x=0;x<bcc.children.length - 1;x++){
			bcc.children[x].remove();
		}
	}

	$("#subject").val("");
	bcc.children[0].children[0].children[0].value='';
	CKEDITOR.instances.editor1.setData('');
	$(".files").empty();
}

function addField(element){
	var elem = element.parentElement.previousElementSibling;
	var cln = elem.cloneNode(true);
	cln.children[0].children[0].value='';
	// Append the cloned <li> element to <ul> with id="myList1"
	element.parentElement.parentElement.insertBefore(cln,element.parentElement);
}

function deleteField(element){
	if(element.parentElement.parentElement.parentElement.parentElement.children.length == 2){
		element.parentElement.previousElementSibling.value='';
		return;
	}
	element.parentElement.parentElement.parentElement.remove();

}

function toggleContactPicker(element){

}

function showReply(sender, subject){
	refreshForm();
	document.getElementsByClassName("to")[0].value= sender;
	document.getElementById("subject").value = 'RE: ' + subject;

	$("#inbox-content").html('');
	$("#composer_div").show(40);
}

function showForward(subject){
	if($("#the_plain").length == 1){
		var data = $("#the_plain").html();
	}
	else{
		var data = $("#the_html").attr("srcdoc");
	}

	refreshForm();
	document.getElementById("subject").value = 'FW: ' + subject;

	$("#inbox-content").html('');
	$("#composer_div").show(40);

	setTimeout(function(){
		CKEDITOR.instances.editor1.setData(data, function()
		{
		    this.checkDirty();  // true
		});
	},3000);


}