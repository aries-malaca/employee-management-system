<div class="row">
	<div class="col-md-6">
		<input type="hidden" value="Draft" id="{{Request::segment(3)}}"/>
		<a href="javascript:;" id="delete" class="btn btn-sm red">
			<i class="fa fa-trash-o"></i> Delete 
		</a>
		<div class="btn-group">
			<a class="btn btn-sm blue dropdown-toggle" href="#" data-toggle="dropdown">
			Actions <i class="fa fa-angle-down"></i>
			</a>
			<ul class="dropdown-menu">
				<li>
					<a href="javascript:;" id="mark">
					<i class="fa fa-check-square-o"></i> Mark All </a>
				</li>
				<li>
					<a href="javascript:;" id="unmark">
					<i class="fa fa-square-o"></i> Unmark All </a>
				</li>
				<li>
					<a href="javascript:;" id="read">
					<i class="fa fa-eye"></i> Mark as Read</a>
				</li>
				<li>
					<a href="javascript:;" id="unread">
					<i class="fa fa-eye-slash"></i> Mark as Unread</a>
				</li>
				<li>
					<a href="javascript:;" id="important">
					<i class="fa fa-star"></i> Mark as Important</a>
				</li>
			</ul>
		</div>
		<div class="btn-group">
			<a class="btn btn-sm green dropdown-toggle" href="#" data-toggle="dropdown">
			Move <i class="fa fa-angle-down"></i>
			</a>
			<ul class="dropdown-menu">
				@if(Request::segment(3) != 'Inbox')
				<li>
					<a href="javascript:;" id="moveinbox">
					 Inbox </a>
				</li>
				@endif
				@if(Request::segment(3) != 'Sent')
				<li>
					<a href="javascript:;" id="movesent">
					 Sent </a>
				</li>
				@endif
				@if(Request::segment(3) != 'Draft')
				<li>
					<a href="javascript:;" id="movedraft">
					 Drafts</a>
				</li>
				@endif
				@if(Request::segment(3) != 'Trash')
				<li>
					<a href="javascript:;" id="movetrash">
					 Trash</a>
				</li>
				@endif
			</ul>
		</div>
	</div>
	<div class="col-md-6">

	</div>
</div>