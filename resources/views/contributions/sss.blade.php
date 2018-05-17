<div class="portlet box yellow">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>SSS Contribution
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<div class="scrollable">
			<table class="table table-striped table-bordered table-hover">
				<thead>
				<tr>
					<th>From</th>
					<th>To</th>
					<th>Salary Credit</th>
					<th>Employee Share</th>
					<th>ER</th>
					<th>EC</th>
					<th>Employer Share</th>
					<th>Total</th>
				</tr>
				</thead>
				<tbody v-if="contributions.length>0">
				<tr v-for="(sss,key) in contributions[0].contribution_data.data">
					<td>@{{ format_number(sss[0].from) }}</td>
					<td>@{{ format_number(sss[0].to) }}</td>
					<td>@{{ format_number(sss[0].credit) }}</td>
					<td>@{{ format_number(sss[0].employee) }}</td>
					<td>@{{ format_number(sss[0].employer - sss[0].ec) }}</td>
					<td>@{{ format_number(sss[0].ec) }}</td>
					<td>@{{ format_number(sss[0].employer) }}</td>
					<td>@{{ format_number(sss[0].employer +  sss[0].employee) }}</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>