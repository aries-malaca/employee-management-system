<div class="portlet box blue">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Philhealth Contribution
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
				<th>From</th>
                <th>To</th>
                <th>Employee Share</th>
                <th>Employer Share</th>
	        </tr>			
			</thead>
			<tbody v-if="contributions.length>0">
                <tr v-for="(ph,key) in contributions[2].contribution_data.data">
                    <td>@{{ format_number(ph[0].from) }}</td>
					<td>@{{ format_number(ph[0].to) }}</td>
					<td>@{{ format_number(ph[0].employee) }}</td>
					<td>@{{ format_number(ph[0].employer)  }}</td>
                </tr>
			</tbody>
		</table>
	</div>
</div>
