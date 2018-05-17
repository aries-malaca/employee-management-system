function getAge(birthdate){
    var today = new Date();
    var birthDate = new Date(birthdate);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    document.getElementById("age").value = (age);
}

function fillPositions(department_id){
  $.ajax({
    method: "GET",
    url: "../../get/Position/" + department_id,
  })
  .done(function(data) {
    var parsed = JSON.parse(data);
    
    var str = '';
    
    for(var x in parsed){
        
      str = str + '<option value="'+ parsed[x]['id'] +'">' + parsed[x]['position_name'] + '</option>';
    }
    
    document.getElementById("position_id").innerHTML = str;
  });
}


function getSalary(){
  var grade = document.getElementById("salary_grade").value;
  var step = document.getElementById("salary_step").value;
  
  $.ajax({
    method: "GET",
    url: "../../fetch/Salary/" + grade + "/" + step,
  })
  .done(function(data) {
    document.getElementById("salary").value = data;
  });
}

function selectSalary(){
  if(document.getElementById("salary").value != ''){
    document.getElementById("salary_rate").value =document.getElementById("salary").value;
  }
   
}



function getSchedule(){
  $.ajax({
    method: "GET",
    url: "../../schedules/get/" + document.getElementById("current_id").value
  })
  .done(function(data) {
    document.getElementById("events2").value = data;
  });
}

function getAttendance(){
  $.ajax({
    method: "GET",
    url: "../../attendance/getAttendance/" + document.getElementById("current_id").value
  })
  .done(function(data) {
    document.getElementById("events").value = data;
  });
}

window.onload = function(){
  getSchedule();
  getAttendance();
};