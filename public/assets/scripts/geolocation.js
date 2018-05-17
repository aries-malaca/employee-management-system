
function getLocation() {
    if($("#location_address").length > 0){
      if(document.getElementById("location_address").innerHTML == ""){
        $.ajax({
          method: "GET",
          url: "../../api/getIPLocation/" + $("#ip_address").val()
        })
        .done(function(data) {
          saveLocation(data.lat, data.lon);
        });
      }
    }
}

function showPosition(position) {
    saveLocation(position.coords.latitude,position.coords.longitude);
}

$(document).ready(function(){
    getLocation();
});

function saveLocation(lat, long){
  var dataString = 'my_id='+ document.getElementById("my_id").value +'&latitude='+ lat +'&longitude='+ long;
    $.ajax({
      type: "POST",
      data: dataString,
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
    url: '../../../api/saveLocation'
    })
    .done(function(data) 
    {
      echoLocation(Number(document.getElementById("my_id").value));
    });
}

function echoLocation(id){
    $.ajax({
      method: "GET",
      url: "../../../api/getUserLocationAddress/"+id 
    })
    .done(function(data) {
      if($("#location_address").length > 0){
        document.getElementById("location_address").innerHTML = data;
      }
    });
}


function getTooltipLocation(){
  $(".location-tooltip").each(function(index){
    $.ajax({
      method: "GET",
      url: "../../../api/getUserLocationAddress/"+ $(".location-tooltip").eq(index).data("id")
    })
    .done(function(data) {
      $(".location-tooltip").eq(index).attr("data-original-title","Last location: " + data);
    });
  });

}