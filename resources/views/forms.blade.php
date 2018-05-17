@extends('layouts.main')
@section('content')

<div class="row">
	<div class="col-lg-12">
			<!--end navigation div -->
            <div class="alert alert-info">
                <b>
                    Please file your request immediately to ensure all your leaves/overtime credited in your payroll.
                </b>
            </div>
			<div class="row">
		        <div class="col-xs-12" >
                    <ul class="nav nav-tabs" id="auth" v-cloak>
                        <li class="active" v-if="auth.allow_adjustment == 1">
                            <a href="#adjustment_tab" data-toggle="tab">Time Adjustment</a>
                        </li>
                        <li v-if="auth.allow_overtime == 1">
                            <a href="#overtime_tab" data-toggle="tab">Overtime</a>
                        </li>
                        <li v-if="auth.allow_leave == 1">
                            <a href="#leave_tab" data-toggle="tab">Leave</a>
                        </li>
                        <li v-if="auth.allow_travel == 1">
                            <a href="#travel_tab" data-toggle="tab">Travel</a>
                        </li>
                        <li v-if="auth.allow_offset == 1 && 1==0">
                            <a href="#offset_tab" data-toggle="tab">Offset</a>
                        </li>
                        <li>
                            <a href="#schedules_tab" data-toggle="tab">Change Shift</a>
                        </li>
                        <li>
                            <a href="#salary_adjustment_tab" data-toggle="tab">
                                Salary Adjustment
                                <span class="badge badge-success">New!</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="adjustment_tab">
                            @include('forms.adjustment')
                        </div>
                        <div class="tab-pane fade" id="overtime_tab">
                            @include('forms.overtime')
                        </div>
                        <div class="tab-pane fade" id="travel_tab">
                            @include('forms.travel')
                        </div>
                        <div class="tab-pane fade" id="offset_tab">
                            @include('forms.offset')
                        </div>
                        <div class="tab-pane fade" id="leave_tab">
                            @include('forms.leave')
                        </div>
                        <div class="tab-pane fade" id="schedules_tab">
                            @include('forms.schedule')
                        </div>
                        <div class="tab-pane fade" id="salary_adjustment_tab">
                            @include('forms.salary_adjustment')
                        </div>
                    </div>
				</div>
			</div>
		    <!-- end tab contents -->
		</div>
	</div>
</div>
<input type="hidden" id="form-owner" value="{{Auth::user()->id}}">
<script src="../../assets/vuejs/instances/calendar.js?cache={{ rand() }}"></script>
@endsection