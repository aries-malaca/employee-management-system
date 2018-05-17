var vue_offsets = new Vue({
    el:"#offsets",
    data:{
        auth:{},
        isEmployee:false,
        trackable:false,
        show_all:false,
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_offsets',
        table1:'offset_history',
        previous_credits:0,
        newOffset:{
            duties:[{
                date_start:moment().format("YYYY-MM-DD"),
                date_end:moment().format("YYYY-MM-DD"),
                time_start:"09:00",
                time_end:"09:00",
                notes:''
            }],
            offsets:[{
                date_start:moment().format("YYYY-MM-DD"),
                date_end:moment().format("YYYY-MM-DD"),
                time_start:"09:00",
                time_end:"09:00",
                notes:''
            }],
            duty_sum:0,
            offset_sum:0,
            overhead:0
        },
        offsets:[],
        credited:[],
        positions:[],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
        viewOffset:{},
        edit_offset:{}
    },
    methods:{
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
        addDutyItem:function(){
            this.newOffset.duties.push({
                date_start:moment().format("YYYY-MM-DD"),
                date_end:moment().format("YYYY-MM-DD"),
                time_start:"09:00",
                time_end:"09:00",
                notes:''
            });
        },
        removeDutyItem:function(key){
            this.newOffset.duties.splice(key,1);
        },
        addOffsetItem:function(){
            this.newOffset.offsets.push({
                date_start:moment().format("YYYY-MM-DD"),
                date_end:moment().format("YYYY-MM-DD"),
                time_start:"09:00",
                time_end:"09:00",
                notes:''
            });
        },
        removeOffsetItem:function(key){
            if(this.newOffset.offsets.length==1){
                toastr.warning("Can't delete all items.");
                return;
            }
            this.newOffset.offsets.splice(key,1);
        },
        hasError:function(data ,key, field){
            for(var x=0;x<this.newOffset[field].length;x++){
                if(key == x)
                    continue;

                if(new Date(data.date_start + " " + data.time_start ).getTime() <= new Date(this.newOffset[field][x].date_end + " " + this.newOffset[field][x].time_end).getTime() &&
                    new Date(data.date_end + " " + data.time_end).getTime() >= new Date(this.newOffset[field][x].date_start + " " + this.newOffset[field][x].time_start).getTime()  )
                {
                    return ("Conflict at item #"+(key+1));
                }
            }
        },
        saveOffset:function(button){
            if(this.total_duty_hours===false || this.total_offset_hours===false){
                toastr.error("Invalid Date/Time.");
                return false;
            }

            if(this.newOffset.offset_sum > (this.newOffset.duty_sum+this.previous_credits)){
                toastr.error("Not enough offset credits.");
                return false;
            }
            if(this.newOffset.offset_sum==0){
                toastr.error("Provide usage date/time.");
                return false;
            }

            var $btn = $(button.target);
            $btn.button('loading');

            if(this.newOffset.duty_sum>=this.newOffset.offset_sum){
                this.newOffset.overhead = this.newOffset.duty_sum - this.newOffset.offset_sum;
            }

            this.postData('../../forms/addOffset', this.newOffset,function(){
                    toastr.success("Offset/s added");
                    vue_offsets.getMyOffsets()
                    vue_offsets.newOffset={
                        duties:[{
                            date_start:moment().format("YYYY-MM-DD"),
                            date_end:moment().format("YYYY-MM-DD"),
                            time_start:"09:00",
                            time_end:"09:00",
                            notes:''
                        }],
                            offsets:[{
                            date_start:moment().format("YYYY-MM-DD"),
                            date_end:moment().format("YYYY-MM-DD"),
                            time_start:"09:00",
                            time_end:"09:00",
                            notes:''
                        }],
                            duty_sum:0,
                            offset_sum:0,
                            overhead:0
                    }
            },
            function(){
                $btn.button('reset');
            });
        },
        deleteOffset:function(offset,button){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }

            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/deleteRequest',{id:offset.id},function(){
                    toastr.success("Offset has been deleted");
                    vue_offsets.getMyOffsets();
                },
                function(){
                    $btn.button('reset');
                });
        },
        hasMyHistory:function(offset){
            if(Number($("#form-owner").val()) == offset.employee_id && offset.request_data.status != 'pending'){
                return true;
            }
            for(var x=0;x<offset.action_data.approved_by.length;x++){
                if(offset.action_data.approved_by[x].id == Number($("#my_id").val())){
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
        getEmployeeOffsets:function(){
            $.get('../../requests/getRequests/offset', function(data){
                vue_offsets.offsets = [];
                var pendings = 0;
                data.forEach(function(item,i){
                    vue_offsets.offsets.push(item);
                    vue_offsets.offsets[i].request_data = JSON.parse(item.request_data);
                    vue_offsets.offsets[i].action_data = JSON.parse(item.action_data);

                    if(item.for_my_approval){
                        pendings++;
                    }
                });
                if(pendings>0){
                    $("#offsets_counter").html(pendings);
                }
                else{
                    $("#offsets_counter").html("");
                }
            });
        },
        getEmployees:function(){
            $.get('../../employees/getAllEmployees', function(data){
                vue_offsets.employees = [];
                data.forEach(function(item,i){
                    vue_offsets.employees.push(item);
                });
            });
        },
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_offsets.positions = [];
                data.forEach(function(item,i){
                    vue_offsets.positions.push(item);
                });
            });
        },
        getMyOffsets:function(){
            $.get('../../requests/getRequests/offset/' + $("#my_id").val(), function(data){
                vue_offsets.offsets = [];
                data.forEach(function(item,i){
                    vue_offsets.offsets.push(item);
                    vue_offsets.offsets[i].request_data = JSON.parse(item.request_data);
                    vue_offsets.offsets[i].action_data = JSON.parse(item.action_data);
                });
            });
        },
        showActionModal:function(offset, action){
            this.actionModal.request = offset;
            this.actionModal.action=action;
            this.actionModal.notes ='';
            $("#offset-modal").modal("show");
        },
        approveOffset:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/approveRequest',this.actionModal,function(){
                toastr.success("Offset has been approved.");
                vue_offsets.getEmployeeOffsets();
                $("#offset-modal").modal("hide");
                $.get('../../requests/sendNotification/'+vue_offsets.actionModal.request.id +'/'+ $("#my_id").val(), function(data){
                });
                $.get('../../requests/sendConfirmation/'+vue_offsets.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        denyOffset:function(button){
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../requests/denyRequest',this.actionModal,function(){
                toastr.success("Adjustment has been denied.");
                vue_offsets.getEmployeeOffsets();
                $("#offset-modal").modal("hide");
                $.get('../../requests/sendConfirmation/'+vue_offsets.actionModal.request.id, function(data){
                });
            },
            function(){
                $btn.button('reset');
            });
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        getHours:function(data){
            var start = data.date_start+' '+ data.time_start;
            var end = data.date_end+' '+ data.time_end;
            var m = moment.duration(moment(end).diff(moment(start)));

            return {hours:m._data.hours, minutes:m._data.minutes};
        },
        showOffsetDetails:function(offset){
            $("#view-offset-modal").modal("show");
            this.viewOffset = offset;
        },
        editRequest:function(request){
            $("#edit-offset").modal("show");
            this.edit_offset = request;
        },
        deleteRequest:function(travel){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../requests/deleteRequest',{id:offset.id},function(){
                    toastr.success("Offset has been deleted");
                    $("#edit-offset").modal("hide");
                    vue_offsets.getEmployeeOffsets();
                },
                function(){
                });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
    },
    computed:{
        total_duty_hours:function(){
            var diff = 0;
            for(var x=0;x<this.newOffset.duties.length;x++){
                var datetime_start = this.newOffset.duties[x].date_start + ' ' + this.newOffset.duties[x].time_start;
                var datetime_end = this.newOffset.duties[x].date_end + ' ' + this.newOffset.duties[x].time_end;
                if(moment(datetime_end).diff(datetime_start, 'minutes')<0){
                    return false;
                }
                diff += moment(datetime_end).diff(datetime_start, 'minutes')
            }
            diff = diff/60;
            this.newOffset.duty_sum = Number(diff.toFixed(2));
            return Number(diff.toFixed(2));
        },
        total_offset_hours:function(){
            var diff = 0;
            for(var x=0;x<this.newOffset.offsets.length;x++){
                var datetime_start = this.newOffset.offsets[x].date_start + ' ' + this.newOffset.offsets[x].time_start;
                var datetime_end = this.newOffset.offsets[x].date_end + ' ' + this.newOffset.offsets[x].time_end;
                if(moment(datetime_end).diff(datetime_start, 'minutes')<0){
                    return false;
                }
                diff += moment(datetime_end).diff(datetime_start, 'minutes')
            }
            diff = diff/60;
            this.newOffset.offset_sum = Number(diff.toFixed(2));
            return Number(diff.toFixed(2));
        },
        pending_offsets:function(){
            return this.offsets.filter(function(offset){
                return offset.request_data.status == 'pending' && (offset.for_my_approval || vue_offsets.isEmployee || vue_offsets.show_all);
            });
        },
        offset_history:function(){
            return this.offsets.filter(function(offset){
                return vue_offsets.hasMyHistory(offset) || (vue_offsets.show_all && offset.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_offsets.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_offsets.trackable = true;
            }
        });
        if($("#approve_by_id").length>0){
            this.getEmployeeOffsets();
        }
        else{
            this.getMyOffsets();
            this.isEmployee = true;
        }

        this.getEmployees();
        this.getPositions();
    }
});