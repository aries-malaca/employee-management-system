<div class="row" >
    <div class="col-md-12">
        <div class="alert alert-info alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Note:</strong> Taxable Salary (Tax Amount), % of over defined each columns. <br/>
			<strong>Computation:</strong> Withholding Tax = (Tax amount based on this table) + (amount of over * % of over).
		</div>
    </div>
</div>
<div class="portlet box blue">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Income Tax Table (Semi-Monthly)
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
		</div>
	</div>
	<div class="portlet-body">
		<div class="scrollable">
			<table class="table table-striped table-bordered table-hover"  v-if="tax_exemptions.length>0">
				<thead>
				<tr>
					<th>Status</th>
					<th>20% over <br/>
						(@{{ format_number(tax_exemptions[0].tax_exemption_data.semimonthly[0][1]) }})
					</th>
					<th>25% over <br/>
						(@{{ format_number(tax_exemptions[0].tax_exemption_data.semimonthly[1][1]) }})
					</th>
					<th>30% over <br/>
						(@{{ format_number(tax_exemptions[0].tax_exemption_data.semimonthly[2][1]) }})
					</th>
					<th>32% over <br/>
						(@{{ format_number(tax_exemptions[0].tax_exemption_data.semimonthly[3][1]) }})
					</th>
					<th>35% over <br/>
						(@{{ format_number(tax_exemptions[0].tax_exemption_data.semimonthly[4][1]) }})
					</th>
				</tr>
				</thead>
				<tbody>
				<tr v-for="(tax_exemption,key) in tax_exemptions">
					<td>@{{ tax_exemption.tax_exemption_name }}</td>
					<td>@{{ format_number(tax_exemption.tax_exemption_data.semimonthly[0][0]) }} </td>
					<td>@{{ format_number(tax_exemption.tax_exemption_data.semimonthly[1][0]) }}</td>
					<td>@{{ format_number(tax_exemption.tax_exemption_data.semimonthly[2][0]) }}</td>
					<td>@{{ format_number(tax_exemption.tax_exemption_data.semimonthly[3][0]) }}</td>
					<td>@{{ format_number(tax_exemption.tax_exemption_data.semimonthly[4][0]) }}</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>