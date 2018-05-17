@extends('layouts.main')

@section('content')

@if(session()->has('editing') && session('editing') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Salary base successfully updated.</strong>
		</div>
    </div>
</div>
@endif
		
<ul class="nav nav-tabs" >
    <li class="active">
		<a href="#tab_1_3" data-toggle="tab">
		Employee Salaries </a>
	</li>
	<li class="">
		<a href="#tab_1_4" data-toggle="tab">
		Salary Grading </a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade active in" id="tab_1_3">
	    <div class="portlet box green">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i>Employee Salaries
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse" data-original-title="" title="">
					</a>

				</div>
			</div>
			<div class="portlet-body">
                <table id="sample_5" class="table dataTable table-striped table-bordered table-hover">
        	        <thead>
        			<tr>
        				<th>Employee</th>
        				<th>Salary</th>
        				<th>Date Effective</th>
						<th>Date Updated</th>
        	        </tr>			
        			</thead>
        			<tbody> 
        		    @foreach($employee_salaries as $value)
                        <tr>
                            <td><a href="{{url('employee/'.$value['user_id'])}}">{{$value['employee']}}</a></td>  
                            <td>{{ number_format($value['salary'],2)}}</td>
                            <td>{{ dateNormal($value['date_effective']) }}</td>  
                            <td>{{ dateNormal($value['updated_at']) }}</td>
                        </tr>
        		    @endforeach
        			</tbody>
        		</table>
			</div>
		</div>
	</div>
	<div class="tab-pane fade" id="tab_1_4">
        <div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i>Salary Grades
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse" data-original-title="" title="">
					</a>

				</div>
			</div>
			<div class="portlet-body">
                <table id="sample_6" class="table dataTable table-striped table-bordered table-hover">
        	        <thead>
            			<tr>
            			    <th>Grade</th>
            			    @for($x=1; $x <= 12; $x++ )
            				<th>Step {{$x}}</th>
            				@endfor
            				<th>&nbsp;</th>
            	        </tr>			
        			</thead>
        			<tbody> 
        		    @foreach($salary_grades as $value)
        		    <tr>
        		        <td>{{$value['grade_number']}}</td>
                        @for($x=1; $x <= 12; $x++ )
            			<td> {{ number_format($value['amounts'][$x-1],2) }}</td>
            			@endfor
            			<td>
            			    <a data-toggle="modal" href="#edit{{$value['grade_number']}}" type="button" class="btn blue btn-sm">Edit</a>
            			    <!-- Start of add bank Modal-->
                            <div class="modal fade" id="edit{{$value['grade_number']}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            	<div class="modal-dialog modal-lg">
                            		<div class="modal-content">
                            			<form action="{{url('salaries/editSalaryBase')}}" method="post">
                            			<input type="hidden" value="{{$value['grade_number']}}" name="id"/>
                            			{!! csrf_field() !!}
                            			<div class="modal-header">
                            				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            				<h4 class="modal-title">Edit Salary Grade {{ $value['grade_number'] }}</h4>
                            			</div>
                            			<div class="modal-body">
                            				<div class="form-body">
                            				    @for($x=1; $x <= 12; $x++ )
                            				        @if($x%4 == 1)
                                            	    <div class="row">
                                            	    @endif
                                                		<div class="col-sm-3">
                                                		    <div class="form-group">
                                                				<label class="control-label bold">Step {{$x}}</label>
                                                				<input type="text" value="{{$value['amounts'][$x-1]}}" name="step[]" class="form-control">
                                                			</div>
                                                		</div>
                                                	@if($x%4 == 0)
                                            	    </div>
                                            	    @endif
                                            	@endfor
                                            </div>
                            			</div>
                            			<div class="modal-footer">
                            				<button type="submit" class="btn blue">Save</button>
                            				<button type="button" class="btn default" data-dismiss="modal">Close</button>
                            			</div>
                            			</form>
                            		</div>
                            		<!-- /.modal-content -->
                            	</div>
                            	<!-- /.modal-dialog -->
                            </div>
                            <!-- end of add bank Modal-->
            			</td>
            		</tr>
        		    @endforeach
        			</tbody>
        		</table>
			</div>
		</div>
	</div>
</div>
@endsection