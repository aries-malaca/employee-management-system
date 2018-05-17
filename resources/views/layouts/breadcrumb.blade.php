@if(isset($page['title']))
<h3 class="page-title">
{{ $page['title'] or "Home" }} <small>{{ $page['description'] or ""}}</small>
</h3>			
@endif
<div class="page-bar" style="margin-bottom: 10px;">
	<ul class="page-breadcrumb util-btn-margin-bottom-5">
		@if(isset($page['title']))
			@if($page['title'] != 'Employee Dashboard' && $page['title'] != 'Employee Forms')
				<li>
					<i class="fa fa-home"></i>
					<a href="/">Home</a>
				</li>			
			@else
				<div class="clearfix">
				@if($config['show_timein_button'] == 1)
					@if(!$config['is_timedin'] || $config['is_completed_time'])
						<a id="pulsate-regular" {{$submit_once}} href="{{url('attendance/onScreenAddLog')}}" 
							class="btn btn-md green">Time in</a>
					@else
						<a id="pulsate-regular" {{$submit_once}} onclick="this.setAttribute('style','display:none')" href="{{url('attendance/onScreenAddLog')}}" 
							class="btn btn-md red">Time out</a>
					@endif 
				
				@endif

				@if( $page['title'] == 'Employee Forms')
					<a href="{{url('calendar')}}" class="btn btn-md blue">Employee Dashboard</a>
				@else
					<a href="{{url('forms')}}" class="btn btn-md blue">Employee Forms</a>
				@endif
				</div>
			@endif

		
		@else
			<div class="clearfix">
			@if($config['show_timein_button'] == 1)
				@if(!$config['is_timedin'] || $config['is_completed_time'])
					<a id="pulsate-regular" {{$submit_once}} href="{{url('attendance/onScreenAddLog')}}" 
						class="btn btn-md green">Time in</a>
				@else
					<a id="pulsate-regular" {{$submit_once}} onclick="this.setAttribute('style','display:none')" href="{{url('attendance/onScreenAddLog')}}" 
						class="btn btn-md red">Time out</a>
				@endif 
			
			@endif
			<a href="{{url('calendar')}}" class="btn btn-md blue">Employee Dashboard</a>
			<a href="{{url('forms')}}" class="btn btn-md yellow">Employee Forms</a>
			</div>

		@endif

	
		@if(isset($page['parent']))
		<li>
			<i class="fa fa-angle-right"></i>
			<a href="{{ url($page['parent_url']) }}">{{$page['parent']}}</a>
		</li>
		@endif


		@if(isset($page['sub_parent']))
		<li>
			<i class="fa fa-angle-right"></i>
			<a href="{{ url($page['sub_parent_url']) }}">{{ $page['sub_parent'] or ""}}</a>
		</li>
		@endif
		
		@if(isset($page['title']))
			@if($page['title'] != 'Employee Dashboard' && $page['title'] != 'Employee Forms' )
				<li>
					<i class="fa fa-angle-right"></i>
					<a href="">{{ $page['title'] or ""}}</a>
				</li>
			@endif

		@endif
	</ul>
	<div class="page-toolbar">
		<div id="dashboard-report-range" style="background-color:#364150" class="pull-right tooltips btn btn-fit-height grey-salt" data-placement="top" >
			<i class="icon-calendar"></i>&nbsp; <span class="thin uppercase visible-lg-inline-block"></span>&nbsp; <?= date('m/d/Y') ?> <span id="time"> </span>
		</div>
	</div>
</div>

<script>
function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    m = checkTime(m);
    s = checkTime(s);
    
    var ampm ='';
    
    if(h>11 && h!=0){
    	ampm='PM'
    	
    	if(h>12){
    		h = h - 12;
    	}
    }
    else{
    	ampm='AM';
    	
    	if(h==0){
    		h = 12;
    	}
    }
    
    
    document.getElementById('time').innerHTML =
    h + ":" + m + ":" + s + " " + ampm;
    var t = setTimeout(startTime, 500);
}
function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
}

window.onload=startTime;
</script>
