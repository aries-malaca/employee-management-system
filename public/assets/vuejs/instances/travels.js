var vue_travels = new Vue({
    el:"#travels",
    data:{
        auth:{},
        trackable:false,
        show_all:false,
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_travels',
        table1:'travel_history',
        isEmployee:false,
        newTravel:[{
            date_start:moment().format("YYYY-MM-DD"),
            time_start:'09:00',
            time_end:'09:00',
            notes:''
        }],
        travels:[],
        employees:[],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
        positions:[],
        edit_travel:{}
    },
    methods:{
        getEmployeeTravels:function(){
            $.get('../../requests/getRequests/travel', function(data){
                vue_travels.travels = [];
                var pendings = 0;
                data.forEach(function(item,i){
                    vue_travels.travels.push(item);
                    vue_travels.travels[i].request_data = JSON.parse(item.request_data);
                    vue_travels.travels[i].action_data = JSON.parse(item.action_data);
                    if(item.for_my_approval){
                        pendings++;
                    }
                });
                if(pendings>0){
                    $("#travels_counter").html(pendings);
                }
            });
        },
        getEmployees:function(){
            $.get('../../employees/getAllEmployees', function(data){
                vue_travels.employees = [];
                data.forEach(function(item,i){
                    vue_travels.employees.push(item);
                });
            });
        },
        getMyTravels:function(){
            $.get('../../requests/getRequests/travel/' + $("#my_id").val(), function(data){
                vue_travels.travels = [];
                data.forEach(function(item,i){
                    vue_travels.travels.push(item);
                    vue_travels.travels[i].request_data = JSON.parse(item.request_data);
                    vue_travels.travels[i].action_data = JSON.parse(item.action_data);
                });
            });
        },
        clearTravelItems:function(){
            this.newTravel = [];
            this.addTravelItem();
        },
        addTravelItem:function(){
            this.newTravel.push({
                date_start:moment().format("YYYY-MM-DD"),
                time_start:'09:00',
                time_end:'09:00',
                notes:''
            });
        },
        removeTravelItem:function(key){
            if(this.newTravel.length==1){
                toastr.warning("Can't delete all items.");
                return;
            }
            this.newTravel.splice(key,1);
        },
        hasError:function(data ,key){
            if(data.notes == '')
                return ('Please provide notes for travel.');

            if(data.date_start == '' || data.time_start == '' || data.time_end == '')
                return("Invalid date/time at item #"+(key+1));

            if(new Date(data.date_start + " " + data.time_start).getTime() > new Date(data.date_start + " " + data.time_end).getTime() )
                return("Invalid date/time at item #"+(key+1));

            for(var x=0;x<this.newTravel.length;x++){
                if(key == x)
                    continue;

                if(new Date(data.date_start + " " + data.time_start ).getTime() <= new Date(this.newTravel[x].date_start + " " + this.newTravel[x].time_end).getTime() &&
                    new Date(data.date_start + " " + data.time_end).getTime() >= new Date(this.newTravel[x].date_start + " " + this.newTravel[x].time_start).getTime()  )
                {
                    return ("Conflict at item #"+(key+1));
                }
            }

        },
        saveTravel:function(button){
            for(var x=0;x<this.newTravel.length;x++){
                if(e = this.hasError(this.newTravel[x], x)){
                    toastr.error(e);
                    return true;
                }
            }
            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../forms/addTravel',{data:this.newTravel},function(){
                toastr.success("Travel/s added");
                vue_travels.getMyTravels();
                vue_travels.newTravel = [];
                vue_travels.addTravelItem();
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
        deleteTravel:function(travel,button){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            var $btn = $(button.target);
            $btn.button('loading');
            this.postData('../../requests/deleteRequest',{id:travel.id},function(){
                toastr.success("Travel has been deleted");
                vue_travels.getMyTravels();
            },
            function(){
                $btn.button('reset');
            });
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        showActionModal:function(travel, action){
            this.actionModal.request = travel;
            this.actionModal.action=action;
            this.actionModal.notes ='';
            $("#travel-modal").modal("show");
        },
        approveTravel:function(button){
            var $btn = $(button.target);
            $btn.button('loading');
            this.postData('../../requests/approveRequest',this.actionModal,function(){
                toastr.success("Travel has been approved.");
                vue_travels.getEmployeeTravels();
                $("#travel-modal").modal("hide");
            },
            function(){
                $btn.button('reset');
            });
        },
        denyTravel:function(button){
            var $btn = $(button.target);
            $btn.button('loading');
            this.postData('../../requests/denyRequest',this.actionModal,function(){
                toastr.success("Travel has been denied.");
                vue_travels.getEmployeeTravels();
                $("#travel-modal").modal("hide");
            },
            function(){
                $btn.button('reset');
            });
        },
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_travels.positions = [];
                data.forEach(function(item,i){
                    vue_travels.positions.push(item);
                });
            });
        },
        hasMyHistory:function(travel){
            if(Number($("#form-owner").val()) == travel.employee_id && travel.request_data.status != 'pending'){
                return true;
            }
            for(var x=0;x<travel.action_data.approved_by.length;x++){
                if(travel.action_data.approved_by[x].id == Number($("#my_id").val())){
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
        getHours:function(travel){
            var start = travel.request_data.date_start+' '+ travel.request_data.time_start;
            var end = travel.request_data.date_start+' '+ travel.request_data.time_end;
            var m = moment.duration(moment(end).diff(moment(start)));

            return (m._data.hours + ' H ' + (m._data.minutes>0 ? ', ' + m._data.minutes + ' M' :''));
        },
        editRequest:function(request){
            $("#edit-travel").modal("show");
            this.edit_travel = request;
        },
        deleteRequest:function(travel){
            if(!confirm("Are you sure you want to delete this?")){
                return false;
            }
            this.postData('../../requests/deleteRequest',{id:travel.id},function(){
                    toastr.success("Travel has been deleted");
                    $("#edit-travel").modal("hide");
                    vue_travels.getEmployeeTravels();
                },
                function(){
                });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
    },
    computed:{
        pending_travels:function(){
            return this.travels.filter(function(travel){
                return travel.request_data.status == 'pending' && (travel.for_my_approval || vue_travels.isEmployee || vue_travels.show_all);
            });
        },
        travel_history:function(){
            return this.travels.filter(function(travel){
                return vue_travels.hasMyHistory(travel) || (vue_travels.show_all && travel.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_travels.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_travels.trackable = true;
            }
        });
        if($("#approve_by_id").length>0){
            this.getEmployeeTravels();
        }
        else{
            this.getMyTravels();
            this.isEmployee = true;
        }

        this.getEmployees();
        this.getPositions();
    }
});