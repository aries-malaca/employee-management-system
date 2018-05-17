var vue_branch_history = new Vue({
    el:"#branch_history",
    data:{
        branch_id:1,
        start:moment().format("YYYY-MM-01"),
        end:moment().format("YYYY-MM-")+ moment().endOf('month').format("DD"),
        branches:[],
        loading:false,
        setSchedule:{
            branch_id:0,
            time:'',
            date:'',
            employee_id:0,
            single:false,
            name:''
        },
    },
    methods:{
        getData:function(url, field){
            $.get(url, function(data){
                vue_branch_history[field] = [];
                data.forEach(function(item, i){
                    vue_branch_history[field].push(item);
                });
            });
        },
        getBranches:function(){
            this.getData('../../branches/getBranches','branches');
        },
        getBranchHistory:function(){
            if (!jQuery().fullCalendar)
                return;

            this.loading = true;
            $.get("../../schedules/getBranchHistory/" + this.branch_id + "/" + this.start + "/" + this.end,function(data){
                $('#calendar2').fullCalendar('destroy'); // destroy the calendar
                $('#calendar2').fullCalendar({ //re-initialize the calendar
                    disableDragging: true,
                    editable: false,
                    events: data,// events source,
                    gotoDate:vue_branch_history.start,
                    eventClick: function(event) {
                        vue_branch_history.setSchedule.branch_id=event.data.branch_id;
                        vue_branch_history.setSchedule.date=event.start._i;
                        vue_branch_history.setSchedule.time=event._tt;
                        vue_branch_history.setSchedule.employee_id = event.employee_id;
                        vue_branch_history.setSchedule.single = (event.single === true)
                        vue_branch_history.setSchedule.id = event.id
                        vue_branch_history.setSchedule.name = event.name;
                        $("#schedule-modal").modal("show");
                    },
                });
                vue_branch_history.loading = false;
            });
        },
        saveSchedule:function(){
            this.postData('../schedules/saveSingleSchedule', this.setSchedule, function(){
                toastr.success("Successfully added schedule.");
                vue_branch_history.getBranchHistory();
                $("#schedule-modal").modal("hide");
            });
        },
        deleteSchedule:function(){
            if(!confirm("Are you sure you want to delete this single schedule?")){
                return false;
            }
            this.postData('../schedules/deleteSingleSchedule', this.setSchedule, function(){
                toastr.success("Successfully deleted schedule.");
                vue_branch_history.getBranchHistory();
                $("#schedule-modal").modal("hide");
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
    },
    computed:{
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
        branchInfo:function(){
            for(var x=0;x<this.branches.length;x++){
                if(this.branches[x].id == this.branch_id){
                    return this.branches[x];
                }
            }
            return [];
        }
    },
    mounted:function(){
        this.getBranches();
    }
});