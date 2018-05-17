var vue_leaves = new Vue({
    el:"#leaves",
    data:{
        auth:{},
        trackable:false,
        show_all:false,
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_leaves',
        table1:'leave_history',
        newLeave:[{
            date_start:moment().format("YYYY-MM-DD"),
            date_end:moment().format("YYYY-MM-DD"),
            leave_type_id:0,
            mode:'FULL',
            notes:''
        }],
        leaves:[],
        leave_types:[],
        employees:[],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
        isEmployee:false,
        positions:[],
        edit_leave:{}
    },
    methods:{
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_leaves.positions = [];
                data.forEach(function(item,i){
                    vue_leaves.positions.push(item);
                });
            });
        },
        getEmployees:function(){
            $.get('../../employees/getAllEmployees', function(data){
                vue_leaves.employees = [];
                data.forEach(function(item,i){
                    vue_leaves.employees.push(item);
                });
            });
        },
        getMyLeaves:function(){
            $.get('../../requests/getRequests/leave/' + $("#my_id").val(), function(data){
                vue_leaves.leaves = [];
                data.forEach(function(item,i){
                    vue_leaves.leaves.push(item);
                    vue_leaves.leaves[i].request_data = JSON.parse(item.request_data);
                    vue_leaves.leaves[i].action_data = JSON.parse(item.action_data);
                });
            });
        },
        getEmployeeLeaves:function(){
            $.get('../../requests/getRequests/leave', function(data){
                vue_leaves.leaves = [];
                var pendings = 0;
                data.forEach(function(item,i){
                    vue_leaves.leaves.push(item);
                    vue_leaves.leaves[i].request_data = JSON.parse(item.request_data);
                    vue_leaves.leaves[i].action_data = JSON.parse(item.action_data);
                    if(item.for_my_approval){
                        pendings++;
                    }
                });
                if(pendings>0){
                    $("#leves_counter").html(pendings);
                }
            });
        },
        getLeaveTypes:function(employee_id){
            $.get('../../leave_types/getLeaveTypes/'+ employee_id, function(data){
                vue_leaves.leave_types = [];
                data.forEach(function(item,i){
                    vue_leaves.leave_types.push(item);
                    vue_leaves.leave_types[i].leave_type_data = JSON.parse(item.leave_type_data);
                    vue_leaves.newLeave.leave_type_id = vue_leaves.leave_types[0].id;
                });
            });
        },
        clearLeaveItems:function(){
            this.newLeave = [];
            this.addLeaveItem();
        },
        addLeaveItem:function(){
            this.newLeave.push({
                date_start:moment().format("YYYY-MM-DD"),
                date_end:moment().format("YYYY-MM-DD"),
                leave_type_id:this.leave_types[0].id,
                mode:'FULL',
                notes:''
            });
        },
        removeLeaveItem:function(key){
            if(this.newLeave.length==1){
                toastr.warning("Can't delete all items.");
                return;
            }
            this.newLeave.splice(key,1);
        },
        hasError:function(data ,key){
            if(data.leave_type_id == 0 || data.leave_type_id == undefined)
                return("Select leave type");
            if(data.notes == '')
                return ('Please provide reason for leave.');

            if(data.date_start == '' || data.date_end == '')
                return("Invalid date/time at item #"+(key+1));

            if(new Date(data.date_start).getTime() > new Date(data.date_end).getTime() )
                return("Invalid date/time at item #"+(key+1));

            for(var x=0;x<this.newLeave.length;x++){
                if(key == x)
                    continue;

                if(new Date(data.date_start).getTime() <= new Date(this.newLeave[x].date_end).getTime() &&
                    new Date(data.date_end).getTime() >= new Date(this.newLeave[x].date_start).getTime()  )
                {
                    return ("Conflict at item #"+(key+1));
                }
            }

        },
        saveLeave:function(button){
            for(var x=0;x<this.newLeave.length;x++){
                if(e = this.hasError(this.newLeave[x], x)){
                    toastr.error(e);
                    return true;
                }
            }
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../forms/addLeave',{data:this.newLeave},function(){
                toastr.success("Leave/s added");
                vue_leaves.getMyLeaves();
                vue_leaves.newLeave = [];
                vue_leaves.addLeaveItem();
                vue_leaves.getLeaveTypes($("#my_id").val());
            },
            function(){
                $btn.button('reset');
            });
        },
        deleteLeave:function(leave,button){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }

            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/deleteRequest',{id:leave.id},function(){
                toastr.success("Leave has been deleted");
                vue_leaves.getMyLeaves();
                vue_leaves.getLeaveTypes($("#my_id").val());
            },
            function(){
                $btn.button('reset');
            });
        },
        postData:function(url, d, callback, complete){
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
        formatDateTime:function(str,format){
            return moment(str).format(format);
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
        resolveRange:function(key){
            if(!this.allowStaggered(this.newLeave[key].leave_type_id)){
                this.newLeave[key].date_end = moment(this.newLeave[key].date_start).add(this.getMaxDays(this.newLeave[key].leave_type_id),"days").format("YYYY-MM-DD");
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
        getLeaveTypeName:function(leave_type_id){
            for(var x=0;x<this.leave_types.length;x++){
                if(leave_type_id == this.leave_types[x].id){
                    return this.leave_types[x].leave_type_name;
                }
            }
        },
        showActionModal:function(adjustment, action){
            this.actionModal.request = adjustment;
            this.actionModal.action=action;
            this.actionModal.notes ='';
            $("#leave-modal").modal("show");
        },
        approveLeave:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/approveRequest',this.actionModal,function(){
                toastr.success("Leave has been approved.");
                vue_leaves.getEmployeeLeaves();
                $("#leave-modal").modal("hide");

                $.get('../../requests/sendNotification/'+vue_leaves.actionModal.request.id +'/'+ $("#my_id").val(), function(data){
                });
                $.get('../../requests/sendConfirmation/'+vue_leaves.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        denyLeave:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/denyRequest',this.actionModal,function(){
                toastr.success("Leave has been denied.");
                vue_leaves.getEmployeeLeaves();
                $("#leave-modal").modal("hide");
                $.get('../../requests/sendConfirmation/'+vue_leaves.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        hasMyHistory:function(leave){
            if(Number($("#form-owner").val()) == leave.employee_id && leave.request_data.status != 'pending'){
                return true;
            }
            for(var x=0;x<leave.action_data.approved_by.length;x++){
                if(leave.action_data.approved_by[x].id == Number($("#my_id").val())){
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
        showCreditsModal:function(leave){
            for(var x =0;x<this.leave_types.length;x++){
                this.leave_types[x].credits = 0;
                this.leave_types[x].used = 0;
            }
            this.getLeaveTypes(leave.employee_id);
            $("#credits-modal").modal("show");
        },
        editRequest:function(request){
            $("#edit-leave").modal("show");
            this.edit_leave = request;
        },
        deleteRequest:function(leave){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../requests/deleteRequest',{id:leave.id},function(){
                    toastr.success("Leave has been deleted");
                    $("#edit-leave").modal("hide");
                    vue_leaves.getEmployeeLeaves();
                },
                function(){
                });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
    },
    computed:{
        pending_leaves:function(){
            return this.leaves.filter(function(leave){
                return leave.request_data.status == 'pending' && (leave.for_my_approval || vue_leaves.isEmployee || vue_leaves.show_all);
            });
        },
        leave_history:function(){
            return this.leaves.filter(function(leave){
                return vue_leaves.hasMyHistory(leave) || (vue_leaves.show_all && leave.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_leaves.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_leaves.trackable = true;
            }
        });
        if($("#approve_by_id").length>0){
            this.getEmployeeLeaves();
        }
        else{
            this.getMyLeaves();
            this.isEmployee = true;
        }

        this.getLeaveTypes($("#my_id").val());
        this.getEmployees();
        this.getPositions();
    }
});