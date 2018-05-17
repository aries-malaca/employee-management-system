var vue_overtimes = new Vue({
    el:"#overtimes",
    data:{
        auth:{},
        trackable:false,
        show_all:false,
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_overtimes',
        table1:'overtime_history',
        isEmployee:false,
        newOvertime:[{
            date_start:moment().format("YYYY-MM-DD"),
            time_start:'09:00',
            date_end:moment().format("YYYY-MM-DD"),
            time_end:'09:00',
            notes:''
        }],
        overtimes:[],
        employees:[],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
        positions:[],
        edit_overtime:{}
    },
    methods:{
        getEmployeeOvertimes:function(){
            $.get('../../requests/getRequests/overtime', function(data){
                vue_overtimes.overtimes = [];
                var pendings = 0;
                data.forEach(function(item,i){
                    vue_overtimes.overtimes.push(item);
                    vue_overtimes.overtimes[i].request_data = JSON.parse(item.request_data);
                    vue_overtimes.overtimes[i].action_data = JSON.parse(item.action_data);
                    if(item.for_my_approval){
                        pendings++;
                    }
                });
                if(pendings>0){
                    $("#overtimes_counter").html(pendings);
                }
            });
        },
        getEmployees:function(){
            $.get('../../employees/getAllEmployees', function(data){
                vue_overtimes.employees = [];
                data.forEach(function(item,i){
                    vue_overtimes.employees.push(item);
                });
            });
        },
        getMyOvertimes:function(){
            $.get('../../requests/getRequests/overtime/' + $("#my_id").val(), function(data){
                vue_overtimes.overtimes = [];
                data.forEach(function(item,i){
                    vue_overtimes.overtimes.push(item);
                    vue_overtimes.overtimes[i].request_data = JSON.parse(item.request_data);
                    vue_overtimes.overtimes[i].action_data = JSON.parse(item.action_data);
                });
            });
        },
        clearOvertimeItems:function(){
            this.newOvertime = [];
            this.addOvertimeItem();
        },
        addOvertimeItem:function(){
            this.newOvertime.push({
                date_start:moment().format("YYYY-MM-DD"),
                time_start:'09:00',
                date_end:moment().format("YYYY-MM-DD"),
                time_end:'09:00',
                notes:''
            });
        },
        removeOvertimeItem:function(key){
            if(this.newOvertime.length==1){
                toastr.warning("Can't delete all items.");
                return;
            }
            this.newOvertime.splice(key,1);
        },
        hasError:function(data ,key){
            if(data.notes == '')
                return ('Please provide notes for overtime.');

            if(data.date_start == '' || data.date_end == '' || data.time_start == '' || data.time_end == '')
                return("Invalid date/time at item #"+(key+1));

            if(new Date(data.date_start + " " + data.time_start).getTime() > new Date(data.date_end + " " + data.time_end).getTime() )
                return("Invalid date/time at item #"+(key+1));

            if(this.getHours(data).hours == 0 && this.getHours(data).minutes<30){
                return ("Minimum of 30 minutes.");
            }

            for(var x=0;x<this.newOvertime.length;x++){
                if(key == x)
                    continue;

                if(new Date(data.date_start + " " + data.time_start ).getTime() <= new Date(this.newOvertime[x].date_end + " " + this.newOvertime[x].time_end).getTime() &&
                    new Date(data.date_end + " " + data.time_end).getTime() >= new Date(this.newOvertime[x].date_start + " " + this.newOvertime[x].time_start).getTime()  )
                {
                    return ("Conflict at item #"+(key+1));
                }
            }

        },
        saveOvertime:function(button){
            for(var x=0;x<this.newOvertime.length;x++){
                if(e = this.hasError(this.newOvertime[x], x)){
                    toastr.error(e);
                    return true;
                }
            }
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../forms/addOvertime',{data:this.newOvertime},function(){
                toastr.success("Overtime/s added");
                vue_overtimes.getMyOvertimes();
                vue_overtimes.newOvertime = [];
                vue_overtimes.addOvertimeItem();
            },
            function(){
                $btn.button('reset');
            });
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
        deleteOvertime:function(overtime,button){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/deleteRequest',{id:overtime.id},function(){
                toastr.success("Overtime has been deleted");
                vue_overtimes.getMyOvertimes();
            },
            function(){
                $btn.button('reset');
            });
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        showActionModal:function(overtime, action){
            this.actionModal.request = overtime;
            this.actionModal.action=action;
            this.actionModal.notes ='';
            $("#overtime-modal").modal("show");
        },
        approveOvertime:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/approveRequest',this.actionModal,function(){
                toastr.success("Overtime has been approved.");
                vue_overtimes.getEmployeeOvertimes();
                $("#overtime-modal").modal("hide");
                $.get('../../requests/sendNotification/'+vue_overtimes.actionModal.request.id +'/'+ $("#my_id").val(), function(data){
                });
                $.get('../../requests/sendConfirmation/'+vue_overtimes.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        denyOvertime:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/denyRequest',this.actionModal,function(){
                toastr.success("Overtime has been denied.");
                vue_overtimes.getEmployeeOvertimes();
                $("#adjustment-modal").modal("hide");

                $.get('../../requests/sendConfirmation/'+vue_overtimes.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        getHours:function(data){
            var start = data.date_start+' '+ data.time_start;
            var end = data.date_end+' '+ data.time_end;
            var m = moment.duration(moment(end).diff(moment(start)));

            return {hours:m._data.hours, minutes:m._data.minutes};
        },
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_overtimes.positions = [];
                data.forEach(function(item,i){
                    vue_overtimes.positions.push(item);
                });
            });
        },
        hasMyHistory:function(overtime){
            if(Number($("#form-owner").val()) == overtime.employee_id && overtime.request_data.status != 'pending'){
                return true;
            }
            for(var x=0;x<overtime.action_data.approved_by.length;x++){
                if(overtime.action_data.approved_by[x].id == Number($("#my_id").val())){
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
        editRequest:function(request){
            $("#edit-overtime").modal("show");
            this.edit_overtime = request;
        },
        deleteRequest:function(overtime){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../requests/deleteRequest',{id:overtime.id},function(){
                    toastr.success("Overtime has been deleted");
                    $("#edit-overtime").modal("hide");
                    vue_overtimes.getEmployeeOvertimes();
                },
                function(){
                });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
    },
    computed:{
        pending_overtimes:function(){
            return this.overtimes.filter(function(overtime){
                return overtime.request_data.status == 'pending' && (overtime.for_my_approval || vue_overtimes.isEmployee || vue_overtimes.show_all);
            });
        },
        overtime_history:function(){
            return this.overtimes.filter(function(overtime){
                return vue_overtimes.hasMyHistory(overtime) || (vue_overtimes.show_all && overtime.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_overtimes.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_overtimes.trackable = true;
            }
        });
        if($("#approve_by_id").length>0){
            this.getEmployeeOvertimes();
        }
        else{
            this.getMyOvertimes();
            this.isEmployee = true;
        }

        this.getEmployees();
        this.getPositions();
    }
});