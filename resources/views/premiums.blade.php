@extends('layouts.main')
@section('content')
<ul class="nav nav-tabs" >
	<li class="active">
		<a href="#tab_1_4" data-toggle="tab">
		Premiums Settings</a>
	</li>
</ul>
<div class="tab-content" id="premiums">
	<div class="tab-pane fade active in" id="tab_1_4">
        <div class="portlet box yellow col-md-6">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Premium Settings
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>
        		</div>
        	</div>
        	<div class="portlet-body">
        		<div class="row">
        			<div class="col-md-6">
						<div class="form-group">
							<label class="control-label bold">Regular Overtime Rate</label>
							<input type="number" v-model="setPremium.regular_overtime" class="form-control"/>
						</div>
						<div class="form-group">
							<label class="control-label bold">Restday Overtime Rate</label>
							<input type="number" v-model="setPremium.restday_overtime" class="form-control"/>
						</div>
						<div class="form-group">
							<label class="control-label bold">Restday Beyond 8 hours Rate</label>
							<input type="number" v-model="setPremium.restday_beyond_overtime" class="form-control"/>
						</div>
						<div class="form-group">
							<label class="control-label bold">Regular Night Diff. Rate</label>
							<input type="number" v-model="setPremium.regular_nightdiff" class="form-control"/>
						</div>
						<div class="form-group">
							<label class="control-label bold">Restday Night Diff. Rate</label>
							<input type="number" v-model="setPremium.restday_nightdiff" class="form-control"/>
						</div>
						<button type="button" @click="updatePremiumSettings" class="btn btn-success"> Update </button>
        			</div>
        		</div>
        	</div>
        </div>
        <!-- End portlet-->
	</div>
	<!-- End tab-->
</div>
<script src="../../assets/vuejs/instances/premiums.js?cache={{ rand() }}"></script>
@endsection