<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Tax Exemption
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
		</div>
	</div>
	<div class="portlet-body">
		<table class="table dataTable table-striped table-bordered table-hover">
	        <thead>
				<tr>
					<th style="width:120px">ID</th>
					<th>Exeption Name</th>
					<th style="width:120px"></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(tax_exemption,key) in tax_exemptions">
					<td>@{{ tax_exemption.id }}</td>
					<td>@{{ tax_exemption.tax_exemption_name }}</td>
					<td>
						<a @click="editTaxExemption(tax_exemption)" type="button" class="btn blue btn-xs">Edit</a>
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Start of add tax_exemption Modal-->
		<div class="modal fade" id="tax-exemption-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						<h4 class="modal-title">Update Tax Exemption</h4>
					</div>
					<div class="modal-body">
						<div class="form-body">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<label class="control-label bold">Tax Exemption Name</label>
										<input type="text" v-model="setTaxExemption.tax_exemption_name" class="form-control">
									</div>
								</div>
								<!--/span-->
							</div>
							<!--/row-->
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" @click="updateTaxExemption" class="btn blue">Save</button>
						<button type="button" class="btn default" data-dismiss="modal">Close</button>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<!-- end of add tax_exemption Modal-->
	</div>
</div>