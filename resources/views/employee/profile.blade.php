<!-- BEGIN FORM-->
<div class="form-body">
	<form autocomplete="off" onsubmit="return false">
		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					<label class="control-label bold">First Name</label>
					<input type="text" class="form-control" v-model="newEmployee.first_name" placeholder="First Name">
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-4">
				<div class="form-group">
					<label class="control-label bold">Middle Name</label>
					<input type="text" v-model="newEmployee.middle_name" class="form-control" placeholder="Middle Name">
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-4">
				<div class="form-group">
					<label class="control-label bold">Last Name</label>
					<input type="text" v-model="newEmployee.last_name"  class="form-control" placeholder="Last Name">
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->
		<div class="row">
			<div class="col-sm-3">
				<div class="form-group">
					<label class="control-label bold">Gender</label>
					<select v-bind:disabled="!newEmployee.view_salary" v-model="newEmployee.gender" class="form-control">
						<option value="male">Male</option>
						<option value="female">Female</option>
					</select>
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-3">
				<div class="form-group">
					<label class="control-label bold">Date of Birth</label>
					<input v-bind:disabled="!newEmployee.view_salary" class="form-control form-control-inline" v-model="newEmployee.birth_date" type="date" />
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-3" v-bind:disabled="!newEmployee.view_salary">
				<div class="form-group">
					<label class="control-label bold">Age</label>
					<input type="text" v-bind:value="getAge" class="form-control" readonly />
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-3">
				<div class="form-group">
					<label class="control-label bold">Civil Status</label>
					<select v-model="newEmployee.civil_status" class="form-control">
						<option value="single">Single</option>
						<option value="married">Married</option>
					</select>
				</div>
			</div>
			<!--/span-->
			<!--/span-->
			<div class="col-sm-3">
				<div class="form-group">
					<label class="control-label bold">Mobile</label>
					<input type="text" min="0"  v-model="newEmployee.mobile" class="form-control" />
				</div>
			</div>
			<!--/span-->
			<div class="col-sm-3">
				<div class="form-group">
					<label class="control-label bold">Telephone</label>
					<input type="text" min="0" v-model="newEmployee.telephone" class="form-control" />
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->
		<div class="row">
			<div class="col-md-8">
				<div class="form-group">
					<label class="control-label bold">Address</label>
					<input type="text" v-model="newEmployee.address" class="form-control" />
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label bold">Birth Place</label>
					<input type="text" v-model="newEmployee.birth_place" class="form-control" />
				</div>
			</div>
		</div>
		<!--/row-->
		<div class="row">
			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label bold">Town/City</label>
					<input type="text" v-model="newEmployee.city" class="form-control" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label bold">Province/State</label>
					<input type="text" v-model="newEmployee.state" class="form-control" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label bold">Country</label>
					<input type="text" v-model="newEmployee.country" class="form-control" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label bold">Zip Code</label>
					<input type="number" min="0" v-model="newEmployee.zip_code" class="form-control" />
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label bold">About @{{ newEmployee.first_name }} @{{ newEmployee.last_name }}</label>
					<textarea v-model="newEmployee.about" class="form-control"></textarea>
				</div>
			</div>
		</div>
		<!--/row-->
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label bold">Contact Person</label>
					<input v-model="newEmployee.contact_person" class="form-control" type="text">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label bold">Contact Info</label>
					<input v-model="newEmployee.contact_info" class="form-control" type="text">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label bold">Contact Relationship</label>
					<input v-model="newEmployee.contact_relationship" class="form-control" type="text">
				</div>
			</div>
		</div>
		<!--/row-->
	</form>
</div>
<div class="form-actions right">
	<button type="button" @click="updateProfile" class="btn blue"><i class="fa fa-check"></i> Update Profile</button>
</div>
<!-- END FORM-->