var vue_home = new Vue({
    el:"#home",
    data:{
        pagination:[Filter('name'),Filter('name'),Filter('name')],
        table:'present_employees',
        table1:'absents',
        table2:'onRestdays',
        employees:[],
        present_employees:[],
        requests:[],
        present_employees_array:[],
        celebrants:[],
        enrolled:0,
        isChrome:false,
        view_attendance:{
            basic_data:{},
            advanced_data:[]
        },
        unsync:[],
    },
    methods:{
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        getOutLog:function(data){

        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_home[field] = [];
                if(field == 'present_employees')
                    vue_home.present_employees_array = [];
                data.forEach(function(item, i){
                    vue_home[field].push(item);
                    if(field == 'employees')
                        vue_home[field][i].branch_name = vue_home.getCurrentBranch(item.user_id);
                    if(field == 'present_employees')
                        vue_home.present_employees_array.push(item.user_id);

                });
            });
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees/with_schedule', 'employees');
        },
        getEnrolledCount:function(){
            $.get('../../fingerprint/getEnrolledCount', function(data){
                vue_home.enrolled = data;
            });
        },
        getPresentEmployees:function(){
            this.getData('../../attendance/getPresentEmployees/' + moment().format("YYYY-MM-DD"), 'present_employees');
        },
        getRequests:function(){
            this.getData('../../requests/getRequests/all', 'requests');
        },
        getCelebrants:function(){
            this.getData('../../api/getBirthdayCelebrants/'+moment().format("MM")+'/'+moment().format("DD"),'celebrants');
        },
        getUnsync:function(){
            this.getData('../../fingerprint/getUnsync', "unsync");
        },
        isRestDay:function(employee_schedules){
            for(var y=0;y<employee_schedules.length;y++){
                if(moment().format('YYYY-MM-DD')==moment(employee_schedules[y].schedule_start).format('YYYY-MM-DD') &&
                    moment().format('YYYY-MM-DD')==moment(employee_schedules[y].schedule_end).format('YYYY-MM-DD') &&
                    employee_schedules[y].schedule_data == '00:00'){
                    return true;
                }

                if(moment().format('X')>=moment(employee_schedules[y].schedule_start).format('X') &&
                    moment().format('X')<=moment(employee_schedules[y].schedule_end).format('X')){
                    if(employee_schedules[y].schedule_data.length>6){
                        var s = JSON.parse(employee_schedules[y].schedule_data);
                        if(s[Number(moment().format('d'))] == '00:00'){
                            return true;
                        }
                    }
                }
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
        employeeData:function(employee_id){
            for(var x=0;x<this.employees.length;x++){
                if(employee_id == this.employees[x].user_id){
                    return this.employees[x];
                }
            }
        },
        viewAttendance:function(id){
            $('#view-modal').modal("show");
            $.get("../../attendance/getAttendance/" + id +"/"+moment().format("YYYY-MM-DD"),function(data){
                vue_home.view_attendance.advanced_data = data;
            });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
        format_number:function(string){
            return string.toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})
        }
    },
    computed:{
        absents:function(){
            return this.employees.filter(function (o) {
                return !vue_home.isRestDay(o.schedules) && vue_home.present_employees_array.indexOf(o.user_id) == -1;
            });
        },
        onRestdays:function(){
            return this.employees.filter(function (o) {
                return vue_home.isRestDay(o.schedules) ;
            });
        },
        onLeaves:function(){
            return this.employees.filter(function (o) {
                return o.onLeave==1;
            });
        },
        countRequests:function(){
            var count = 0;
            var pending = [];
            for(var x=0; x<this.requests.length;x++){
                if(this.requests[x].for_my_approval && this.requests[x].request_type !== 'salary_adjustment'){
                    count++;
                    pending.push(this.requests[x]);
                }
            }
            console.log(pending);
            return count;
        },
        enrolled_rate:function(){
            return this.format_number((this.enrolled/this.employees.length) * 100)
        },
        lbo_rate:function(){
            var count = 0;
            for(var x=0;x<this.employees.length;x++){
                if(this.employees[x].lbo_identifier.length>4){
                    count++;
                }
            }
            return this.format_number((count/this.employees.filter(function(i){
                    return (i.position_name == 'Wax Technician');
                }).length) * 100);
        },
        system_login:function(){
            var logins = 0;
            for(var x=0;x<this.employees.length;x++){
                var diff = Number(moment().format('X')) - Number(moment(this.employees[x].last_activity).format('X')) ;
                if(diff<86400){
                    logins++;
                }
            }
            return this.format_number( (logins/this.employees.length)* 100) ;
        },
        filtered:Pagination,
        filtered1:Pagination1,
        filtered2:Pagination2
    },
    mounted:function(){
        this.getEmployees();
        this.getRequests();
        this.getPresentEmployees();
        this.getCelebrants();
        this.getEnrolledCount();
        this.getUnsync();
        if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())){
            this.isChrome = true;
        }


    }
});