var vue_calendar = new Vue({
    el:"#calendar_page",
    data:{
        pagination:[Filter('created_at'),Filter('created_at')],
        newEmployee:{},
        setSchedule:undefined,
        table:'payslips',
        table1:'filtered_transactions',
        payslips:[],
        show_loading_attendance:true,
        transactions:[],
        filter_by:'active-recurring',
        timeSheet:{
            month:moment().format("M"),
            year:moment().format("YYYY"),
            cutoff:Number(moment().format("D")) > 15?2:1,
        },
        attendances:[],
        show_raw:false,
        isChrome:false,
        view_attendance:{
            basic_data:{},
            advanced_data:[]
        }
    },
    methods:{
        getMonthName:function(n){
            return moment("2000-"+ n +"-01").format("MMMM");
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_calendar[field] = [];
                data.forEach(function(item, i){
                    vue_calendar[field].push(item);
                });
            });
        },
        initAttendance:function(){
            if (!jQuery().fullCalendar)
                return;
            let u = this;
            u.show_loading_attendance = true;
            $.get("../../attendance/getAttendance/" + $("#my_id").val() +"/flag/"+ (this.show_raw?1:0),function(data){
                u.show_loading_attendance = false;
                setTimeout(function(){
                    $('#calendar').fullCalendar('destroy'); // destroy the calendar
                    $('#calendar').fullCalendar({ //re-initialize the calendar
                        disableDragging: true,
                        editable: false,
                        events: data,
                        eventClick: function(event) {
                            if(event.id !== undefined){
                                vue_calendar.viewAttendance(event);
                            }
                        }
                    });
                },200);

            });
        },
        initSchedule:function(){
            if (!jQuery().fullCalendar)
                return;
            $.get("../../schedules/getSchedule/" + $("#my_id").val() ,function(data){
                $('#calendar2').fullCalendar('destroy'); // destroy the calendar
                $('#calendar2').fullCalendar({ //re-initialize the calendar
                    disableDragging: true,
                    editable: false,
                    events: data// events source
                });
            });
        },
        viewAttendance:function(event){
            $('#view-modal').modal("show");
            this.view_attendance.basic_data = event;
            this.view_attendance.advanced_data = [];
            if(event.date_credited !== undefined){
                $.get("../../attendance/getAttendance/" + $("#my_id").val() +"/"+event.date_credited,function(data){
                    vue_calendar.view_attendance.advanced_data = data;
                });
            }
        },
        getTransactionTimes:function(transaction){
            var start = new Date(transaction.start_date).getTime()/1000;
            var end = new Date(transaction.end_date).getTime()/1000;
            var times = 0;

            if(transaction.frequency == 'once'){
                return 1;
            }

            while(start<end){
                if(transaction.cutoff == 'every cutoff'){
                    if(new Date(start * 1000).getDate() == 1 || new Date(start * 1000).getDate() == 16){
                        times++;
                    }
                }

                if(transaction.cutoff == 'first cutoff'){
                    if(new Date(start * 1000).getDate() == 1){
                        times++;
                    }
                }

                if(transaction.cutoff == 'second cutoff'){
                    if(new Date(start * 1000).getDate() == 16){
                        times++;
                    }
                }

                start += 86400;
            }
            return times;
        },
        format_number:function(string){
            return string.toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})
        },
        formatDateTime:function(string, format){
            return moment(string).format(format);
        },
        getPayslips: function () {
            this.getData("../../payroll/getPayslips/"+$("#my_id").val(),"payslips");
        },
        getTransactions:function(){
            this.getData('../../transactions/getTransactions/' + $("#my_id").val(),'transactions');
        },
        getTimeSheet:function(){
            $("#loading").show();
            $.get('../../attendance/getTimesheet/' + this.timeSheet.month + '/' + this.timeSheet.year + '/' + this.timeSheet.cutoff + '/'+  $("#my_id").val() , function(data){
                vue_calendar.attendances = [];
                var has_data = false;
                data.forEach(function(item, i){
                    vue_calendar.attendances.push(item);

                    if(moment().format("MM/DD/YYYY") === item.date){
                        has_data = true;
                    }

                });
                
                $("#loading").hide();
            });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        getOutLog:function(data){

        }
    },
    computed:{
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
        },
        filtered:Pagination,
        filtered1:Pagination1,
        filtered_transactions:function(){
            return this.transactions.filter(function(item){
                if(vue_calendar.filter_by == 'active-recurring' && item.frequency == 'recurring'
                    && moment(item.end_date).format('X')>moment().format('X')){
                    return true;
                }

                if(vue_calendar.filter_by == 'inactive-recurring' && item.frequency == 'recurring'
                    && moment(item.end_date).format('X')<moment().format('X')){
                    return true;
                }

                if(vue_calendar.filter_by == 'active-nonrecurring' && item.frequency == 'once'
                    && moment(item.end_date).format('X')>moment().format('X')){
                    return true;
                }

                if(vue_calendar.filter_by == 'inactive-nonrecurring' && item.frequency == 'once'
                    && moment(item.end_date).format('X')<moment().format('X')){
                    return true;
                }

                return false;
            });
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
    },
    mounted:function(){
        this.initAttendance();
        this.initSchedule();
        this.getPayslips();
        this.getTransactions();
        this.getTimeSheet();
        if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())){
            this.isChrome = true;
        }

        $.get("../../employees/getEmployee/"+$("#my_id").val() , function(data){

        })
    }
});

if($("#auth").html() !== undefined){
    var vue_auth = new Vue({
        el:"#auth",
        data:{
            auth:{}
        },
        methods:{
            getAuth:function(){
                $.get("../../employees/getAuth" ,function(data){
                    vue_auth.auth = data;
                });
            }
        },

        mounted:function(){
            this.getAuth();
        }
    });
}