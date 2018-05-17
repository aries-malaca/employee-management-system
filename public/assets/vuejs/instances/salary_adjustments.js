var vue_salary_adjustments = new Vue({
    el:"#salary_adjustments",
    data:{
        auth:{},
        trackable:false,
        show_all:false,
        isEmployee:false,
        employees:[],
        salary_adjustments:[],
        positions:[],
        periods:[],
        pagination:[Filter('requested_at',0),Filter('requested_at',0)],
        table:'pending_salary_adjustments',
        table1:'salary_adjustment_history',
        newSalaryAdjustment:[{
            period:null,
            discrepancy:'unpaid_day',
            notes:'',
        }],
        actionModal:{
            request:{},
            notes:'',
            action:'approve'
        },
    },
    methods:{
        addSalaryAdjustmentItem:function(){
            this.newSalaryAdjustment.push({
                period:null,
                discrepancy:'unpaid_day',
                notes:'',
            });
        },
        getEmployees:function(){
            $.get('../../employees/getAllEmployees', function(data){
                vue_salary_adjustments.employees = [];
                data.forEach(function(item,i){
                    vue_salary_adjustments.employees.push(item);
                });
            });
        },
        getPositions:function(){
            $.get('../../positions/getPositions', function(data){
                vue_salary_adjustments.positions = [];
                data.forEach(function(item,i){
                    vue_salary_adjustments.positions.push(item);
                });
            });
        },
        getPayrollPeriods:function(){
            $.get('../../payroll/getPayrollPeriods/'+ $("#my_id").val(), function(data){
                vue_salary_adjustments.periods = [];
                data.forEach(function(item,i){
                    vue_salary_adjustments.periods.push(item);
                });
            });
        },
        computeGross:function(key){
            for(var x=0;x<this.periods.length;x++){
                if(this.newSalaryAdjustment[key].period === this.periods[x].start){
                    var payslip_data = this.periods[x].payslip_data;
                }
            }

            if(payslip_data === undefined)
                return 0;

            if(this.newSalaryAdjustment[key].discrepancy!=='unpaid_overtime'){
                return 1 * payslip_data.salary_rates.daily + payslip_data.cola_rate;
            }

            return 0;
        },
        hasError:function(data ,key){
            if(data.period == '' || data.period===null){
                return("Invalid Period at item #"+(key+1));
            }
            if(data.notes == '')
                return ('Please provide notes.');
        },
        showPrompt:function(adj){
            var notes = prompt("Please input your updated notes:", );

            this.postData('../../requests/updateNotes',{id:adj.id, notes:notes},function(){
                toastr.success("Successfully updated request.");
                vue_salary_adjustments.getMyAdjustments();
            });
        },
        saveSalaryAdjustment:function(button){
            for(var x=0;x<this.newSalaryAdjustment.length;x++){
                if(e = this.hasError(this.newSalaryAdjustment[x], x)){
                    toastr.error(e);
                    return true;
                }
            }

            var $btn = $(button.target);
            $btn.button('loading');

            this.postData('../../forms/addSalaryAdjustment',{data:this.newSalaryAdjustment},function(){
                    toastr.success("Salary Adjustment/s added");
                    vue_salary_adjustments.getMyAdjustments();
                    vue_salary_adjustments.newSalaryAdjustment = [];
                    vue_salary_adjustments.addSalaryAdjustmentItem();
                },
                function(){
                    $btn.button('reset');
                });

        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
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
        getMyAdjustments:function(){
            $.get('../../requests/getRequests/salary_adjustment/' + $("#my_id").val(), function(data){
                vue_salary_adjustments.salary_adjustments = [];
                data.forEach(function(item,i){
                    vue_salary_adjustments.salary_adjustments.push(item);
                    vue_salary_adjustments.salary_adjustments[i].request_data = JSON.parse(item.request_data);
                    vue_salary_adjustments.salary_adjustments[i].action_data = JSON.parse(item.action_data);
                });
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
                    vue_salary_adjustments.getMyAdjustments();
                },
                function(){
                    $btn.button('reset');
                });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
    },
    computed:{
        pending_salary_adjustments:function(){
            return this.salary_adjustments.filter(function(adjustment){
                return adjustment.request_data.status == 'pending' && (adjustment.for_my_approval || vue_salary_adjustments.isEmployee || vue_salary_adjustments.show_all);
            });
        },
        salary_adjustment_history:function(){
            return this.salary_adjustments.filter(function(adjustment){
                return (vue_salary_adjustments.show_all || adjustment.request_data.status != 'pending');
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        $.get('../../employees/getAuth', function(data){
            vue_salary_adjustments.auth = data;
            if(data.level == 1 || data.level == 5){
                vue_salary_adjustments.trackable = true;
            }
        });
        if($("#approve_by_id").length>0){

        }
        else{
            this.getMyAdjustments();
            this.isEmployee = true;
        }

        this.getEmployees();
        this.getPayrollPeriods();
        this.getPositions();
    }
});