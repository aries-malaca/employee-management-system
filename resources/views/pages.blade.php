@extends('layouts.main')

@section('content')

@if(session()->has('update') && session('update') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Pages successfully updated.</strong>
		</div>
    </div>
</div>
@endif

<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>List of Pages
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<form method="post" action="{{url('pages/processEdit')}}">
			{!! csrf_field() !!}
			<table id="sample_5" class="table dataTable table-striped table-bordered table-hover">
		        <thead>
				<tr>
					<th>Page Name</th>
					<th>Levels</th>
					<th>Active</th>
		        </tr>			
				</thead>
				<tbody> 
	                @foreach($pages as $value)
	                <tr>
	                    <td>{{ $value['menu_title'] }}</td>
	                    <td>
	                        @foreach($levels as $level)
	                        <input @if(strlen($value['levels'])>0) {{ (in_array( $level['id'],explode(",",$value['levels']))  ? 'checked':'') }} @endif
	                            name="{{ 'level['.$value['id'].']['. $level['id'].']'}}" type="checkbox" value="{{$level['id']}}"> {{$level['level_name']}}
	                        @endforeach
	                    </td>
	                    <td>
	                        <input type="checkbox" {{($value['menu_active']==1? 'checked':'')}} class="make-switch switch-large" name="{{ 'status['.$value['id'].']' }}" data-label-icon="fa fa-fullscreen" 
	                            data-on-text="<i class='fa fa-check'></i>" data-off-text="<i class='fa fa-times'></i>">
	                    </td>
	                </tr>
	                @endforeach
				</tbody>
			</table>
			<button type="submit" class="btn blue">Update</button>
		</form>
	</div>
</div>
@endsection