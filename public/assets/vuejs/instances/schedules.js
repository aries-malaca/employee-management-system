var vue_schedules = new Vue({
    el: "#schedules",
    data:{
        auth:{},
        trackable:false,
        show_all:false,
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_schedules',
        table1:'schedule_history',
        isEmployee:false,
        employees:[],
        branches:[],
        search:'',
        filter:'active',
        setSchedule:[],
        display:{
            id:0,
            name:'',
            schedules:[]
        },
        setSingleSchedule:{
            employee_id:$("#my_id").val(),
            employee_name:$("#my_name").val(),
            branch_id:0,
            time:null,
            date:moment().format("YYYY-MM-DD"),
            is_flexi_time:0,
            notes:''
        },
        schedule_requests:[],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
        scheduleModal:{
            branch_id:0,
            is_flexi_time:0,
            schedule_data:["","","","","","",""]
        },
        editing:{
            id:0,
            schedule_data:["","","","","","",""],
            date_start:"",
            date_end:"",
            branch_id:0,
            employee_id:0,
        },
        positions:[],
        edit_overtime:{}
    },
    methods:{
        moment:moment,
        editRequest:function(request){
            $("#edit-schedule").modal("show");
            this.edit_schedule = request;
        },
        addItem:function(employee){
            if(employee.schedules.length>2){
                toastr.error("Cannot add new schedule, selected employee already had 3 schedules. You may edit the (active schedule) or delete the (inactive/ past schedules).");
                return false;
            }

            for(var x=0;x<this.setSchedule.length;x++){
                if(this.setSchedule[x].employee_id == employee.user_id){
                    toastr.warning("Employee Already in list.");
                    return false;
                }
            }

            var d1 = moment().format("YYYY-MM-DD");
            var d2 = moment().format("YYYY-MM-DD");
            var b = this.branches[0].id;
            var f = 0;

            if(this.setSchedule.length>0){
                d1 = this.setSchedule[this.setSchedule.length - 1].date_start;
                d2 = this.setSchedule[this.setSchedule.length - 1].date_end;
                b = this.setSchedule[this.setSchedule.length - 1].branch_id;
                f = this.setSchedule[this.setSchedule.length - 1].is_flexi_time;
            }

            this.setSchedule.push({
                employee_id:employee.user_id,
                employee_name:employee.name,
                branch_id:b,
                schedule_data:["","","","","","",""],
                date_start:d1,
                date_end:d2,
                is_flexi_time:f
            });
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_schedules[field] = [];
                var pendings = 0;
                data.forEach(function(item, i){
                    vue_schedules[field].push(item);
                    if(field == 'branches'){
                        vue_schedules.branches[i].schedules.forEach(function(sched,k){
                            vue_schedules.branches[i].schedules[k].schedule_data = sched.schedule_data.split(",");
                        });
                    }
                    else if(field == 'employees'){
                        vue_schedules.employees[i].branch_name = vue_schedules.getCurrentBranch(item.user_id);
                        vue_schedules.employees[i].schedules.forEach(function(sched,k){
                            if(sched.schedule_data.length>5)
                                vue_schedules.employees[i].schedules[k].schedule_data = JSON.parse(sched.schedule_data);
                        });
                    }
                    else if(field == 'schedule_requests'){
                        vue_schedules.schedule_requests[i].request_data = JSON.parse(item.request_data);
                        vue_schedules.schedule_requests[i].action_data = JSON.parse(item.action_data);

                        if(item.for_my_approval){
                            pendings++;
                        }
                    }
                });
                if(pendings>0){
                    $("#schedules_counter").html(pendings);
                }
            });
        },
        getBranches:function(){
            this.getData('../../branches/getAllBranches','branches');
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees/with_schedule','employees');
        },
        removeItem:function(key){
            this.setSchedule.splice(key,1);
        },
        postData:function(url, d, callback,complete){
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
                },
                complete:complete
            });
        },
        resetRequest:function(request){

            this.postData('../../requests/resetRequestApproval', request, function(){
                toastr.success("Reset success.");
                vue_schedules.getScheduleRequests();
            },function () {

            })
        },
        saveSchedule:function(){
            for(var x=0;x<this.setSchedule.length;x++){
                if(this.setSchedule[x].branch_id == 0 ){
                    toastr.error("Select branch.");
                    return;
                }
                if(this.setSchedule[x].date_start == "" || this.setSchedule[x].date_end == ""){
                    toastr.error("Select dates.");
                    return;
                }

                if(new Date(this.setSchedule[x].date_start).getTime() > new Date(this.setSchedule[x].date_end).getTime()){
                    toastr.error("Invalid date selected.");
                    return;
                }

                if(this.setSchedule[x].schedule_data.indexOf(undefined) != -1 || this.setSchedule[x].schedule_data.indexOf("") != -1 ){
                    toastr.error("Select Schedule.");
                    return;
                }
            }

            this.postData('../../schedules/addSchedules',{data: this.setSchedule}, function(){
                toastr.success("Successfully added schedule.");
                vue_schedules.setSchedule = [];
                vue_schedules.getEmployees();
                vue_schedules.getBranches();
            },
            function(){

            });
        },
        getKey:function(branch_id,field){

            for(var x=0;x<this[field].length;x++){
                if(field == 'branches')
                    var l = this[field][x].id;
                else
                    var l = this[field][x].user_id;

                if(Number(l) == Number(branch_id)){
                    return x;
                }

            }
            return false;
        },
        clearBranchData:function(key){
            if(key == -1)
                this.setSingleSchedule.schedule_data = ["","","","","","",""];
            else
                this.setSchedule[key].schedule_data = ["","","","","","",""];
        },
        showViewModal:function(employee){
            this.display.name = employee.name;
            this.display.schedules = [];
            this.display.id = employee.user_id;
            employee.schedules.forEach(function(item,i){
                vue_schedules.display.schedules.push(item);
            });
            $("#view-modal").modal("show");
        },
        getScheduleName:function(name,branch_id,key){
            var k = this.getKey(branch_id,'branches');

            if(k===false)
                return name;

            var b = this.branches[k];

            if(name == '00:00')
                return 'Rest Day';

            for(var x=0; x< b.schedules.length;x++){
                if(b.schedules[x].schedule_data[key] == name)
                    return b.schedules[x].schedule_name + ' ('+ moment("2000-01-01 " + name).format("LT") +')';
            }

            return name;
        },
        readableDate:function(string){
            return moment(string).format("MM/DD/YYYY");
        },
        getScheduleColor:function(name,branch_id,key){
            var k = this.getKey(branch_id,'branches');
            if(!k)
                return 'black';

            var b = this.branches[k];

            if(name == '00:00')
                return 'black';

            for(var x=0; x< b.schedules.length;x++){
                if(b.schedules[x].schedule_data[key] == name)
                    return b.schedules[x].schedule_color;
            }

            return 'black';
        },
        deleteSchedule:function(schedule,employee_id){

            if(this.isActiveSchedule(schedule)){
                alert("Unable to delete active schedule.");
                return false;
            }

            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../schedules/deleteSchedule',{id: schedule.id}, function() {
                toastr.success("Successfully deleted schedule.");
                vue_schedules.setSchedule = [];
                vue_schedules.getEmployees();
                vue_schedules.getBranches();
                $("#view-modal").modal("hide");
            },
            function(){

            });
        },
        dayName:function(number){
            switch(number){
                case 1:
                    return 'Monday';
                    break;
                case 2:
                    return 'Tuesday';
                    break;
                case 3:
                    return 'Wednesday';
                    break;
                case 4:
                    return 'Thursday';
                    break;
                case 5:
                    return 'Friday';
                    break;
                case 6:
                    return 'Saturday';
                    break;
                case 7:
                    return 'Sunday';
                    break;
            }
        },
        requestSchedule:function(button){
            if(this.setSingleSchedule.notes == ''){
                toastr.error("Please provide notes.");
                return false;
            }
            if(this.setSingleSchedule.branch_id==0){
                toastr.error("Please select branch.");
                return false;
            }

                if(this.setSingleSchedule.time =='' || this.setSingleSchedule.time == undefined) {
                    toastr.error("Please select schedule.");
                    return false;
                }

            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../forms/addSchedule',this.setSingleSchedule, function() {
                vue_schedules.getScheduleRequests();
                toastr.success("Successfully added schedule.");
            },
            function(){
                $btn.button('reset');
            });
        },
        getScheduleRequests:function(){
            if($("#approve_by_id").length>0){
                this.isEmployee = false;
                this.getData('../../requests/getRequests/schedule','schedule_requests');
            }
            else{
                this.isEmployee = true;
                this.getData('../../requests/getRequests/schedule/' + $("#my_id").val(),'schedule_requests');
            }

        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        deleteRequest:function(request){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../requests/deleteRequest',{id:request.id},function(){
                toastr.success("Schedule request has been deleted");
                $("#edit-schedule").modal("hide");
                vue_schedules.getScheduleRequests();
            },
            function(){
            });
        },
        showActionModal:function(schedule, action){
            this.actionModal.request = schedule;
            this.actionModal.action=action;
            this.actionModal.notes ='';
            $("#action-modal").modal("show");
        },
        approveSchedule:function(button){
            var $btn = $(button.target);
            $btn.button('loading');
            this.postData('../../requests/approveRequest',this.actionModal,function(){
                toastr.success("Schedule request has been approved.");
                vue_schedules.getScheduleRequests();
                $("#action-modal").modal("hide");
                $.get('../../requests/sendNotification/'+vue_schedules.actionModal.request.id +'/'+ $("#my_id").val(), function(data){
                });
                $.get('../../requests/sendConfirmation/'+vue_schedules.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        denySchedule:function(button){
            var $btn = $(button.target);
            $btn.button('loading');
            this.postData('../../requests/denyRequest',this.actionModal,function(){
                toastr.success("Schedule request has been denied.");
                vue_schedules.getScheduleRequests();
                $("#action-modal").modal("hide");

                $.get('../../requests/sendConfirmation/'+vue_schedules.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        hasMyHistory:function(schedule){
            if(schedule.user_id == Number($("#form-owner").val()) && schedule.request_data.status != 'pending'){
                return true;
            }
            for(var x=0;x<schedule.action_data.approved_by.length;x++){
                if(schedule.action_data.approved_by[x].id == Number($("#my_id").val())){
                    return true;
                }
            }

            return false;
        },
        editSchedule:function(schedule){
            var data = schedule.schedule_data;
            this.editing.id=schedule.id;
            this.editing.employee_id=schedule.employee_id;
            this.editing.date_start = moment(schedule.schedule_start).format("YYYY-MM-DD");
            this.editing.date_end = moment(schedule.schedule_end).format("YYYY-MM-DD");
            this.editing.branch_id = schedule.branch_id;
            this.editing.schedule_data = [data[1], data[2], data[3], data[4], data[5], data[6], data[0]];
        },
        updateSchedule:function(){
            this.postData('../../schedules/updateSchedule', this.editing, function(){
                toastr.success("Successfully updated schedule.");
                vue_schedules.getEmployees();
                vue_schedules.getBranches();

                for(var x =0; x<vue_schedules.display.schedules.length;x++){
                    if(vue_schedules.editing.id  == vue_schedules.display.schedules[x].id){
                        vue_schedules.display.schedules[x].schedule_start = vue_schedules.editing.date_start;
                        vue_schedules.display.schedules[x].schedule_end = vue_schedules.editing.date_end;
                        vue_schedules.display.schedules[x].branch_id = vue_schedules.editing.branch_id;
                        d = vue_schedules.editing.schedule_data;
                        vue_schedules.display.schedules[x].schedule_data = [d[6],d[0],d[1],d[2],d[3],d[4],d[5]];
                    }
                }
                vue_schedules.editing.id = 0;
            },function(){
                
            });
        },
        copyLast:function(key){
            this.setSchedule[key].date_start = this.setSchedule[key-1].date_start;
            this.setSchedule[key].date_end = this.setSchedule[key-1].date_end;
            this.setSchedule[key].branch_id = this.setSchedule[key-1].branch_id;
            this.setSchedule[key].schedule_data = [];
            for(var x=0;x<this.setSchedule[key-1].schedule_data.length;x++){
                this.setSchedule[key].schedule_data.push(this.setSchedule[key-1].schedule_data[x]);
            }
        },
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_schedules.positions = [];
                data.forEach(function(item,i){
                    vue_schedules.positions.push(item);
                });
            });
        },
        hasMyHistory:function(schedule){
            if(Number($("#my_id").val()) == schedule.employee_id){
                return true;
            }
            for(var x=0;x<schedule.action_data.approved_by.length;x++){
                if(schedule.action_data.approved_by[x].id == Number($("#my_id").val())){
                    return true;
                }
            }

            return false;
        },
        getPositionName:function(position_id){
            for(var x=0;x<this.positions.length;x++){
                if(Number(position_id) == this.positions[x].id){
                    return this.positions[x].position_name
                }
            }
            return '';
        },
        isActiveSchedule:function(schedule){
            if(Number(moment().format('X')) >= Number(moment(schedule.schedule_start).format('X')) && Number(moment().format('X')) <= Number(moment(schedule.schedule_end).format('X'))){
                return true;
            }
            return false;
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
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    computed:{
        filteredEmployees:function(){
            return this.employees.filter(function (object) {
                if(object.name.toLowerCase().indexOf(vue_schedules.search) != -1 || vue_schedules.search ==''){
                    if(object.branch_name == 'N/A' && vue_schedules.filter=='floating'){
                        return true;
                    }
                    else if(object.branch_name != 'N/A' && vue_schedules.filter=='active'){
                        return true;
                    }
                }
                return false;
            }).map(function(item){
                item.has_conflict = item.schedules.filter(function(schedule){
                        return (Number(moment(schedule.schedule_start).format("X")) <= Number(moment().format("X")) && Number(moment(schedule.schedule_end).format("X")) >= Number(moment().format("X")) )
                    }).length > 1;

                item.range_scheds = item.schedules.filter(function(schedule){
                        return schedule.schedule_type === 'RANGE'
                    }).length;

                return item;
            });
        },
        pending_schedules:function(){
            return this.schedule_requests.filter(function(schedule){
                return schedule.request_data.status == 'pending' && (schedule.for_my_approval || vue_schedules.isEmployee || vue_schedules.show_all);
            });
        },
        schedule_history:function(){
            return this.schedule_requests.filter(function(schedule){
                return vue_schedules.hasMyHistory(schedule) || (vue_schedules.show_all && schedule.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1,
        availableSchedules:function(){
            var scheds = [];
            for(var x=0;x<this.branches.length;x++){
                if(this.branches[x].id == this.setSingleSchedule.branch_id){
                    scheds = this.branches[x].schedules;
                }
            }

            for(var x=0;x<scheds.length;x++){
                scheds[x].time = scheds[x].schedule_data[moment(this.setSingleSchedule.date).format('d')];
            }


            return scheds;
        },
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_schedules.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_schedules.trackable = true;
            }
        });
        this.getBranches();
        this.getEmployees();
        this.getScheduleRequests();
        this.getPositions();
    }
});