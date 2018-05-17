var vue_employees = new Vue({
    el:"#employees",
    data:{
        allow_add_employee:($("#allow_add_employee").val() == 1),
        pagination:[Filter('name'),Filter('created_at'),Filter('name')],
        table:'employees',
        table1:'payslips',
        table2:'inactive_employees',
        employees:[],
        show_loading_attendance:true,
        inactive_employees:[],
        show_raw:true,
        newFile:{
            description:'',
            category:'',
            file_name:''
        },
        newLeaveCredit:{},
        newSalary:{
            id:0,
            start:moment().format("YYYY-MM-DD"),
            end:moment().format("YYYY-MM-DD"),
            amount:0,
            is_present:true,
        },
        newAttendances:[{
            date_start: moment().format("YYYY-MM-DD"),
            date_end: moment().format("YYYY-MM-DD"),
            type: 'ADJUSTMENT',
            time_start: moment().format("HH:MM"),
            time_end: moment().format("HH:MM"),
            mode: 'IN',
            notes: '',
            employee_id: $("#current_id").val()
        }],
        newLeave: {
            date_start: moment().format("YYYY-MM-DD"),
            date_end: moment().format("YYYY-MM-DD"),
            leave_type_id: 0,
            mode: 'FULL',
            notes: '',
            employee_id:0
        },
        newEmployee:{
            id:0,
            trainee_biometric_no:0,
            view_salary:false,
            delete_attendance:false,
            employee_no:0,
            first_name:'',
            middle_name:'',
            last_name:'',
            gender:'male',
            civil_status:'single',
            birth_date:moment("2000-01-01").format("YYYY-MM-DD"),
            address:'',
            about:'',
            birth_place:'',
            email:'',
            mobile:0,
            telephone:0,
            city:'',
            state:'',
            country:'',
            zip_code:'',
            contact_person:'',
            contact_relationship:'',
            contact_info:'',
            local_number:'',
            active_status:1,
            hired_date:moment().format("YYYY-MM-DD"),
            regularization_date:moment().format("YYYY-MM-DD"),
            end_employment_date:moment().format("YYYY-MM-DD"),
            end_employment_reason:'',
            next_evaluation:moment().add(6, "months").format("YYYY-MM-DD"),
            department_id:'',
            position_id:'',
            company_id:'',
            branch_id:1,
            batch_id:'',
            biometric_no:0,
            receive_notification:1,
            tax_exemption_id:'',
            allow_overtime:1,
            allow_leave:1,
            allow_offset:1,
            allow_adjustment:1,
            allow_travel:1,
            bank_code:'',
            bank_number:'',
            skills:'',
            employee_status:'',
            trans:[],
            sss_no:'',
            tin_no:'',
            philhealth_no:'',
            pagibig_no:'',
            hmo_no:'',
            cola_rate:0,
            salary_rate:0,
            password:'',
            level:'',
            allow_access:1,
            allow_suspension:1
        },
        display:{
            employee_no:'',
            address:'',
            mobile:'',
            name:'',
            birth_date:'',
            gender:'',
            picture:'',
            position_name:'',
            last_activity:''
        },
        branches:[],
        batches:[],
        companies:[],
        departments:[],
        positions:[],
        employmentStatuses:[],
        tax_exemptions:[],
        contributions:[],
        levels:[],
        banks:[],
        salary_history:[],
        files:[],
        leave_types:[],
        payslips:[],
        transactions:[],
        show_salary:false,
        setSchedule:{
            branch_id:0,
            time:'',
            date:'',
            employee_id:0,
            single:false,
            is_hr:false,
            is_read_only:false
        },
        timeSheet:{
            month: Number(moment().format("D")) > 5 ? Number(moment().format("M")) : ((Number(moment().format("M"))-1)),
            year:Number(moment().format("YYYY")),
            cutoff:Number(moment().format("D")) > 20 || Number(moment().format("D")) < 6 ?2:1,
        },
        attendances:[],
        view_attendance:{
            basic_data:{},
            advanced_data:[]
        }
    },
    methods: {
        clearAddForm:function(){
            this.newEmployee = {
                id:0,
                trainee_biometric_no:0,
                view_salary:false,
                delete_attendance:false,
                employee_no:0,
                first_name:'',
                middle_name:'',
                last_name:'',
                gender:'male',
                civil_status:'single',
                birth_date:moment("2000-01-01").format("YYYY-MM-DD"),
                address:'',
                about:'',
                birth_place:'',
                email:'',
                mobile:0,
                telephone:0,
                city:'',
                state:'',
                country:'',
                zip_code:'',
                contact_person:'',
                contact_relationship:'',
                contact_info:'',
                local_number:'',
                active_status:1,
                hired_date:moment().format("YYYY-MM-DD"),
                next_evaluation:moment().add(6, "months").format("YYYY-MM-DD"),
                regularization_date:moment().format("YYYY-MM-DD"),
                end_employment_date:moment().format("YYYY-MM-DD"),
                end_employment_reason:'',
                department_id:'',
                position_id:'',
                company_id:'',
                branch_id:'',
                batch_id:'',
                biometric_no:0,
                receive_notification:1,
                tax_exemption_id:'',
                allow_overtime:1,
                allow_leave:1,
                allow_offset:1,
                allow_adjustment:1,
                allow_travel:1,
                bank_code:'',
                bank_number:'',
                skills:'',
                employee_status:'',
                trans:[],
                sss_no:'',
                tin_no:'',
                philhealth_no:'',
                pagibig_no:'',
                hmo_no:'',
                cola_rate:0,
                salary_rate:0,
                password:'',
                level:'',
                allow_access:1,
                allow_suspension:1
            };
            if(undefined === $("#current_id").val())
                this.getTempID();
            this.getContributions();
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_employees[field] = [];
                if(field == 'contributions')
                    vue_employees.newEmployee.trans = [];

                data.forEach(function(item, i){
                    vue_employees[field].push(item);
                    if(field == 'contributions')
                        vue_employees.newEmployee.trans.push({ id:item.id, checked: true});
                    if(field == 'salary_history'){
                        vue_employees[field][i].amount = vue_employees[field][i].amount.toFixed(2);
                        vue_employees[field][i].start_date = moment(vue_employees[field][i].start_date).format("MM/DD/YYYY");
                        if(vue_employees[field][i].end_date == "0000-00-00 00:00:00")
                            vue_employees[field][i].end_date = "Present";
                        else
                            vue_employees[field][i].end_date = moment(vue_employees[field][i].end_date).format("MM/DD/YYYY");
                    }

                    if(field == 'employees'){
                        vue_employees[field][i].branch_name = vue_employees.getCurrentBranch(item.user_id);
                    }
                });
            });
        },
        getLeaveTypes:function(){
            $.get('../../leave_types/getLeaveTypes/'+ $("#current_id").val(), function(data){
                vue_employees.leave_types = [];
                data.forEach(function(item,i){
                    vue_employees.leave_types.push(item);
                    vue_employees.leave_types[i].leave_type_data = JSON.parse(item.leave_type_data);
                });
            });
        },
        getTempID:function(){
            $.get('../../employees/getTempID', function(data){
                vue_employees.newEmployee.employee_no = data;
            });
        },
        getEmployees: function () {
            this.getData("../../employees/getEmployees/with_schedule","employees");
        },
        getInactiveEmployees: function () {
            this.getData("../../employees/getInactiveEmployees","inactive_employees");
        },
        getBranches: function () {
            this.getData("../../branches/getBranches","branches");
        },
        getFiles: function (employee_id) {
            this.getData("../../files/getFiles/"+employee_id,"files");
        },
        getPayslips: function (employee_id) {
            this.getData("../../payroll/getPayslips/"+employee_id,"payslips");
        },
        getBatches: function () {
            this.getData("../../batches/getBatches","batches");
        },
        getBanks: function () {
            this.getData("../../banks/getBanks","banks");
        },
        getDepartments: function () {
            this.getData("../../departments/getDepartments","departments");
        },
        getPositions: function () {
            this.getData("../../positions/getPositions","positions");
        },
        getCompanies: function () {
            this.getData("../../companies/getCompanies","companies");
        },
        getEmploymentStatuses:function(){
            this.getData("../../employment/getEmploymentStatuses","employmentStatuses");
        },
        getTaxExemptions: function () {
            this.getData("../../contributions/getTaxExemptions","tax_exemptions");
        },
        getContributions:function(){
            this.getData("../../contributions/getContributions","contributions");
        },
        getLevels:function(){
            this.getData("../../levels/getLevels","levels");
        },
        deleteEmployee:function(id){
            if(confirm("Are you sure you want to delete this employee? Deleting this will also deletes payslip, attendance, requests, leaves, messages, notes, evaluations, transactions, logs. files.")){
                $.ajax({
                    url: "../../employees/deleteEmployee",
                    data: {id:id},
                    method: "POST",
                    headers: {'X-CSRF-TOKEN': $('#token').val()},
                    success: function (data) {
                        if (data.result == 'success') {
                            toastr.success("Successfully deleted Employee.");
                            vue_employees.getEmployees();
                        }
                        else
                            toastr.error(data.result);
                    }
                });
            }
            return false;
        },
        showAddModal:function(){
            $("#add-modal").modal("show");
        },
        postData:function(url, d, callback){
            $.ajax({
                url: url,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: d,
                success: function (data) {
                    if (data.result == 'success')
                        callback();
                    else
                        toastr.error(data.errors);
                }
            });
        },
        addEmployee:function() {
            this.postData('../employees/addEmployee', this.newEmployee, function(){
                toastr.success("Successfully added employee.");
                vue_employees.clearAddForm();
                vue_employees.getEmployees();
                $("#add-modal").modal("hide");
            });
        },
        updateProfile:function(){
            this.postData('../employees/updateProfile', this.newEmployee, function(){
                toastr.success("Successfully updated profile.");
                vue_employees.getEmployee($("#current_id").val());
            });
        },
        updateWork:function(){
            this.postData('../employees/updateWork', this.newEmployee, function(){
                toastr.success("Successfully updated work.");
                vue_employees.getEmployee($("#current_id").val());
            });
        },
        updateSystem:function(){
            this.postData('../employees/updateSystem', this.newEmployee, function(){
                toastr.success("Successfully updated system access.");
                vue_employees.getEmployee($("#current_id").val());
            });
        },
        getEmployee:function(id){
            var i = id;
            $.get("../../employees/getEmployee/"+id, function(data){
                vue_employees.display = {
                    employee_no:data.employee_no,
                    address:data.address,
                    mobile:data.mobile,
                    position_name:data.position_name,
                    birth_date:moment(data.birth_date).format("MM/DD/YYYY"),
                    gender:data.gender.toUpperCase(),
                    picture:data.picture,
                    name:data.name,
                    last_activity:data.last_activity
                };

                vue_employees.newEmployee = {
                    id:i,
                    trainee_biometric_no:data.trainee_biometric_no,
                    view_salary:data.view_salary,
                    delete_attendance:data.delete_attendance,
                    employee_no:data.employee_no,
                    first_name:data.first_name,
                    middle_name:data.middle_name,
                    last_name:data.last_name,
                    gender:data.gender,
                    civil_status:data.civil_status,
                    birth_date:moment(data.birth_date).format("YYYY-MM-DD"),
                    address:data.address,
                    about:data.about,
                    birth_place:data.birth_place,
                    email:data.email,
                    mobile:data.mobile,
                    telephone:data.telephone,
                    city:data.city,
                    state:data.state,
                    country:data.country,
                    zip_code:data.zip_code,
                    contact_person:data.contact_person,
                    contact_relationship:data.contact_relationship,
                    contact_info:data.contact_info,
                    active_status:data.active_status,
                    local_number:data.local_number,
                    hired_date:moment(data.hired_date).format("YYYY-MM-DD"),
                    next_evaluation:moment(data.next_evaluation).format("YYYY-MM-DD"),
                    department_id:data.department_id,
                    position_id:data.position_id,
                    company_id:data.company_id,
                    branch_id:data.branch_id,
                    batch_id:data.batch_id,
                    biometric_no:data.biometric_no,
                    receive_notification:data.receive_notification,
                    tax_exemption_id:data.tax_exemption_id,
                    allow_overtime:data.allow_overtime,
                    allow_leave:data.allow_leave,
                    allow_offset:data.allow_offset,
                    allow_adjustment:data.allow_adjustment,
                    allow_travel:data.allow_travel,
                    regularization_date:moment(data.regularization_date).format("YYYY-MM-DD"),
                    end_employment_date:moment(data.end_employment_date).format("YYYY-MM-DD"),
                    end_employment_reason:data.end_employment_reason,
                    bank_code:data.bank_code,
                    bank_number:data.bank_number,
                    skills:data.skills,
                    employee_status:data.employee_status,
                    trans:[],
                    sss_no:data.sss_no,
                    tin_no:data.tin_no,
                    philhealth_no:data.philhealth_no,
                    pagibig_no:data.pagibig_no,
                    hmo_no:data.hmo_no,
                    cola_rate:data.cola_rate,
                    salary_rate:data.salary_rate,
                    password:'',
                    level:data.level,
                    allow_access:data.allow_access,
                    allow_suspension:data.allow_suspension
                };

                if(data.contributions != null){
                    var contribs = data.contributions.split(",");
                    contribs.forEach(function(item,i){
                        contribs[i] = Number(item);
                    });
                }
                else{
                    var contribs = [];
                }
                for(var x=0;x<vue_employees.contributions.length;x++){
                    var obj = {id: vue_employees.contributions[x].id ,checked:false}
                    if(contribs.indexOf(vue_employees.contributions[x].id) != -1)
                        obj.checked = true;

                    vue_employees.newEmployee.trans.push(obj);
                }

                vue_employees.getData('../salaries/getSalaryHistory/'+i, 'salary_history');
                $(".emp_div").show();
            });
        },
        initAttendance:function(adate){
            if (!jQuery().fullCalendar)
                return;

            this.show_loading_attendance = true;
            let u = this;
            $.get("../../attendance/getAttendance/" + this.newEmployee.id  +"/flag/"+ (this.show_raw?1:0),function(data){
                u.show_loading_attendance = false;
                setTimeout(function(){
                    $('#calendar').fullCalendar('destroy'); // destroy the calendar
                    $('#calendar').fullCalendar({ //re-initialize the calendar
                        disableDragging: true,
                        editable: false,
                        events: data,// events source,
                        eventClick: function(event) {
                            if(event.id !== undefined){
                                vue_employees.viewAttendance(event);
                            }
                        },
                    });
                    if(isNaN(adate))
                        $('#calendar').fullCalendar('gotoDate',adate);
                },200);
            });
        },
        viewAttendance:function(event){
            $('#view-modal').modal("show");
            this.view_attendance.basic_data = event;
            this.view_attendance.advanced_data = [];
            if(event.date_credited !== undefined){
                $.get("../../attendance/getAttendance/" + this.newEmployee.id +"/"+event.date_credited,function(data){
                    vue_employees.view_attendance.advanced_data = data;
                });
            }
        },
        deleteAttendance:function(event){
            if(!this.newEmployee.delete_attendance){
                return false;
            }

            if(event.type == 'LEAVE'){
                if(!confirm("Are you sure you want to delete this leave ("+ event.title +")?"))
                    return false;

                this.postData('../attendance/deleteLeave', {request_id:event.id}, function(){
                    toastr.success("Successfully deleted leave.");
                    vue_employees.initAttendance(event.datetime);
                });
            }
            else{
                if(!confirm("Are you sure you want to delete this log ("+ moment(event.attendance_stamp).format("MM/DD/YYYY LT") +")?"))
                    return false;

                var data = {id:event.id,via:event.via,datetime:event.attendance_stamp,employee_id:event.employee_id };

                this.postData('../attendance/deleteAttendance', data, function(){
                    toastr.success("Successfully deleted attendance log.");
                    vue_employees.initAttendance(event.datetime);
                    $('#view-modal').modal("hide");
                });

            }

        },
        initSchedule:function(adate){
            if (!jQuery().fullCalendar)
                return;

            $.get("../../schedules/getSchedule/" + this.newEmployee.id,function(data){
                $('#calendar2').fullCalendar('destroy'); // destroy the calendar
                $('#calendar2').fullCalendar({ //re-initialize the calendar
                    disableDragging: true,
                    editable: false,
                    events: data,// events source
                    eventClick: function(event) {
                        vue_employees.editSchedule(event);
                    }
                });
                if(isNaN(adate))
                    $('#calendar2').fullCalendar('gotoDate',adate);
            });
        },
        editSchedule:function(event){

        		this.setSchedule.branch_id=event.branch;
	            this.setSchedule.date=event._i;
	            this.setSchedule.time=event.tt;
	            this.setSchedule.employee_id = Number($("#current_id").val());
	            this.setSchedule.single = (event.single === true);
	            this.setSchedule.id = event.id;
	            this.setSchedule.is_read_only = event.is_read_only;
	            this.setSchedule.is_hr = event.is_hr;
	            $("#schedule-modal").modal("show");
        },
        deleteFile:function(file){
            if(!confirm("Are you sure you want to delete "+ file.description + "?"))
                return false;

            this.postData('../files/deleteFile', file , function(){
                toastr.success("Successfully deleted file.");
                vue_employees.getFiles($("#current_id").val());
            });
        },
        showFileModal:function(){
            $("#file-modal").modal("show");
        },
        showAttendanceModal:function(){
            $("#attendance-modal").modal("show");
        },
        showLeaveModal:function(){
            $("#leave-modal").modal("show");
        },
        hasError:function(data){
            for(var x=0;x<data.length;x++){

                if(data[x].type=='ADJUSTMENT' && (data[x].date_start == '' || data[x].time_start == ''))
                    return "Date/Time required.";

                if(data[x].type=='OVERTIME' && (data[x].date_start == '' || data[x].time_start == '' || data[x].date_end == '' || data[x].time_end == ''))
                    return "Date/Time required.";

                if((data[x].type=='TRAVEL' || data[x].type=='OFFSET') && (data[x].date_start == '' || data[x].time_start == '' || data[x].date_end == '' || data[x].time_end == ''))
                    return "Date/Time required.";

                if(data[x].notes == ''){
                    //return "Note is required.";
                }

                if(data[x].type == 'OVERTIME'){
                    if(moment(data[x].date_end +" " + data[x].time_end).format('X') < moment(data[x].date_start +" " + data[x].time_start).format('X')){
                        return "Start time is greater than end time."
                    }
                }
                if(data[x].type == 'TRAVEL' || data[x].type == 'OFFSET'){
                    if(moment(data[x].date_start +" " + data[x].time_end).format('X') < moment(data[x].date_start +" " + data[x].time_start).format('X')){
                        return "Start time is greater than end time."
                    }
                }
            }

            return false;
        },
        isGraveyard:function(time){
            if(Number(moment("2000-01-01 "+time).format('H')) < 4){
                return true;
            }

            return false;
        },
        addAttendanceRow:function(){
            this.newAttendances.push({
                date_start: moment(this.newAttendances[0].date_start).format("YYYY-MM-DD"),
                date_end: moment(this.newAttendances[0].date_end).format("YYYY-MM-DD"),
                type: this.newAttendances[0].type,
                time_start: this.newAttendances[0].time_start,
                time_end: this.newAttendances[0].time_end,
                mode: 'IN',
                notes: '',
                employee_id: $("#current_id").val()
            });
        },
        removeAttendanceRow:function(key){
            if(this.newAttendances.length == 1){
                toastr.error("Unable to delete all rows.");
                return false;
            }

            this.newAttendances.splice(key,1);
        },
        saveAttendance:function(){
            var error = this.hasError(this.newAttendances);
            if(error !== false){
                toastr.error(error);
                return false;
            }

            this.postData('../attendance/submitAttendances', {data:this.newAttendances}, function(){
                toastr.success("Successfully added attendance log.");
                vue_employees.initAttendance(vue_employees.newAttendances[0].date_start);
                $("#attendance-modal").modal("hide");
            });
        },
        uploadFile:function(){
            $("#file-modal").modal("hide");

            var file_data = $("#file").prop("files")[0];
            if(file_data !==undefined ){
                var form_data = new FormData();                  // Creating object of FormData class
                form_data.append("file", file_data);             // Appending parameter named file with properties of file_field to form_data
                form_data.append("description", this.newFile.description);
                form_data.append("category", this.newFile.category);
                form_data.append("employee_id", this.newEmployee.id);
                $.ajax({
                    url: "../../files/uploadFile",
                    dataType: 'script',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,                         // Setting the data attribute of ajax with file_data
                    type: 'post',
                    headers: {'X-CSRF-TOKEN': $('#token').val()}
                });

                setTimeout(function(){
                    vue_employees.getFiles($("#current_id").val());
                    this.newFile = {
                        description:'',
                        category:'',
                        file_name:''
                    };
                },2000);
            }
        },
        getCurrentBranch:function(employee_id){
            for(var x=0;x<this.employees.length;x++){
                if(employee_id == this.employees[x].user_id){
                    for(var y=0;y<this.employees[x].schedules.length;y++){
                        if(moment().format('X')>=moment(this.employees[x].schedules[y].schedule_start).format('X') &&
                            moment().format('X')<=moment(this.employees[x].schedules[y].schedule_end).format('X')){
                            return this.employees[x].schedules[y].branch_name;
                        }
                    }
                }
            }
            return 'N/A';
        },
        saveSchedule:function(){
            this.postData('../schedules/saveSingleSchedule', this.setSchedule, function(){
                toastr.success("Successfully added schedule.");
                vue_employees.initSchedule(vue_employees.setSchedule.date);
                vue_employees.getTimeSheet();
                vue_employees.initAttendance();

                $.get('../../attendance/importAttendance/'+ moment().format("YYYY-MM-DD") +'/'+ vue_employees.setSchedule.branch_id, function(data){
                    vue_employees.getTimeSheet();
                    vue_employees.initAttendance();
                });

                $("#schedule-modal").modal("hide");
            });
        },
        deleteSchedule:function(){
            if(!confirm("Are you sure you want to delete this single schedule?")){
                return false;
            }
            this.postData('../schedules/deleteSingleSchedule', this.setSchedule, function(){
                toastr.success("Successfully deleted schedule.");
                vue_employees.initSchedule(vue_employees.setSchedule.date);
                vue_employees.getTimeSheet();
                vue_employees.initAttendance();
                $("#schedule-modal").modal("hide");
            });
        },
        allowHalfDay:function(leave_type_id){
            for(var x=0;x<this.leave_types.length;x++){
                if(leave_type_id == this.leave_types[x].id){
                    return (this.leave_types[x].leave_type_data.allow_half_day == 'true');
                }
            }
            return false;
        },
        allowStaggered:function(leave_type_id){
            for(var x=0;x<this.leave_types.length;x++){
                if(leave_type_id == this.leave_types[x].id){
                    return (this.leave_types[x].leave_type_data.is_staggered == 'true');
                }
            }
            return false;
        },
        resolveRange:function(){
            if(!this.allowStaggered(this.newLeave.leave_type_id)){
                this.newLeave.date_end = moment(this.newLeave.date_start).add(this.getMaxDays(this.newLeave.leave_type_id),"days").format("YYYY-MM-DD");
            }
        },
        getMaxDays:function(leave_type_id){
            for(var x=0;x<this.leave_types.length;x++){
                if(leave_type_id == this.leave_types[x].id){
                    return (Number(this.leave_types[x].credits) - 1);
                }
            }
            return 0;
        },
        saveLeave:function(){
            this.postData('../attendance/saveLeave', this.newLeave, function(){
                toastr.success("Successfully added leave.");
                vue_employees.initAttendance(vue_employees.newLeave.date_start);
                $("#leave-modal").modal("hide");
            });
        },
        getTimeSheet:function(){
            $("#loading").show();
            $.get('../../attendance/getTimesheet/' + this.timeSheet.month + '/' + this.timeSheet.year + '/'+ this.timeSheet.cutoff +'/' +  this.newEmployee.id , function(data){
                vue_employees.attendances = [];
                var has_data = false;
                data.forEach(function(item, i){
                    vue_employees.attendances.push(item);

                    if(moment().format("MM/DD/YYYY") === item.date){
                        if(item.remarks.indexOf("absent") === -1)
                            has_data = true;
                    }

                });
                $("#loading").hide();
            });
        },
        getMonthName:function(n){
            return moment("2000-"+ n +"-01").format("MMMM");
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        getOutLog:function(data){

        },
        finalizeSchedule:function(){
            $.ajax({
                url: '../../attendance/lockSchedules',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: {employee_id:$("#current_id").val(), attendances:this.attendances},
                success: function (data) {
                    if (data.result == 'success'){
                        toastr.success("Schedules Locked");
                        vue_employees.getTimeSheet();
                        vue_employees.initAttendance();
                    }
                    else
                        toastr.error(data.errors);
                }
            });
        },
        fixSchedule:function(){
            $.ajax({
                url: '../../attendance/fixSchedule',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: {employee_id:$("#current_id").val(), attendances:this.attendances},
                success: function (data) {
                    if (data.result == 'success'){
                        toastr.success("Schedules Fixed");
                        for(var x=0;x<data.branches.length;x++){
                            $.get('../../attendance/importAttendance/'+ moment().format("YYYY-MM-DD") +'/'+ data.branches[x], function(g){
                                vue_employees.getTimeSheet();
                                vue_employees.initAttendance();
                            });
                        }
                    }
                    else
                        toastr.error(data.errors);
                }
            });
        },
        editSalary:function(salary){
            this.newSalary={
                id:salary.id,
                start:moment(salary.start_date).format("YYYY-MM-DD"),
                end:moment(salary.end_date).format("YYYY-MM-DD"),
                amount:salary.amount,
                is_present:(salary.end_date==='0000-00-00')
            };
            $("#edit-salary-modal").modal("show");
        },
        updateSalary:function(salary){
            $.ajax({
                url: '../../employees/updateSalaryRow',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: this.newSalary,
                success: function (data) {
                    if (data.result == 'success'){
                        toastr.success("Successfully update Salary");
                        $("#edit-salary-modal").modal("hide");
                        vue_employees.getData('../salaries/getSalaryHistory/'+Number($("#current_id").val()), 'salary_history');
                        vue_employees.getEmployee($("#current_id").val());
                    }
                    else
                        toastr.error(data.errors);
                }
            });
        },
        editLeaveCredit:function(leave){
            this.newLeaveCredit = {
                leave_type_id: leave.id,
                employee_id: Number($("#current_id").val()),
                max_leave:0,
                year:moment().format("YYYY")
            };
            $("#leave-credit-modal").modal("show");
        },
        updateLeaveCredit:function(){
            $.ajax({
                url: '../../leave/updateLeaveCredit',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data:this.newLeaveCredit,
                success: function (data) {
                    if (data.result == 'success'){
                        toastr.success("Leave Credit Adjusted");
                        vue_employees.getLeaveTypes();
                        $("#leave-credit-modal").modal("hide");
                    }
                    else
                        toastr.error(data.errors);
                }
            });
        }
    },
    mounted:function(){
        this.getEmployees();
        this.getInactiveEmployees();
        this.getBranches();
        this.getBatches();
        this.getCompanies();
        this.getDepartments();
        this.getPositions();
        this.getEmploymentStatuses();
        this.getTaxExemptions();
        this.getBanks();
        this.getTaxExemptions();
        this.getContributions();
        this.getLevels();
        this.clearAddForm();

        setTimeout(function(){
            if($("#current_id").length>0){
                vue_employees.getLeaveTypes();
                vue_employees.getEmployee($("#current_id").val());
                vue_employees.getFiles($("#current_id").val());
                vue_employees.getPayslips($("#current_id").val());
                vue_employees.newEmployee.id = Number($("#current_id").val());
                vue_employees.newLeave.employee_id = Number($("#current_id").val());
                $(".sl").select2();
                $(".select2-chosen").html(vue_employees.display.name);
                vue_employees.getTimeSheet();
                vue_employees.initAttendance();
            }
        },2000);
    },
    computed:{
        salary_frequency:function(){
            for(var x=0;x<this.positions.length;x++){
                if(this.positions[x].id == this.newEmployee.position_id){
                    var position_data = JSON.parse(this.positions[x].position_data);
                    return position_data.salary_frequency.toUpperCase();
                }
            }
            return "";
        },
        currentYear:function(){
            return Number(moment().format("YYYY"));
        },
        rangeYears:function(){
            var years = [];
            for(var x=this.currentYear;x>(this.currentYear-2);x--){
                years.push(x);
            }

            return years;
        },
        filtered:Pagination,
        filtered1:Pagination1,
        filtered2:Pagination2,
        getAge:function(){
            d2 = new Date();
            d1 = new Date(this.newEmployee.birth_date);
            var diff = d2.getTime() - d1.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        isOnline:function(){
            if(this.display.current_id != 0){
                if( (Number(moment().format('X')) - Number(moment(this.display.last_activity).format('X')) ) <300)
                    return true;
            }
            return false
        },
        availableSchedules:function(){
            var scheds = [];
            for(var x=0;x<this.branches.length;x++){
                if(this.branches[x].id == this.setSchedule.branch_id){
                    scheds = this.branches[x].schedules;
                }
            }
            for(var x=0;x<scheds.length;x++){
                var o = moment(this.setSchedule.date).format('d');

                if(o==0){
                    o=6;
                }
                else{
                    o--;
                }

                scheds[x].time = scheds[x].schedule_data.split(',')[o];
            }


            return scheds;
        },
        currentBranch:function(){
            var branch = 'N/A';
            for(var x=0;x<this.employees.length;x++){
                if(this.employees[x].user_id == this.newEmployee.id){
                    return this.employees[x].branch_name;
                }
            }
            return branch;
        },
        totalTime:function(){
            var total={
                late:0,
                undertime:0,
                ot:0
            };

            for(var x=0;x<this.attendances.length;x++){
                total.late+= this.attendances[x].late_hours;
                total.undertime+= this.attendances[x].undertime_hours;
                total.ot+= this.attendances[x].overtime_hours;
            }
            return total;
        }
    }
});