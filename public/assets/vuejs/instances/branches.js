var vue_branches = new Vue({
    el: "#branches",
    data:{
        pagination:[Filter('branch_name'),Filter('name')],
        table:'branches',
        warnings:[],
        loading_warnings:true,
        table1:'display_employees',
        colors:['blue','green','red','yellow','orange','purple','pink','aqua','gray'],
        times:[ {value:"06:00", label:"06:00 AM"},{value:"06:30", label:"06:30 AM"},
                {value:"07:00", label:"07:00 AM"},{value:"07:30", label:"07:30 AM"},
                {value:"08:00", label:"08:00 AM"},{value:"08:30", label:"08:30 AM"},
                {value:"09:00", label:"09:00 AM"},{value:"09:30", label:"09:30 AM"},
                {value:"10:00", label:"10:00 AM"},{value:"10:30", label:"10:30 AM"},
                {value:"11:00", label:"11:00 AM"},{value:"11:30", label:"11:30 AM"},
                {value:"12:00", label:"12:00 PM"},{value:"12:30", label:"12:30 PM"},
                {value:"13:00", label:"01:00 PM"},{value:"13:30", label:"01:30 PM"},
                {value:"14:00", label:"02:00 PM"},{value:"14:30", label:"02:30 PM"},
                {value:"15:00", label:"03:00 PM"},{value:"15:30", label:"03:30 PM"},
                {value:"16:00", label:"04:00 PM"},{value:"16:30", label:"04:30 PM"},
                {value:"17:00", label:"05:00 PM"},{value:"17:30", label:"05:30 PM"},
                {value:"18:00", label:"06:00 PM"},{value:"18:30", label:"06:30 PM"},
                {value:"19:00", label:"07:00 PM"},{value:"19:30", label:"07:30 PM"},
                {value:"20:00", label:"08:00 PM"},{value:"20:30", label:"08:30 PM"},
                {value:"21:00", label:"09:00 PM"},{value:"21:30", label:"09:30 PM"},
                {value:"22:00", label:"10:00 PM"},{value:"22:30", label:"10:30 PM"},
                {value:"23:00", label:"11:00 PM"},{value:"23:30", label:"11:30 PM"},
                {value:"00:00", label:"Closed"}
            ],
        branches:[],
        models:['TX628','K14','H3'],
        jas:[],
        sas:[],
        employees:[],
        newBranch:{
            id:0,
            new_id:0,
            sas_id:0,
            bs_id:0,
            branch_name:'',
            branch_address:'',
            branch_map:'',
            branch_phone:'',
            branch_head_employee_id:0,
            branch_email:'',
            branch_data:{
                biometrics:[],
                computer:{
                    ram:'',
                    disk:'',
                    os:'',
                    cpu:''
                },

            }
        },
        display:{
            id:0,
            branch_name:'',
            key: 0,
            schedules:[]
        },
        newSchedule:{
            id:0,
            schedule_name:'',
            schedule_color:'',
            branch_id:0,
            is_default:0,
            schedule_data: ["09:00","09:00","09:00","09:00","09:00","09:00","09:00"]
        }
    },
    methods:{
        clearForm:function(){
            this.newBranch={
                id:0,
                new_id:0,
                sas_id:0,
                bs_id:0,
                branch_name:'',
                branch_address:'',
                branch_map:'',
                branch_phone:'',
                branch_head_employee_id:0,
                branch_email:'',
                branch_data:{
                    biometrics:[],
                    computer:{
                        ram:'',
                        disk:'',
                        os:'',
                        cpu:''
                    },

                }
            }
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_branches[field] = [];
                data.forEach(function(item, i){
                    if(field == 'branches'){
                        item.branch_data = JSON.parse(item.branch_data);
                    }
                    vue_branches[field].push(item);
                    if(field == 'branches'){
                        vue_branches.branches[i].schedules.forEach(function(sched,k){
                            vue_branches.branches[i].schedules[k].schedule_data = sched.schedule_data.split(",");
                        });
                    }
                    if(field == 'warnings'){
                        vue_branches.loading_warnings = false
                    }
                });
            });
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees','employees');
        },
        getAttendanceErrors:function(id){
            this.warnings = [];
            vue_branches.loading_warnings = true
            this.getData('../../attendance/getAttendanceErrors/' + id + '/'+ "2018-04-16" +'/' + "2018-04-30",'warnings');
        },
        getBranches:function(){
            this.getData('../../branches/getBranches','branches');
        },
        getJAS:function(){
            this.getData('../../employees/getJAS','jas');
        },
        getSAS:function(){
            this.getData('../../employees/getSAS','sas');
        },
        showAddModal:function(){
            if(this.newBranch.id!=0){
                this.clearForm();
            }
            $("#add-modal").modal("show");
        },
        editBranch:function(branch){
            this.newBranch = {
                id:branch.id,
                sas_id:branch.sas_id,
                bs_id:branch.bs_id,
                new_id:branch.id,
                branch_name:branch.branch_name,
                branch_address:branch.branch_address,
                branch_map:branch.branch_map,
                branch_phone:branch.branch_phone,
                branch_head_employee_id:branch.branch_head_employee_id,
                branch_email:branch.branch_email,
                branch_data:{
                    biometrics:[],
                    computer:{
                        ram:branch.branch_data.computer.ram,
                        disk:branch.branch_data.computer.disk,
                        os:branch.branch_data.computer.os,
                        cpu:branch.branch_data.computer.cpu
                    },
                }
            }
            if(branch.branch_data.biometrics !== undefined){
                for(var x=0;x<branch.branch_data.biometrics.length;x++){
                    this.newBranch.branch_data.biometrics.push({
                        model:branch.branch_data.biometrics[x].model,
                        serial:branch.branch_data.biometrics[x].serial,
                        em_connector_version:branch.branch_data.biometrics[x].em_connector_version,
                    });
                }
            }
            $("#add-modal").modal("show");
        },
        saveBranch:function(){
            var url,msg;
            if(this.newBranch.id == 0){
                url = '../../branches/addBranch';
                msg = "Successfully added branch.";
            }
            else{
                url = '../../branches/updateBranch';
                msg = "Successfully updated branch.";
            }

            this.postData(url, this.newBranch, function(){
                toastr.success(msg);
                $("#add-modal").modal("hide");
                vue_branches.clearForm();
                vue_branches.getBranches();
            });
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
        showViewModal:function(branch,key){
            this.display.branch_name = branch.branch_name;
            this.display.id = branch.id;
            this.display.key = key;
            this.newSchedule.branch_id = branch.id;
            this.getAttendanceErrors(branch.id);
            $("#view-modal").modal("show");
        },
        timeLabel:function(time){
            for(var x=0;x<this.times.length;x++){
                if(time == this.times[x].value){
                    return this.times[x].label;
                }
            }
        },
        deleteSchedule:function(schedule){
            if(!confirm("Are you sure you want to delete this shift schedule?")){
                return false;
            }
            this.postData('../../branches/deleteSchedule', {id:schedule.id}, function(){
                toastr.success("Successfully deleted schedule.");
                vue_branches.getBranches();
            });
        },
        addSchedule:function(){
            this.postData('../../branches/addSchedule', this.newSchedule, function(){
                toastr.success("Successfully added schedule.");
                vue_branches.getBranches();
                vue_branches.clearSchedForm();
            });
        },
        updateSchedule:function(){
            this.postData('../../branches/updateSchedule', this.newSchedule, function(){
                toastr.success("Successfully updated schedule.");
                vue_branches.getBranches();
                vue_branches.clearSchedForm();
            });
        },
        clearSchedForm:function(){
            vue_branches.newSchedule.schedule_name = '';
            vue_branches.newSchedule.id = 0;
        },
        editSchedule:function(schedule){
            vue_branches.newSchedule.schedule_name = schedule.schedule_name;
            vue_branches.newSchedule.id = schedule.id;
            vue_branches.newSchedule.schedule_data = [];

            schedule.schedule_data.forEach(function(item, i){
                vue_branches.newSchedule.schedule_data.push(item);
            });

            vue_branches.newSchedule.is_default = schedule.is_default;
            vue_branches.newSchedule.schedule_color = schedule.schedule_color;
        },
        addBiometric:function() {
            this.newBranch.branch_data.biometrics.push({
                model:'',
                serial:'',
                em_connector_version:'',
            });
        },
        removeBiometric:function(key){
            this.newBranch.branch_data.biometrics.splice(key,1);
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    computed:{
        filtered:Pagination,
        filtered1:Pagination1,
        display_employees:function(){
            return this.filtered[this.display.key].employees;
        }
    },
    mounted:function(){
        this.getBranches();
        this.getSAS();
        this.getJAS();
        this.getEmployees();
    }
});