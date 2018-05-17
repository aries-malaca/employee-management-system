<div class="page-header navbar navbar-fixed-top" id="header">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="/home">
			<img style="margin:3px 10px; height:38px;"
				src="../../images/app/{{$config['logo']}}" alt="logo" class="logo-default"/>
			</a>
			<div class="menu-toggler sidebar-toggler hide">
				<!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
			</div>
		</div>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		<!-- BEGIN TOP NAVIGATION MENU -->
		<div class="top-menu">
			<ul class="nav navbar-nav pull-right">
				<!-- BEGIN NOTIFICATION DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-extended">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
						<i class="fa fa-bell"></i>
                        <span class="badge badge-success" v-if="countPending>0" v-cloak>@{{ countPending }}</span>
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3> Notifications </h3>
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
								<li v-for="notification in notifications" >
                                    <a @mouseout="seenNotification(notification)" v-bind:style="notification.is_read==0?'background-color:#DDDDFF':''">
                                        <span class="subject">
                                            <span class="bold">@{{ notification.notification_title }}</span><br>
                                            <span class="time" v-bind:style="notification.is_read==0?'background-color:#DDDDFF':'background-color:white'"> @{{ formatDateTime(notification.created_at, 'MM/DD/YYYY LT') }} </span>
                                        </span>
                                        <span class="message" v-if="notification.notification_type == 'missing_attendance'">
                                            @{{ notification.notification_body }} for date: @{{ formatDateTime(notification.notification_data.date, "MM/DD/YYYY") }}
                                        </span>
										<span class="message" v-if="notification.notification_type == 'request_confirmation' || notification.notification_type == 'new_request' ">
                                            @{{ notification.notification_body }}
                                        </span>
                                    </a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<li v-show="employee_logs.length>0" class="dropdown dropdown-extended dropdown-notification">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
						<i class="fa fa-users"></i>
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3>My Employee Activities </h3>
							<a href="{{ url('/logs/myEmployeeLogs') }}">view all</a>
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
                                <li v-for="log in employee_logs">
                                    <a v-bind:href="'../../employee/'+log.log_by_id">
                                        <span class="subject">
                                            <span class="bold">@{{ log.name }}</span><br>
                                            <span class="time" style="background:white"> @{{ formatDateTime(log.created_at, 'MM/DD/YYYY LT') }} </span>
                                        </span>
                                        <span class="message">
                                            @{{ log.log_details }}
                                        </span>
                                    </a>
                                </li>
							</ul>
						</li>
					</ul>
				</li>
				<!-- END NOTIFICATION DROPDOWN -->

				<!-- BEGIN TODO DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-extended" id="header_task_bar">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="fa fa-list-ul"></i>
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3>My Latest Activities </h3>
							<a href="{{ url('/logs/myLogs') }}">view all</a>
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
								<li v-for="log in my_logs">
									<a href="#">
                                        <span class="subject">
                                            <span class="time" style="background:white"> @{{ formatDateTime(log.created_at, 'MM/DD/YYYY LT') }} </span>
                                        </span>
                                        <span class="message">
                                            @{{ log.log_details }}
                                        </span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<!-- END TODO DROPDOWN -->
				<!-- BEGIN USER LOGIN DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-user">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<img alt="" class="img-circle" src="{{ asset('images/employees/'.Auth::user()->picture)  }}"/>
					<span class="username username-hide-on-mobile">
					{{ Auth::user()->name }} </span>
					<i class="fa fa-angle-down"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-default">
						<li>
							<a href="{{ url('profile') }}">
							<i class="icon-user"></i> My Profile </a>
						</li>
						<li>
							<a href="{{ url('mail') }}">
							<i class="icon-envelope"></i> My Mails </a>
						</li>
						<li>
							<a href="{{ url('calendar') }}">
							<i class="icon-calendar"></i> My Calendar </a>
						</li>
						<li>
							<a href="{{ url('forms') }}">
							<i class="fa fa-paper-plane-o"></i> Employee Forms </a>
						</li>
						<li class="divider">
						</li>
						<li>
							<a href="{{ url('lockscreen') }}">
							<i class="icon-lock"></i> Lock Screen </a>
						</li>
						<li>
							<a href="{{ url('auth/logout') }}">
							<i class="icon-key"></i> Log Out </a>
						</li>
					</ul>
				</li>
				<!-- END USER LOGIN DROPDOWN -->
				<!-- BEGIN QUICK SIDEBAR TOGGLER -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				@if($config['enable_chat'] == 1)
				<li class="dropdown dropdown-quick-sidebar-toggler">
					<a href="javascript:;" class="dropdown-toggle">
					<i class="icon-bubble"></i>
					<span class="badge badge-default" id="unseen_count" style="display:none">
					0
					</span>
					</a>

				</li>
				@endif
				<!-- END QUICK SIDEBAR TOGGLER -->
			</ul>
		</div>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END HEADER INNER -->
</div>