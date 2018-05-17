@extends('layouts.main')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> Activity Logs
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
 				<table  id="sample_5" class="table dataTable table-striped table-bordered table-hover">
        	        <thead>
        			<tr>
        			    <th>Category</th>
        			    <th>Date</th>
        				<th>Details</th>
        				@if(Request::segment(2)!='myLogs')
						<th>User</th>
						@endif
                    </tr>			
        			</thead>
        			<tbody> 
  					  @foreach($logs as $key=>$value)
        		      <tr>
        		          <td>{{ $value['log_category'] }}</td>
        		          <td>{{ dateTimeNormal($value['log_date']) }}</td>
        		          <td>{{ $value['log_details'] }}</td>
						  @if(Request::segment(2)!='myLogs')
        		          <td><a href="{{url('employee/'.$value['user_id'])}}">{{ $value['first_name'] .' ' . $value['last_name'] }}</a></td>
						  @endif
        		       </tr>
        		      @endforeach
        			</tbody>
        	    </table>
            </div>
        </div>
    </div>  
</div>
@endsection