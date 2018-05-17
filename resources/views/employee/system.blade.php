<!-- BEGIN FORM-->
<div class="form-body">
	<form autocomplete="off" onsubmit="return false">
		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					<label class="control-label bold">Email Address</label>
					<input type="email"  v-model="newEmployee.email" class="form-control" />
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label class="control-label bold">Password</label>
					<input type="password" autocomplete="off" class="form-control" v-model="newEmployee.password" placeholder="Unchanged">
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-4" v-show="newEmployee.view_salary">
				<div class="form-group">
					<label class="control-label bold">Access Level</label>
					<select v-model="newEmployee.level" required class="form-control">
						<option v-bind:value="level.id" v-for="level in levels">@{{ level.level_name }}</option>
					</select>
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->

		<div class="row" v-show="newEmployee.view_salary">
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label bold">Biometric ID</label>
					<input type="number" v-model="newEmployee.biometric_no" class="form-control">
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label bold">Biometric ID 2</label>
					<input type="number" v-model="newEmployee.trainee_biometric_no" class="form-control" />
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label class="control-label bold">Allow Suspension</label>
					<select v-model="newEmployee.allow_suspension" class="form-control">
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-2">
				<div class="form-group">
					<label class="control-label bold">Receive Notif.</label>
					<select v-model="newEmployee.receive_notification" class="form-control">
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-2">
				<div class="form-group">
					<label class="control-label bold">Allow Access</label>
					<select class="form-control" v-model="newEmployee.allow_access">
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->
	</form>
</div>
<!--/form body-->

<div class="form-actions right">
	<button type="button" @click="updateSystem" class="btn blue"><i class="fa fa-check"></i> Update System Access</button>
</div>
<!-- END FORM-->