@extends('layouts.main')

@section('content')
<div class="note note-warning">
	<h4 class="block">Can't show Employee Profile!</h4>
	<p>
		It's either the requested ID not exists or your account has no permission to view this employee.
	</p>
</div>

<a href="{{ url('employees') }}" class="btn grey-cascade">Back</a>
@endsection