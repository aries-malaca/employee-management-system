@extends('layouts.main')

@section('content')

<ul class="nav nav-tabs" >
    <li class="active">
		<a href="#tab_1_3" data-toggle="tab">
		For Evaluation List </a>
	</li>
	<li class="">
		<a href="#tab_1_4" data-toggle="tab">
		Evaluation History </a>
	</li>
	<li class="">
		<a href="#tab_1_5" data-toggle="tab">
		Evaluation Template </a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade active in" id="tab_1_3">
        <div class="portlet box green">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>For Evaluation List
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
        				<th>Evaluation Date</th>
        				<th>Date Hired</th>
        				<th style="width:120px"></th>
        	        </tr>			
        			</thead>
        			<tbody> 
                     @foreach($config['employees_for_evaluation'] as $key => $value)
        	        <tr>
        	            <td>
        	            	<a href="{{ url('employee/' . $value['id'] ) }}"><img class="img" 
        	            			style="height: 26px" src="{{ asset('images/employees/'.$value['picture']) }}"></a>
        	            	<a href="{{ url('employee/' . $value['id'] ) }}">
        		            	{{ @$value['name'] }}
        	            </td>

        	            <td>{{dateNormal($value['next_evaluation'])}}</td>
        	            <td>{{dateNormal($value['hired_date'])}}</td>
        	            <td>
                            <a data-toggle="modal" href="#modal_eval{{$value['id']}}" type="button" class="btn blue btn-xs">Evaluate</a>
			                <!-- Start of eval Modal-->
			                <div class="modal fade" id="modal_eval{{$value['id']}}" tabindex="-1" role="dialog" 
			                    aria-labelledby="myModalLabel" aria-hidden="true">
			                	<div class="modal-dialog modal-lg">
			                		<div class="modal-content">
			                			<form action="{{url('evaluation/processAdd')}}" method="post">
			                			<input type="hidden" value="{{$value['id']}}" name="employee_id">
			                			{!! csrf_field() !!}
			                			<div class="modal-header">
			                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			                				<h4 class="modal-title">Evaluate {{ @$value['name'] }}</h4>
			                			</div>
			                			<div class="modal-body">
			                				<div class="form-body">
                                        	    <div class="row">
                                        	       <div class="col-md-12">
                                                	    <table class="table dataTable table-striped table-bordered table-hover">
                                                	        <thead>
                                                	        <th>Questions</th>
                                                	        
                                                            @foreach($template->rate_values as $key=>$value)
                                                            <th>{{$value}}</th>
                                                            @endforeach
                                                            </thead>
                                                            <tbody>
                                                            
                                                            @foreach($template->rate_questions as $key=> $question)
                                                            <tr>
                                                                <td>{{ $question }}
                                                                    <input type="hidden" value="{{$question}}" name="question[{{$key}}]"/>
                                                                </td>
                                                                @foreach($template->rate_values as $key2=>$value2)
                                                                <td>
                                                                    <div class="radio-list">
					                                                    <label class="radio-inline">
                                                                        <input type="radio" {{ ($key2==0?'checked':'') }}
                                                                            id="optionsRadios1" name="answer[{{$key}}]" value="{{$value2}}">
                                                                        </label>
                                                                </td>
                                                                @endforeach
                                                            </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <br/><br/>
                                                @foreach($template->rate_texts as $key=>$value)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        {{ $value }}
                                                        <input type="hidden" value="{{$value}}" name="text_question[{{$key}}]"/>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <textarea name="text_answer[{{$key}}]" class="form-control"></textarea>
                                                    </div>
                                                </div>
                                                @endforeach
			                                </div>
			                			</div>
			                			<div class="modal-footer">
			                				<button type="submit" class="btn blue">Save</button>
			                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			                			</div>
			                			</form>
			                		</div><!-- /.modal-content -->
			                	</div><!-- /.modal-dialog -->
			                </div><!-- end of eval Modal-->
        	                
        	            </td>
        	         </tr>
        		    @endforeach
        			</tbody>
        		</table>
            </div>
        </div>
    </div>
    <div class="tab-pane fade in" id="tab_1_4">
        <div class="portlet box green">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Evaluation History
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
        	    <table id="sample_54" class="table dataTable table-striped table-bordered table-hover">
        	        <thead>
        			<tr>
        				<th>Employee</th>
        				<th>Date Evaluated</th>
        				<th>Evaluated By</th>
        				<th style="width:120px"></th>
        	        </tr>			
        			</thead>
        			<tbody> 
        			@foreach($evaluations as $value)
        			<tr>
        			    <td> {{ $value['employee_name'] }} </td>
        			    <td> {{ dateNormal($value['created_at']) }} </td>
        			    <td> {{ $value['evaluated_by_name'] }} </td>
        			    <td> 
                            <a data-toggle="modal" href="#modal_eval{{$value['id']}}" type="button" class="btn blue btn-xs">View</a>
			                <!-- Start of eval Modal-->
			                <div class="modal fade" id="modal_eval{{$value['id']}}" tabindex="-1" role="dialog" 
			                    aria-labelledby="myModalLabel" aria-hidden="true">
			                	<div class="modal-dialog modal-lg">
			                		<div class="modal-content">
			                			<div class="modal-header">
			                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			                				<h4 class="modal-title">Evaluation Details</h4>
			                			</div>
			                			<div class="modal-body">
			                				<div class="row">
			                				    <div class="col-md-6">
			                				        <label class="bold">Employee: {{ $value['employee_name'] }}</label>
			                				    </div>
			                				    <div class="col-md-6">
			                				        <label class="bold">Evaluation Date: {{ dateNormal($value['created_at']) }}</label>
			                				    </div>
			                				</div>
			                				<div class="row">
			                				    <div class="col-md-6">
			                				        <label class="bold">Evaluated by: {{ $value['evaluated_by_name'] }}</label>
			                				    </div>
			                				    <div class="col-md-6">
			                				        
			                				    </div>
			                				</div>
			                				<div class="row">
			                				    <div class="col-md-12">
    			                				    <table class="table dataTable table-striped table-bordered table-hover">
    		                				            <tbody>
    		                				                @foreach(json_decode($value['evaluation_data'])->items as $item)
    		                				                <tr>
    		                				                    <td>{{ $item[0] }}</td>
    		                				                    <td>{{ $item[1] }}</td>
    		                				                </tr>
    		                				                @endforeach
	                				                        @foreach(json_decode($value['evaluation_data'])->items_text as $item)
    		                				                <tr>
    		                				                    <td>{{ $item[0] }}</td>
    		                				                    <td>{{ $item[1] }}</td>
    		                				                </tr>
    		                				                @endforeach
    		                				            </tbody>
    		                				        </table>
		                				        </div>
			                				</div>
			                			</div>
			                			<div class="modal-footer">
			                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			                			</div>
			                			</form>
			                		</div><!-- /.modal-content -->
			                	</div><!-- /.modal-dialog -->
			                </div><!-- end of eval Modal-->
        			    </td>
        			</tr>
        			@endforeach
        			</tbody>
        		</table>
            </div>
        </div>
    </div>
    <div class="tab-pane fade in" id="tab_1_5">
        <div class="portlet box green">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Evaluation Template
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
        	    <div class="row">
        	       <div class="col-md-12">
                	    <table id="sample_56" class="table dataTable table-striped table-bordered table-hover">
                	        <thead>
                	        <th>Questions</th>
                	        
                            @foreach($template->rate_values as $value)
                            <th>{{$value}}</th>
                            @endforeach
                            </thead>
                            <tbody>
                            
                            @foreach($template->rate_questions as $question)
                            <tr>
                                <td>{{ $question }}</td>
                                @foreach($template->rate_values as $value2)
                                <td>&nbsp;</td>
                                @endforeach
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <br/><br/>
                @foreach($template->rate_texts as $value)
                <div class="row">
                    <div class="col-md-6">
                        {{ $value }}
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" readonly></textarea>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection