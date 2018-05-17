<div class="inbox-header" data-messageid="{{$message->id}}" data-folder="{{ ucfirst(strtolower($message->selectedFolder)) }}">
	<span>
		<input type="checkbox" class="mail-checkbox" checked style="display:none">
	</span>
	<h2 class="pull-left"><button onclick="getMails('{{ ucfirst(strtolower($message->selectedFolder)) }}',$('#current_page').val(),'date','desc')" class="btn btn-circle default"><i class="fa fa-chevron-left"></i> {{ $message->selectedFolder }}</button> {{$message->subject}}
	</h2> 
</div>
<div class="inbox-view-info">
	<div class="row">
		<div class="col-md-7">
			From: {!! ($message->fromName !==null? '<span class="bold">'.$message->fromName.'</span>':'') !!} <span> &#60;{{$message->fromAddress}}&#62;</span> <br/>
			To:
			<span class="bold">
				@foreach($message->to as $key=>$value)
					@if(json_decode(Auth::user()->email_client_data)->email == $key)
						ME,
					@else
						{{ ($value!==null? $value.'<'.$key.'>':$key )}},
					@endif
				@endforeach
			</span> <br/>
			@if(sizeof($message->cc)>0)
			CC:
			<span class="bold">
				@foreach($message->cc as $key=>$value)
					@if(json_decode(Auth::user()->email_client_data)->email == $key)
						ME,
					@else
						{{ ($value!==null? $value.'<'.$key.'>':$key )}},
					@endif
				@endforeach
			</span> <br/>
			@endif
			@if(sizeof($message->bcc)>0)
			BCC:
			<span class="bold">
				@foreach($message->bcc as $key=>$value)
					@if(json_decode(Auth::user()->email_client_data)->email == $key)
						ME,
					@else
						{{ ($value!==null? $value.'<'.$key.'>':$key )}},
					@endif
				@endforeach
			</span> <br/>
			@endif
			Time: {{datetimeNormal($message->date)}}
		</div>
		<div class="col-md-5 inbox-info-btn">
			<div class="btn-group">
				@if($message->selectedFolder!='SENT')
					<button class="btn blue reply-btn" onclick="showReply('{{$message->fromAddress}}','{{$message->subject}}')">
					<i class="fa fa-reply"></i> Reply 
				    </button>
				@else
					<button class="btn blue reply-btn" onclick="showForward('{{$message->subject}}')">
					<i class="fa fa-arrow-right"></i> Forward 
				    </button>
				@endif
				<button class="btn blue dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-angle-down"></i>
				</button>
				<ul class="dropdown-menu pull-right">
					@if($message->selectedFolder!='SENT')
					<li>
						<a href="javascript:;" onclick="showReply('{{$message->fromAddress}}','{{$message->subject}}')" class="reply-btn">
						<i class="fa fa-reply"></i> Reply </a>
					</li>
					@endif
					<li>
						<a href="javascript:;" onclick="showForward('{{$message->subject}}')" class="forward-btn">
						<i class="fa fa-arrow-right"></i> Forward </a>
					</li>
					<li>
						<a href="javascript:;" id="delete">
						<i class="fa fa-trash-o"></i> Delete </a>
					</li>
					<li>
					</div>
				</div>
			</div>
		</div>
		<div class="inbox-view">
			@if(isset($message->textHtml) )
				<iframe id="the_html" style="width:100%;height:450px;" srcdoc='{!! str_replace("'","&apos;",$message->textHtml) !!}'>
				</iframe>
			@else
				<div id="the_plain">
				{!! trim(str_replace('\r\n', '<br/>',str_replace('>','&gt;',str_replace('<','&lt;',json_encode($message->textPlain)))),'"') !!}
				</div>
			@endif
		</div>

		@if(sizeof($message->getAttachments()))
		<hr>
		<div class="inbox-attached">
			<div class="margin-bottom-15">
				<span>
				{{sizeof($message->getAttachments())}} Attachment(s)
				</span>
			</div>

			@foreach($message->getAttachments() as $key=>$value)
			<div class="margin-bottom-25">
				<!--img src="../../assets/admin/pages/media/gallery/image4.jpg" -->
				<div>
					<strong>{{ $value->name }}</strong>
					<a target="_blank" href="{{ url('mailboxes/'.json_decode(Auth::user()->email_client_data)->email .'/'.$message->selectedFolder.'/' .basename($value->filePath)) }}"> Download </a>
				</div>
			</div>
			@endforeach
		</div>
		@endif
	</div>
</div>