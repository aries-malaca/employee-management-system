<div class="portlet box red">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Pagibig Contribution
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<table class="table table-striped table-bordered table-hover">
	        <thead>
				<tr>
					<th>Status</th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody v-if="contributions.length>0">
				<tr>
					<td>Monthly</td>
					<td>@{{ format_number(contributions[3].contribution_data.monthly) }} </td>
				</tr>
				<tr>
					<td>Semi Monthly</td>
					<td>@{{ format_number(contributions[3].contribution_data.semimonthly) }} </td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
