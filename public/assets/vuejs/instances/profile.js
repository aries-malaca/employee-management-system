var vue_profile = new Vue({
    el:"#profile",
    data:{
        profile:{
            id:0,
            employee_no:'',
            name:'',
            first_name:'',
            middle_name:'',
            last_name:'',
            gender:'male',
            birth_date:'2000-01-01',
            civil_status:'single',
            email:'',
            mobile:'',
            telephone:'',
            address:'',
            birth_place:'',
            city:'',
            state:'',
            country:'',
            zip_code:'',
            about:'',
            contact_person:'',
            contact_info:'',
            contact_relationship:'',
            picture:'',
            position_name:'',
            department_name:'',
            company_name:'',
            sss_no:'',
            pagibig_no:'',
            tin_no:'',
            philhealth_no:'',
            age:0
        },
        password:{
            old_password:'',
            password:'',
            password2:''
        }
    },
    methods:{
        getData:function(){
            $.get('../../employees/getEmployee/' + $("#my_id").val(), function(data){
                vue_profile.profile = {
                    id:data.user_id,
                    employee_no:data.employee_no,
                    name:data.name,
                    first_name:data.first_name,
                    middle_name:data.middle_name,
                    last_name:data.last_name,
                    gender:data.gender,
                    birth_date:data.birth_date,
                    civil_status:data.civil_status,
                    email:data.email,
                    mobile:data.mobile,
                    telephone:data.telephone,
                    address:data.address,
                    birth_place:data.birth_place,
                    city:data.city,
                    state:data.state,
                    country:data.country,
                    zip_code:data.zip_code,
                    about:data.about,
                    contact_person:data.contact_person,
                    contact_info:data.contact_info,
                    contact_relationship:data.contact_relationship,
                    picture:data.picture,
                    position_name:data.position_name,
                    department_name:data.department_name,
                    company_name:data.company_name,
                    sss_no:data.sss_no,
                    pagibig_no:data.pagibig_no,
                    tin_no:data.tin_no,
                    philhealth_no:data.philhealth_no,
                    employment_status_name:data.employment_status_name,
                }
            });
        },
        updateProfile:function(){
            $.ajax({url: '../profile/updateProfile',
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': $('#token').val()},
                    data: vue_profile.profile,
                    success: function (data) {
                    if (data.result == 'success') {
                        toastr.success("Successfully updated profile.");
                    }
                    else {
                        toastr.error(data.errors);
                    }
                }
            });
        },
        getAge:function(){
            d2 = new Date();
            d1 = new Date(this.profile.birth_date);
            var diff = d2.getTime() - d1.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        updatePassword:function(){
            $.ajax({url: '../profile/updatePassword',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: vue_profile.password,
                success: function (data) {
                    if (data.result == 'success') {
                        toastr.success("Successfully updated password.");
                        $("#password-modal").modal("hide");
                        vue_profile.password={
                            old_password:'',
                                password:'',
                                password2:''
                        };
                    }
                    else {
                        toastr.error(data.errors);
                    }
                }
            });
        },
        moment:moment
    },
    mounted:function(){
        this.getData();
    }
});