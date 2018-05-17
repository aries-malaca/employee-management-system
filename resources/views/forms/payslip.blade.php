<div class="row">
     <div class="col-md-12">
        <div class="portlet box blue-madison">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> Payslip
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
				<div class="alert alert-info">
					<b>Info:</b> You may file Salary Adjustment if you had unpaid Day or Leave or Overtime. Go Employee Forms or click: <a href="../../forms#salary_adjustment_tab">This Link</a>
				</div>
				@include('pagination.header_0')
				<div class="scrollable">
					<table class="table table-striped table-bordered table-hover">
						<thead>
						<tr>
							<th>Date Generated</th>
							<th>Pay Period</th>
							<th style="width:140px"></th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="payslip in filtered">
							<td>
								@{{ formatDateTime(payslip.created_at,"MM/DD/YYYY LT") }}
							</td>
							<td>
								@{{ formatDateTime(payslip.date_start,"MM/DD/YYYY") }} - @{{ formatDateTime(payslip.date_end,"MM/DD/YYYY") }}
							</td>
							<td>
								<a v-bind:href="'../../payroll/previewSingle/' + payslip.id"
								   class="btn btn-xs btn-info" target="_blank">View</a>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				@include('pagination.footer_0')
            </div>
        </div>
    </div>
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i> Transactions
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse" data-original-title="" title="">
					</a>
				</div>
			</div>
			<div class="portlet-body">
				@include('pagination.header_1')
				<label> Filter By:
					<select v-model="filter_by" class="form-control">
						<option value="active-recurring">Active - Recurring</option>
						<option value="active-nonrecurring">Active - Non-Recurring</option>
						<option value="inactive-recurring">Inactive - Recurring</option>
						<option value="inactive-nonrecurring">Inactive - Non-Recurring</option>
					</select>
				</label>
				<div class="scrollable">
					<table class="table table-striped table-bordered table-hover">
						<thead>
						<tr>
							<th>Transaction</th>
							<th>Start</th>
							<th>End</th>
							<th>Recurring Amt.</th>
							<th>Gives</th>
							<th>Total Amt.</th>
							<th>Frequency</th>
							<th>Notes</th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="transaction in filtered1">
							<td>@{{ transaction.transaction_name }}</td>
							<td>@{{ formatDateTime(transaction.start_date,'MM/DD/YYYY') }}</td>
							<td>@{{ formatDateTime(transaction.end_date,'MM/DD/YYYY') }}</td>
							<td>@{{ format_number(transaction.amount) }}</td>
							<td>@{{ getTransactionTimes(transaction) }}</td>
							<td>@{{ format_number(getTransactionTimes(transaction) * transaction.amount) }}</td>
							<td>
								@{{ transaction.frequency + ' ('+ transaction.cutoff +')' }}
							</td>
							<td>@{{ transaction.notes }}</td>
						</tr>
						</tbody>
					</table>
				</div>
				@include('pagination.footer_1')
			</div>
		</div>
	</div>
</div>