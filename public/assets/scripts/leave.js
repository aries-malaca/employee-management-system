function validate(){
    var leave_mode = $("#leave_mode").val();
    var leave_start = $("#leave_start").val();
    var leave_end = $("#leave_end").val();
    var leave_type = $("#leave_type").val();
    var credits = document.getElementById("leave_type").selectedOptions[0].dataset.credit;
    var gender = document.getElementById("leave_type").selectedOptions[0].dataset.gender;

    if(leave_mode != 'FULL'){
        $("#leave_end").val(leave_start);
        leave_end = $("#leave_end").val();
        toastr.clear();
        toastr.error('Only 1 day can be filed on Half day leave.');
    }

    if(invalidRange(leave_start,leave_end)){
        $("#leave_end").val(leave_start);
        toastr.clear();
        toastr.error('Invalid Range.');
        return;
    }

    if(gender == 'female'){
    	$("#leave_end").val(moment(leave_start).add(credits-1,'days').format('MM/DD/YYYY'));
    }

    if((credits -1) < getDiffDays(leave_start, leave_end)){
        toastr.clear();
    	toastr.error('Not Enough credits');
    	$("#leave_end").val($("#leave_start").val());
        return;
    }
}

function getDiffDays(leave_start, leave_end){
    var date1 = new Date(leave_start);
    var date2 = new Date(leave_end);

    var timeDiff = Math.abs(date2.getTime() - date1.getTime());
    return Math.ceil(timeDiff / (1000 * 3600 * 24)); 
}

function invalidRange(leave_start, leave_end){
    var date1 = new Date(leave_start);
    var date2 = new Date(leave_end);

    if(date2.getTime() < date1.getTime()){
        return true;
    }

    return false;
}