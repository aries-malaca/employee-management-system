<div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
			<!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
			<!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
			<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
			<!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
			<!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
			<ul class="page-sidebar-menu" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
				<!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
				<li class="sidebar-toggler-wrapper">
					<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
					
				</li>
				<br/>	
				<br/>
				<li class="start @if(!isset($page)) active @endif">
					<a href="{{ url('home') }}">
					<i class="icon-home"></i>
					<span class="title">Home</span>
					 @if(!isset($page)) <span class="selected"></span> @endif
					</a>
				</li>
			
				@foreach($menus as $menu)
				<li class=
					"@if(isset($page)) 
							@if($page['title']== $menu['title'] ) 
								active 
							@endif 
						@endif
						@if(isset($page['parent']))
							@if($page['parent'] == $menu['title'])
								active open
							@endif
						@endif">
					<a href="{{ url($menu['url']) }}">
					<i class="{{$menu['icon']}}"></i>
					<span class="title">{{$menu['title']}}</span>
					
					@if(isset($page)) 
						@if($page['title']== $menu['title']) 
							<span class="selected"></span> 
						@endif 
					@endif
					
					@if($menu['has_sub'] == 1) 
						<span class="arrow"></span> 
						@if(isset($page['parent']))
							@if($page['parent'] == $menu['title'])
								<span class="selected"></span> 
							@endif
						@endif
					@endif
					</a>
					@if($menu['has_sub'] == 1)
						<ul class="sub-menu">
							@foreach($menu['subs'] as $sub)
								<li class="
									@if(isset($page))
										@if($sub['title'] == $page['title']) 
											active
										@endif
									@endif
								">
								<a href="{{ url($sub['url']) }}">{{ $sub['title'] }}</a></li>
							@endforeach
						</ul>
					@endif
				</li>
				@endforeach
				<li class="last">
					<a href="{{ url('auth/logout') }}">
					<i class="icon-key"></i>
					<span class="title">Log-out</span>
					</a>
				</li>
			</ul>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>