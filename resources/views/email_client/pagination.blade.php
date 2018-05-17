<div class="row">
	<div class="col-sm-12">
		Showing Page {{Request::input('current_page')}} of {{$pages_count}}, Total rows: {{ $all_mails_count }}
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<ul class="pagination">
			<li class="{{1 == Request::input('current_page') ? 'disabled':''}}">
				<a href="javascript:;"
				@if(1 != Request::input('current_page'))
				 onclick="getMails('{{Request::input('folder')}}',1,'date','desc')"
				@endif
				>First </a>
			</li>

			<li class="{{1 == Request::input('current_page') ? 'disabled':''}}">
				<a href="javascript:;" 
				@if(1 != Request::input('current_page'))
					onclick="getMails('{{Request::input('folder')}}',{{Request::input('current_page') - 1}},'date','desc')"
				@endif
				>Previous </a>
			</li>

			@for($x=1; $x <= $pages_count; $x++)


				@if( (Request::input('current_page')-$x) > 6)
					@continue;
				@endif


				@if( Request::input('current_page') < ($x-6) )
					@continue;
				@endif


				<li class="{{($x == Request::input('current_page') ? 'disabled':'')}}">
					<a href="javascript:;"
					@if($x != Request::input('current_page'))
					 onclick="getMails('{{Request::input('folder')}}',{{$x}},'date','desc')"
					@endif
					>{{$x}} </a>
				</li>
			@endfor

			<li class="{{$pages_count == Request::input('current_page') ? 'disabled':''}}">
				<a href="javascript:;" 
				@if($pages_count != Request::input('current_page'))
					onclick="getMails('{{Request::input('folder')}}',{{Request::input('current_page') + 1}},'date','desc')"
				@endif
				>Next </a>
			</li>

			<li class="{{$pages_count == Request::input('current_page') ? 'disabled':''}}">
				<a href="javascript:;"
				@if($pages_count != Request::input('current_page'))
				 onclick="getMails('{{Request::input('folder')}}',{{$pages_count}},'date','desc')"
				@endif
				>Last </a>
			</li>

		</ul>
	</div>
</div>
