var interval;
function toggleChat(id){
	var convs = document.getElementsByClassName("chat-convo");
	
	for(x=0; convs.length > x; x++){
		convs[x].setAttribute('style','visibility:hidden');
		if(convs[x].attributes["data-id"].value==id){
			convs[x].setAttribute('style','visibility:visible');
			refreshConversation(id, document.getElementById("my_id").value,1);
			document.getElementById("current_chat_id").value=id;

			setTimeout(function(){
				$("input[data-receiver="+id+"]").focus();
			},1000);
			
		}
	}
	
}

function back(id){
	quick_sidebar_tab_1.setAttribute('class', 'tab-pane active page-quick-sidebar-chat');
}

function send(x, event = null){
	if(event != null){
		if(event.keyCode == 13){
			x = x.nextElementSibling.children[0];
		}
		else{
			return;
		}
	}
	

	var message = x.parentElement.previousElementSibling.value;
	var from = x.parentElement.previousElementSibling.attributes['data-sender'].value;
	var to = x.parentElement.previousElementSibling.attributes['data-receiver'].value;;
	var dataString = 'message='+ message +'&from='+ from +'&to='+ to;
	
	if(message==''){
		return;
	}
	
	x.parentElement.previousElementSibling.focus();
	x.parentElement.previousElementSibling.value="";
	$.ajax({
      type: "POST",
      data: dataString,
	  headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
	  url: '../../../api/sendMessage'
    })
    
    .done(function(data) 
    {
    	if(data=='sent'){
    		refreshConversation(to,from,1);
    	}
    	
  	});
}

function expandLimit(element,with_id,my_id){
	element.parentElement.parentElement.parentElement.dataset.limit = Number(element.parentElement.parentElement.parentElement.dataset.limit) + 10; 
	refreshConversation(with_id,my_id,0);

	element.setAttribute("disabled","disabled");
	element.innerHTML = 'Loading Conversation ...';
}

function refreshConversation(with_id, my_id,flag=0){
	
	var dataString = 'with=' + with_id + '&me='+ my_id +'&limit=' + document.getElementById("conversation_id"+with_id).dataset.limit;
	$.ajax({
      type: "POST",
      data: dataString,
	  headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
	  url: '../../../api/getConversation'
    })
    
    .done(function(data) 
    {
    	document.getElementById("conversation_id"+with_id).innerHTML ='';
    	
    	var raw_parsed = JSON.parse(data);
    	var parsed = raw_parsed.data;
    	var string = '';
    	var last_id = 0;

    	if(parsed.length < raw_parsed.total_rows){
    		string = string + '<div class="row" style="margin-top:5px"><div class="col-sm-12"><button onclick="expandLimit(this,'+with_id+','+my_id+')" class="btn blue btn-xs btn-block">Load More</button></div></div>'
    	}

    	for(x=0; parsed.length > x; x++){
    		if(parsed[x].sender_employee_id == my_id){
    			type ='out';
    		}
    		else{
    			type='in';
    		}
    		
    		//set the seen
    		var seen='';
    		if((parsed.length - 1 ) == x && parsed[x].is_read==1){
    			seen ='<br/><span>Seen at ' + parsed[x].message_update +'</span>';
    			last_id = parsed[x].message_id;
    		}

    		if(parsed[x].was_seen == 1){
    			document.getElementById("unseen_count").innerHTML = Number(document.getElementById("unseen_count").innerHTML) - 1;
    			if(Number(document.getElementById("unseen_count").innerHTML) == 0){
    				document.getElementById("unseen_count").setAttribute("style","display:none");
    			}
    			else{
    				document.getElementById("unseen_count").setAttribute("style","display:block");
    			}
    		}

    		string = string + '<div id="msg'+ parsed[x].message_id +'" title="Seen at '+ parsed[x].message_update +'" class="post '+ type +'"><img class="avatar" alt="" src="../../images/employees/'+ parsed[x].sender_picture +'"/><div class="message"><span class="arrow"></span><span class="datetime">'+ parsed[x].message_date +' <a onclick="deleteChat('+parsed[x].message_id+')"><i class="fa fa-times-circle-o"></i></a></span><span class="body" style="word-break: break-word;text-align:left;word-wrap: break-word;">'+ parsed[x].message +'</span>' + seen + '</div></div>';

    	}
    	document.getElementById("conversation_id"+with_id).innerHTML =string;
    	if(flag == 1){
	    	$('.page-quick-sidebar-chat-user-messages').scrollTop(1E10);
	    	var height = Number(document.getElementById('conversation_id'+with_id).attributes["data-height"].value);

	    	$('#conversation_id'+with_id).next().css("top",(height-30));
    	}


  	});
	
}

function deleteChat(id){
	var dataString = 'message_id=' + id;
	$.ajax({
      type: "POST",
      data: dataString,
	  headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
	  url: '../../../messages/deleteMessage'
    })
    
    .done(function(data) 
    {
    	document.getElementById("msg"+id).remove();
  	});
}


function refreshUnseen(){

	var count = 0;
	var emps = [];
	for(index = 0; document.getElementsByClassName("media").length>index; index++){
		emps.push($(".media").eq(index).data("id"));
	}

	$.ajax({
		method: "POST",
		url: "../../messages/countUnreadMessages",
		data: {emps:emps},
	  headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	}
	})
	.done(function(data) {
		var parsed = JSON.parse(data);
		var hits = 0;

		$(".media").each(function( index ) {
			$(".media").eq(index).children().eq(1).children().eq(1).children().eq(1).css('display','none');
		});

		if(parsed.length>0){
			for(x=0; x<parsed.length;x++){
				$("li[data-id='"+parsed[x].sender+"']").children().eq(1).children().eq(1).children().eq(1).css('display','block');
				$("li[data-id='"+parsed[x].sender+"']").children().eq(1).children().eq(1).children().eq(1).children().eq(1).html(parsed[x].hits);

				if(parsed[x].sender == Number(document.getElementById("current_chat_id").value) ){
					refreshConversation(parsed[x].sender, document.getElementById("my_id").value,1);
				}


				hits += parsed[x].hits;
			}
			document.getElementById("unseen_count").innerHTML = hits;
			document.getElementById("unseen_count").setAttribute("style","display:block");

			 $.ajax({
			      method: "GET",
			      url: "../ajax/checkFreshChat/"+ document.getElementById("my_id").value,
			    })
			    .done(function(data) {

			         if(data.length > 0){
			            var parsed = JSON.parse(data);

			            for(x=0; x<parsed.length; x++){

			               if(parsed[x].sender_id==document.getElementById("current_chat_id").value){
			                  continue;
			               }
			               
			               document.getElementById("notification_title").value = parsed[x].title;
			               document.getElementById("notification_message").value = parsed[x].message;
			               document.getElementById("notification_sender_id").value = parsed[x].sender_id;
			               play();
			               notifyMe();
			            }

			         }
			     });     
		}
		else{
			document.getElementById("unseen_count").setAttribute("style","display:none");
		}
	});
}


setInterval(refreshUnseen,5000);