var listing = document.getElementById("listing");
var listing_div = document.getElementById("listing_div");

function addToList(employee, branch, system){
	var tableItemsCount = listing.children[0].children.length;
	if(tableItemsCount >1 ){
		for(x=1; x<tableItemsCount; x++){
	        if(listing.lastElementChild.children[x].children[0].children[0].value == 
	                employee.selectedOptions[0].value ){
	            alert("Employee already in list.");
	            return;
	        }
	    }
	}
	
	addButton.setAttribute("style", "display:none");
	$.ajax({
      method: "GET",
      url: "../ajax/getBranchSchedules/" + branch.value,
    })
    .done(function(data) {
        addButton.setAttribute("style", "display:block");
        listing_div.setAttribute("style","display:block");
    	parsed = JSON.parse(data);
    	console.log(parsed);
    	
    	if(parsed.length == 0){
    		alert("No Available Schedules for this branch.");
    		return;
    	}
    	
    	var row = listing.insertRow(tableItemsCount);
 		row.insertCell(0).innerHTML = '<input type="hidden" value="'+ employee.value +'"/>' + employee.selectedOptions[0].innerHTML;
    	
    	for(var x=0;x<=6;x++){
    		var str='<select style="width:100px">';
    		for (var i = 0; parsed.length>i; i++) {
    			var title = '';
                
    			title =parsed[i].schedule_name;
    			
    	    	sc = parsed[i].schedule_data.split(",");
    	    	
    	    	if(sc[x] == 'closed'){
    	    	    continue;
    	    	}
    	    	else{
    	    	    val = sc[x];
    	    	}
    			
    			str = str + '<option value="'+val+'">'+title+'</option>';
    		}
    		str = str + '<option value="closed">Rest Day</option>';
    		str = str + '</select>'
    		row.insertCell(x+1).innerHTML = '<div>'+str+'</div>';
	    }

    	row.insertCell(8).innerHTML = '<input type="hidden" value="'+ branch.value +'"/>'+ document.getElementById("setBranch").selectedOptions[0].innerHTML ;
		row.insertCell(9).innerHTML = '<select><option value="1">Yes</option><option value="0">No</option></select>';
		row.insertCell(10).innerHTML = '<button type="button" class="btn btn-xs red mini" onclick="deleteItem(this)">Delete </button>'
    });
}

function deleteItem(item){
	item.parentElement.parentElement.remove();
	if(listing.children[0].children.length == 1 ){
	    listing_div.setAttribute("style","display:none");
	}
}

function sendData(x,system){
	x.setAttribute("style","display:none");
	fromdate = $("#setStart").val();
	todate = $("#setEnd").val();

	var cart = document.getElementById("listing");
	var rows = cart.firstElementChild.children.length - 1;
	var employee = new Array();
	var branch = new Array();
	var is_flexi_time = new Array();
	
	var sunday = new Array();
	var monday = new Array();
	var tuesday = new Array();
	var wednesday = new Array();
	var thursday = new Array();
	var friday = new Array();
	var saturday = new Array();

	for (var i = 0; i < rows; i++) {
		var row = cart.firstElementChild.children[i+1];
		employee.push(row.firstElementChild.firstElementChild.value);
		branch.push(row.children[8].children[0].value);
		is_flexi_time.push(row.children[9].children[0].value);
		sunday.push(row.children[7].children[0].children[0].value);
		monday.push(row.children[1].children[0].children[0].value);
		tuesday.push(row.children[2].children[0].children[0].value);
		wednesday.push(row.children[3].children[0].children[0].value);
		thursday.push(row.children[4].children[0].children[0].value);
		friday.push(row.children[5].children[0].children[0].value);
		saturday.push(row.children[6].children[0].children[0].value);
	}

	time = {0:sunday, 1:monday, 2:tuesday, 3:wednesday, 4:thursday, 5:friday, 6:saturday};

	$.post( "../ajax/setSchedule", { _token: $('input[name=_token]').val(), system:system, fromdate: fromdate, todate:todate, employee: employee, time:time , branch:branch, is_flexi_time:is_flexi_time})
	  .done(function( data ) {
	    console.log(data);
	    if(data=='success'){
	    	alert("Schedule successfully added");
	    }
	    x.setAttribute("style","display:block");
	  });
}