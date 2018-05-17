@if(sizeof($mails)>0)
	@include('email_client.tools')
	<div class="table-responsive">

		<table class="table table-striped table-advance table-hover" id="sample_5">
			<thead>
			<tr>
				<th>
					
				</th>	
				<th>Date</th>
				<th><i class="fa fa-star"></i></th>
				<th><i class="fa fa-eye"></i></th>
				<th>To</th>
				<th>Subject</th>
			</tr>
			</thead>
			<tbody>
				@foreach($mails as $key=> $mail)
				<tr data-messageid="{{$mail['id']}}" data-folder="Draft" {{ ($mail['flag']['seen']==0? 'class=unread':'') }}>
					<td class="inbox-small-cells">
						<input type="checkbox" class="mail-checkbox">
					</td>
					<td class="view-message text-right">
						 {{ datetimeNormal($mail['date']) }}
					</td>
					<td class="inbox-small-cells">
						<i class="fa fa-star {{ ($mail['flag']['flagged']==1? 'inbox-started':'') }} "></i>
						{{ ($mail['flag']['flagged']==1? '&nbsp;':'') }}
					</td>
					<td class="view-message inbox-small-cells">
						{!! ($mail['flag']['seen']==1? '<i class="fa fa-eye"></i>':'') !!}
					</td>
					<td class="view-message">
						{{$mail['to']}}
					</td>
					<td class="view-message">
						 {{ $mail['subject'] }}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@include('email_client.pagination')
@else
	<div class="alert alert-warning">
		<strong>Warning!</strong> {{Request::input('folder')}} is Empty.
	</div>
@endif