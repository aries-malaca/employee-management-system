@extends('layouts.main')

@section('content')
<div class="row" id="contributions" v-cloak>
    <ul class="nav nav-tabs tabs-left col-md-2" >
    	<li class="active">
    		<a href="#tab_1_0" data-toggle="tab">
    		Tax Exemption </a>
    	</li>
    	<li class="">
    		<a href="#tab_1_1" data-toggle="tab">
    		Income Tax </a>
    	</li>
    	<li>
    		<a href="#tab_1_2" data-toggle="tab">
    		SSS </a>
    	</li>
    	<li>
    		<a href="#tab_1_3" data-toggle="tab">
    		PhilHealth </a>
    	</li>
    	<li>
    		<a href="#tab_1_4" data-toggle="tab">
    		Pagibig </a>
    	</li>
    </ul>
    <div class="tab-content col-md-10">
    	<div class="tab-pane fade active in" id="tab_1_0">
            @include('contributions.tax_exemption')
    	</div>
    	<div class="tab-pane fade" id="tab_1_1">
            @include('contributions.income_tax')
    	</div>
    	<div class="tab-pane fade" id="tab_1_2">
            @include('contributions.sss')
    	</div>
    	<div class="tab-pane fade" id="tab_1_3">
            @include('contributions.philhealth')
    	</div>
    	<div class="tab-pane fade" id="tab_1_4">
            @include('contributions.pagibig')
    	</div>
    </div>
</div>
<script src="../../assets/vuejs/instances/contributions.js?cache={{ rand() }}"></script>
@endsection