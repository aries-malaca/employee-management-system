@extends('layouts.main')
@section('content')

<div class="row">
    <div class="col-md-2">
        <ul class="nav nav-tabs tabs-left">
            <li class="active">
                <a href="#adjustments" data-toggle="tab">
                    Adjustments <span class="badge badge-success" id="adjustments_counter"></span>
                </a>
            </li>
            <li>
                <a href="#leaves" data-toggle="tab">
                    Leaves <span class="badge badge-success" id="leves_counter"></span>
                </a>
            </li>
            <li>
                <a href="#overtimes" data-toggle="tab">
                    Overtimes <span class="badge badge-success" id="overtimes_counter"></span>
                </a>
            </li>
            <li>
                <a href="#travels" data-toggle="tab">
                    Travel <span class="badge badge-success" id="travels_counter"></span>
                </a>
            </li>
            <li>
                <a href="#offsets" data-toggle="tab">
                    Offset <span class="badge badge-success" id="offsets_counter"></span>
                </a>
            </li>
            <li>
                <a href="#schedules" data-toggle="tab">
                    Schedule <span class="badge badge-success" id="schedules_counter"></span>
                </a>
            </li>
        </ul>
    </div>
    <div class="col-md-10" id="requests">
        <div class="tab-content">
            <div class="tab-pane active fade in" id="adjustments">
                @include('employee.requests.adjustment')
            </div>
            <div class="tab-pane fade" id="overtimes">
                @include('employee.requests.overtime')
            </div>
            <div class="tab-pane fade" id="leaves">
                @include('employee.requests.leave')
            </div>
            <div class="tab-pane fade" id="travels">
                @include('employee.requests.travel')
            </div>
            <div class="tab-pane fade" id="offsets">
                @include('employee.requests.offset')
            </div>
            <div class="tab-pane fade" id="schedules">
                @include('employee.requests.schedule')
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="approve_by_id" value="{{ Auth::user()->id }}">
<script src="../../assets/vuejs/instances/adjustments.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/leaves.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/travels.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/overtimes.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/schedules.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/offsets.js?cache={{ rand() }}"></script>
@endsection