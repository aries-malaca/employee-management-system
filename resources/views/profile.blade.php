@extends('layouts.main')
@section('content')
<link href="../../metronic/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/admin/pages/css/profile.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>

<div class="row" id="profile" v-cloak>
    <div class="col-md-12">
    	<!-- BEGIN PROFILE SIDEBAR -->
    	<div class="profile-sidebar">
    		<!-- PORTLET MAIN -->
    		<div class="portlet light profile-sidebar-portlet">
    			<!-- SIDEBAR USERPIC -->
    			<div class="profile-userpic">
    				<img v-bind:src="'images/employees/'+profile.picture" class="img-responsive" alt="">
    			</div>
    			<!-- END SIDEBAR USERPIC -->
    			<!-- SIDEBAR USER TITLE -->
    			<div class="profile-usertitle" style="margin-top: 10px;">
    				<a data-toggle="modal" href="#portlet-config" class="btn btn-xs btn-info"><i class="icon-refresh"></i> Change picture</a>
    				<br/><br/>
    				<div class="profile-usertitle-name">
    					 @{{ profile.name}}
    				</div>
    				<div class="profile-usertitle-job">
    				    @{{ profile.position_name }}
    				</div>
    			</div>
    			<!-- END SIDEBAR USER TITLE -->
    			<!-- SIDEBAR MENU -->
    			<div class="profile-usermenu">
    			    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-login"></i> 
                        Employee No: @{{ profile.employee_no }}
                    </div>
					<div class="margin-top-10 profile-usertitle-job">
						<i class="icon-login"></i>
						Birthdate: @{{ moment(profile.birth_date).format("MM/DD/YYYY") }}
					</div>
    			    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-login"></i> 
                        Department: @{{ profile.department_name }}
                    </div>
    			    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-login"></i> 
                        Company: @{{ profile.company_name }}
                    </div>
					<div class="margin-top-10 profile-usertitle-job">
						<i class="icon-login"></i>
						Employment Status: @{{ profile.employment_status_name }}
					</div>
                    <div class="profile-usertitle">
    				    <a data-toggle="modal" href="#password-modal" class="btn red"><i class="icon-key"></i> Change Password</a>
    				</div>
                    
                    
    			</div>
    			<!-- END MENU -->
    		</div>
    		<!-- END PORTLET MAIN -->
    	</div>
    	<!-- END BEGIN PROFILE SIDEBAR -->
    	<!-- BEGIN PROFILE CONTENT -->
    	<div  id="navbar-example2" style="border:0px;" class="navbar navbar-default navbar-static profile-content" role="navigation">
    		<div class="row">
                <div class="tab-content col-xs-12" >
					<div class="tab-pane fade active in" id="profile_tab">
                        <div class="form-body">
                        	<div class="row">
                        		<div class="col-sm-4">
                        			<div class="form-group">
                        				<label class="control-label bold">First Name</label>
                        				<input type="text" class="form-control" v-model="profile.first_name" placeholder="First Name">
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-sm-4">
                        			<div class="form-group">
                        				<label class="control-label bold">Middle Name</label>
                        				<input type="text" v-model="profile.middle_name" class="form-control"  placeholder="Middle Name">
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-sm-4">
                        			<div class="form-group">
                        				<label class="control-label bold">Last Name</label>
                        				<input type="text" v-model="profile.last_name" class="form-control" placeholder="Last Name">
                        			</div>
                        		</div>
                        		<!--/span-->
                        	</div>
                        	<!--/row-->
                        	<div class="row">
								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label bold">Civil Status</label>
										<select v-model="profile.civil_status" class="form-control">
											<option value="single">Single</option>
											<option value="married">Married</option>
										</select>
									</div>
								</div>
								<!--/span-->
                        		<div class="col-sm-3">
                        			<div class="form-group">
                        			    <label class="control-label bold">Email Address</label>
                        				<input type="email" v-model="profile.email" class="form-control" />
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-sm-3">
                        			<div class="form-group">
                        			    <label class="control-label bold">Mobile</label>
                        				<input type="text" min="0" v-model="profile.mobile" class="form-control" />
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-sm-3">
                        			<div class="form-group">
                        			    <label class="control-label bold">Telephone</label>
                        				<input type="text" min="0" v-model="profile.telephone" class="form-control" />
                        			</div>
                        		</div>
                        		<!--/span-->
                        	</div>
                        	<!--/row-->
                        	<div class="row">
                        		<div class="col-md-8">
                        			<div class="form-group">
                        				<label class="control-label bold">Address</label>
                        				<input type="text" v-model="profile.address" class="form-control"/>
                        			</div>
                        		</div>
                        		<div class="col-md-4">
                        			<div class="form-group">
                        				<label class="control-label bold">Birth Place</label>
                        				<input type="text" v-model="profile.birth_place" class="form-control"/>
                        			</div>
                        		</div>
                        	</div>
                        	<!--/row-->
                        	<div class="row">
                        		<div class="col-md-3">
                        			<div class="form-group">
                        				<label class="control-label bold">Town/City</label>
                        				<input type="text" v-model="profile.city" class="form-control" />
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-md-3">
                        			<div class="form-group">
                        				<label class="control-label bold">Province/State</label>
                        				<input type="text" v-model="profile.state" class="form-control"/>
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-md-3">
                        			<div class="form-group">
                        				<label class="control-label bold">Country</label>
                        				<input type="text" v-model="profile.country" class="form-control"/>
                        			</div>
                        		</div>
                        		<!--/span-->
                        		<div class="col-md-3">
                        			<div class="form-group">
                        				<label class="control-label bold">Zip Code</label>
                        				<input type="number" min="0" v-model="profile.zip_code" class="form-control"/>
                        			</div>
                        		</div>
                        		<!--/span-->
                        	</div>
                        	<!--/row-->
                        	<div class="row">
                        		<div class="col-md-12">
                        			<div class="form-group">
                        				<label class="control-label bold">About @{{ profile.name }}</label>
                        				<textarea class="form-control" v-model="profile.about"></textarea>
                        			</div>
                        		</div>
                        	</div>
                        	<!--/row-->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label bold">Contact Person</label>
                                        <input type="text" v-model="profile.contact_person" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label bold">Contact Info</label>
                                        <input type="text" v-model="profile.contact_info" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label bold">Contact Relationship</label>
                                        <input type="text" v-model="profile.contact_relationship" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <!--/row-->
							<div class="row">
								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label bold">SSS</label>
										<input disabled type="text"  v-model="profile.sss_no" class="form-control" />
									</div>
								</div>
								<!--/span-->
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Philhealth</label>
                                        <input disabled type="text"  v-model="profile.philhealth_no" class="form-control" />
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Pagibig</label>
                                        <input disabled type="text"  v-model="profile.pagibig_no" class="form-control" />
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label bold">TIN</label>
                                        <input disabled type="text"  v-model="profile.tin_no" class="form-control" />
                                    </div>
                                </div>
                                <!--/span-->
							</div>
							<!--/row-->
                        </div>
                        <div class="form-actions right">
                            <button type="button" @click="getData" class="btn yellow"><i class="icon-refresh"></i> Reset </button>
                        	<button type="button" @click="updateProfile" class="btn blue"><i class="fa fa-check"></i> Update Profile</button>
                        </div>
                        <!-- END FORM-->
					</div>
				</div>
    		</div>
    	    <!-- end tab contents -->
    	</div>
    	<!-- END PROFILE CONTENT -->
    </div>

	<!-- Start of fileupload Modal-->
	<div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">File Upload</h4>
				</div>
				<div class="modal-body">
					<div class="form-body">
						<form action="{{ url('profile/uploadProfilePicture') }}" method="post" enctype="multipart/form-data">
							<label>Select image to upload:</label>
							<input type="file" name="file" id="file"><br/>
							<input type="hidden" name="id" v-bind:value="profile.id"/>
							<input type="submit" class="btn btn-sm btn-success" value="Upload" name="submit">
							{!! csrf_field() !!}
						</form>
					</div>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
	<!-- end fileupload Modal-->


	<!-- Start of password Modal-->
	<div class="modal fade" id="password-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">Change Password</h4>
				</div>
				<div class="modal-body">
					<div class="form-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label bold">Old Password</label>
                                        <input type="password" required autocomplete="off" class="form-control" v-model="password.old_password"/>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label bold">New Password</label>
                                        <input type="password" required autocomplete="off" v-model="password.password" class="form-control" />
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label bold">Re-enter New Password</label>
                                        <input type="password" required autocomplete="off" v-model="password.password2" class="form-control" />
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                        </div>
                        <!--/form body-->
                        <div class="form-actions right">
                            <button @click="updatePassword" type="button" class="btn blue"><i class="fa fa-check"></i> Update Password</button>
                        </div>
                    <!-- END FORM-->
					</div>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
	<!-- end password Modal-->
</div>
<script src="../../assets/vuejs/instances/profile.js?cache={{ rand() }}"></script>
@endsection