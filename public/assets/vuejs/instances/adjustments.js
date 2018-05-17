var vue_adjustments = new Vue({
    el:"#adjustments",
    data:{
        auth:{},
        trackable:false,
        show_all:false,
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_adjustments',
        table1:'adjustment_history',
        isEmployee:false,
        newAdjustment:[{
            date:moment().format("YYYY-MM-DD"),
            time:'09:00',
            mode:'IN',
            notes:''
        }],
        adjustments:[],
        employees:[],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
        positions:[],
        edit_adjustment:{}
    },
    methods:{
        getEmployeeAdjustments:function(){
            $.get('../../requests/getRequests/adjustment', function(data){
                vue_adjustments.adjustments = [];
                var pendings = 0;
                data.forEach(function(item,i){
                    vue_adjustments.adjustments.push(item);
                    vue_adjustments.adjustments[i].request_data = JSON.parse(item.request_data);
                    vue_adjustments.adjustments[i].action_data = JSON.parse(item.action_data);

                    if(item.for_my_approval){
                        pendings++;
                    }
                });
                if(pendings>0){
                    $("#adjustments_counter").html(pendings);
                }
                else{
                    $("#adjustments_counter").html("");
                }
            });
        },
        getEmployees:function(){
            $.get('../../employees/getAllEmployees', function(data){
                vue_adjustments.employees = [];
                data.forEach(function(item,i){
                    vue_adjustments.employees.push(item);
                });
            });
        },
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_adjustments.positions = [];
                data.forEach(function(item,i){
                    vue_adjustments.positions.push(item);
                });
            });
        },
        getMyAdjustments:function(){
            $.get('../../requests/getRequests/adjustment/' + $("#my_id").val(), function(data){
                vue_adjustments.adjustments = [];
                data.forEach(function(item,i){
                    vue_adjustments.adjustments.push(item);
                    vue_adjustments.adjustments[i].request_data = JSON.parse(item.request_data);
                    vue_adjustments.adjustments[i].action_data = JSON.parse(item.action_data);
                });
            });
        },
        clearAdjustmentItems:function(){
            this.newAdjustment = [];
            this.addAdjustmentItem();
        },
        addAdjustmentItem:function(){
            this.newAdjustment.push({
                date:moment().format("YYYY-MM-DD"),
                time:'09:00',
                mode:'IN',
                notes:''
            });
        },
        removeAdjustmentItem:function(key){
            if(this.newAdjustment.length==1){
                toastr.warning("Can't delete all items.");
                return;
            }
            this.newAdjustment.splice(key,1);
        },
        hasError:function(data ,key){
            if(data.date == '' || data.time == ''){
                return("Invalid date/time at item #"+(key+1));
            }
            if(data.notes == '')
                return ('Please provide notes for adjustment.');

            for(var x=0;x<this.newAdjustment.length;x++){
                if(key == x){
                    continue;
                }
                if(data.date == this.newAdjustment[x].date && data.mode == this.newAdjustment[x].mode){
                    return("Duplicate date with same mode at item #"+(key+1));
                }
            }


        },
        saveAdjustment:function(button){
            for(var x=0;x<this.newAdjustment.length;x++){
                if(e = this.hasError(this.newAdjustment[x], x)){
                    toastr.error(e);
                    return true;
                }
            }
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../forms/addAdjustment',{data:this.newAdjustment},function(){
                toastr.success("Adjustment/s added");
                vue_adjustments.getMyAdjustments();
                vue_adjustments.newAdjustment = [];
                vue_adjustments.addAdjustmentItem();
            },
            function(){
                $btn.button('reset');
            });
        },
        deleteAdjustment:function(adjustment,button){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/deleteRequest',{id:adjustment.id},function(){
                toastr.success("Adjustment has been deleted");
                vue_adjustments.getMyAdjustments();
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
        showActionModal:function(adjustment, action){
            this.actionModal.request = adjustment;
            this.actionModal.action=action;
            this.actionModal.notes ='';
            $("#adjustment-modal").modal("show");
        },
        approveAdjustment:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/approveRequest',this.actionModal,function(){
                toastr.success("Adjustment has been approved.");
                vue_adjustments.getEmployeeAdjustments();
                $("#adjustment-modal").modal("hide");

                $.get('../../requests/sendNotification/'+vue_adjustments.actionModal.request.id +'/'+ $("#my_id").val(), function(data){
                });
                $.get('../../requests/sendConfirmation/'+vue_adjustments.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        denyAdjustment:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/denyRequest',this.actionModal,function(){
                toastr.success("Adjustment has been denied.");
                vue_adjustments.getEmployeeAdjustments();
                $("#adjustment-modal").modal("hide");
                
                $.get('../../requests/sendConfirmation/'+vue_adjustments.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        hasMyHistory:function(adjustment){
            if(Number($("#form-owner").val()) == adjustment.employee_id && adjustment.request_data.status != 'pending'){
                return true;
            }
            for(var x=0;x<adjustment.action_data.approved_by.length;x++){
                if(adjustment.action_data.approved_by[x].id == Number($("#my_id").val())){
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
            $("#edit-adjustment").modal("show");
            this.edit_adjustment = request;
        },
        deleteRequest:function(adjustment){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../requests/deleteRequest',{id:adjustment.id},function(){
                    toastr.success("Adjustment has been deleted");
                    $("#edit-adjustment").modal("hide");
                    vue_adjustments.getEmployeeAdjustments();
                },
                function(){
                });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
    },
    computed:{
        pending_adjustments:function(){
            return this.adjustments.filter(function(adjustment){
                return adjustment.request_data.status == 'pending' && (adjustment.for_my_approval || vue_adjustments.isEmployee || vue_adjustments.show_all);
            });
        },
        adjustment_history:function(){
            return this.adjustments.filter(function(adjustment){
                return vue_adjustments.hasMyHistory(adjustment)  || (vue_adjustments.show_all && adjustment.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_adjustments.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_adjustments.trackable = true;
            }
        });
        if($("#approve_by_id").length>0){
            this.getEmployeeAdjustments();
        }
        else{
            this.getMyAdjustments();
            this.isEmployee = true;
        }

        this.getEmployees();
        this.getPositions();
    }
});