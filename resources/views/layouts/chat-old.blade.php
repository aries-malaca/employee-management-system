<a href="javascript:;" class="page-quick-sidebar-toggler"><i class="icon-close"></i></a>
<div class="page-quick-sidebar-wrapper" id="chat_div">
	<div class="page-quick-sidebar">
		<div class="nav-justified">
			<div class="tab-content">
				<div class="tab-pane active page-quick-sidebar-chat" id="quick_sidebar_tab_1">
					<div class="page-quick-sidebar-chat-users" data-rail-color="#ddd" data-wrapper-class="page-quick-sidebar-list">
						<ul class="media-list list-items">
						@foreach($chat_employees as $key => $value)
							@if($value['user_id'] != Auth::user()->id)
							<li class="media" data-id="{{$value->user_id}}"
							onclick="toggleChat({{$value->user_id}});">
								<img class="media-object" src="{{ asset('images/employees/'. $value['picture'] ) }}" alt="...">
								<div class="media-body">
									<h4 class="media-heading">{{$value->name}}</h4>
									<div class="media-heading-sub">
										 {{$value->position_name}}
										 <div class="pull-right">
											 @if( (time()-strtotime($value->last_activity)) < 120 )
											 <span class="badge badge-success">Online</span>
											 @endif
										 </div>
										 <span style="display:none">
										 	<i class="fa fa-envelope-o"></i>
										 	<span class="badge badge-success">0</span>
										 </span>
									</div>
								</div>
							</li>
							@endif
						@endforeach
						@foreach($chat_supervisors as $key => $value)
							@if($value['user_id'] != Auth::user()->id)
							<li class="media" data-id="{{$value->user_id}}"
							onclick="toggleChat({{$value->user_id}});">
								<img class="media-object" src="{{ asset('images/employees/'. $value['picture'] ) }}" alt="...">
								<div class="media-body">
									<h4 class="media-heading">{{$value->name}}</h4>
									<div class="media-heading-sub">
										 {{$value->position_name}}
										 <div class="pull-right">
											 @if( (time()-strtotime($value->last_activity)) < 120 )
											 <span class="badge badge-success">Online</span>
											 @endif
										 </div>
										 <span style="display:none">
										 	<i class="fa fa-envelope-o"></i>
										 	<span class="badge badge-success">0</span>
										 </span>
									</div>
								</div>
							</li>
							@endif
						@endforeach
						</ul>
					</div>
					@foreach($chat_employees as $key => $value)
					<div class="page-quick-sidebar-item chat-convo" data-id="{{$value->user_id}}">
						<div class="page-quick-sidebar-chat-user">
							<div class="page-quick-sidebar-nav">
							<a onclick="back({{$value->user_id}})" 
								class="page-quick-sidebar-back-to-list"><i class="icon-arrow-left"></i>Back</a> &nbsp;&nbsp;&nbsp;
							<a href="{{url('employee/'. $value->user_id)}}">
							<img style="height: 26px" class="img" src="{{ asset('images/employees/'. $value->picture ) }}" alt="..."> {{$value->name}}
							 </a>
							</div>
							<div class="page-quick-sidebar-chat-user-messages" data-limit="10" id="conversation_id{{ $value->user_id }}">
								
							</div>
							<div class="page-quick-sidebar-chat-user-form">
								<div class="input-group">
									<input onkeypress="send(this,event)" data-sender="{{Auth::user()->id}}" data-receiver="{{$value->user_id}}" 
											type="text" class="form-control" placeholder="Type a message here...">
									<div class="input-group-btn">
										<button type="button"  onclick="send(this)"class="btn blue"><i class="fa fa-send"></i></button>
									</div>
								</div>
							</div>
						</div>
					</div>
					@endforeach
					@foreach($chat_supervisors as $key => $value)
					<div class="page-quick-sidebar-item chat-convo" data-id="{{$value->user_id}}">
						<div class="page-quick-sidebar-chat-user">
							<div class="page-quick-sidebar-nav">
							<a onclick="back({{$value->user_id}})" 
								class="page-quick-sidebar-back-to-list"><i class="icon-arrow-left"></i>Back</a> &nbsp;&nbsp;&nbsp;
							<a href="{{url('employee/'. $value->user_id)}}">
							<img style="height: 26px" class="img" src="{{ asset('images/employees/'. $value->picture ) }}" alt="..."> {{$value->name}}
							 </a>
							</div>
							<div class="page-quick-sidebar-chat-user-messages" data-limit="10" id="conversation_id{{ $value->user_id }}">
								
							</div>
							<div class="page-quick-sidebar-chat-user-form">
								<div class="input-group">
									<input onkeypress="send(this,event)" data-sender="{{Auth::user()->id}}" data-receiver="{{$value->user_id}}" 
											type="text" class="form-control" placeholder="Type a message here...">
									<div class="input-group-btn">
										<button type="button"  onclick="send(this)"class="btn blue"><i class="fa fa-send"></i></button>
									</div>
								</div>
							</div>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
<script type="text/javascript" src="../../assets/scripts/chat.js"></script>