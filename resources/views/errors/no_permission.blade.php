@extends('layouts.main')

@section('content')
<div class="note note-warning">
	<h4 class="block">Invalid Page!</h4>
	<p>
		You are not have access to this page.
	</p>
</div>

<a href="{{ url('home') }}" class="btn grey-cascade">Back to Home</a>
@endsection