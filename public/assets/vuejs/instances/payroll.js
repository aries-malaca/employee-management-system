var vue_payroll = new Vue({
    el:"#payroll",
    data:{
        pagination:[Filter('category_value'), Filter('date_start')],
        table:'filtered_payrolls',
        table1:'reports',
        employees:[],
        companies:[],
        batches:[],
        branches:[],
        generate:{
            generate_by:'companies',
            batch_id:0,
            branch_id:0,
            employee_id:0,
            company_id:0,
            year:Number(moment().format("YYYY")),
            month:Number(moment().format("M")),
            cutoff: (Number(moment().format("D"))<16? 1: 2),
            employees:[],
            payslip_id:0
        },
        contribution:null,
        payrolls:[],
        reports:[],
        filter_status:'draft',
        result:undefined,
        success_count:0,
        requests:[],
        filter_request:'pending',
        view_request:{}
    },
    methods:{
        changeBy:function(){
            this.result = undefined;
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_payroll[field] = [];
                data.forEach(function(item, i){
                    if(field=='branches'){
                        item.branch_data = JSON.parse(item.branch_data);
                    }
                    if(field=='requests'){
                        item.request_data = JSON.parse(item.request_data);
                    }
                    vue_payroll[field].push(item);
                });
            });
        },
        getPayrolls:function(){
            this.getData('../../payroll/getPayrolls', 'payrolls');
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees', 'employees');
        },
        getCompanies:function(){
            this.getData('../../companies/getCompanies', 'companies');
        },
        getBatches:function(){
            this.getData('../../batches/getBatches', 'batches');
        },
        getRequests: function () {
            this.getData("../../requests/getRequests/salary_adjustment",'requests');
        },
        getBranches:function(){
            this.getData('../../branches/getBranches', 'branches');
        },
        getMonthName:function(n){
            return moment("2000-"+ n +"-01").format("MMMM");
        },
        lastDayInMonth:function(year,month){
            return moment(year+"-"+month+"-01").daysInMonth();
        },
        generatePayroll:function(){
            $("#generate_button").html("Please wait...");
            $("#generate_button").prop('disabled',true);

            $.ajax({
                url: '../../payroll/preparePayroll',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: this.generate,
                success: function (data) {
                    vue_payroll.result = undefined;

                    if(data.result == 'success'){
                        vue_payroll.generate.employees = data.employees;
                        vue_payroll.generate.payslip_id = data.payslip_id;
                        vue_payroll.success_count = 0;
                        vue_payroll.result = data;
                        var p = 0;
                        for(var x=0; x<data.employees.length; x++){
                            var d = {
                                generate_by:'employees',
                                employee_id:data.employees[x].id,
                                year:vue_payroll.generate.year,
                                month:vue_payroll.generate.month,
                                cutoff:vue_payroll.generate.cutoff,
                                payslip_id:data.payslip_id
                            };

                            $.ajax({
                                url: '../../payroll/generatePayroll',
                                method: 'POST',
                                headers: {'X-CSRF-TOKEN': $('#token').val()},
                                data: d,
                                success: function (data) {
                                    for(var x=0;x<vue_payroll.generate.employees.length;x++){
                                        if(data.id == vue_payroll.generate.employees[x].id){
                                            vue_payroll.generate.employees[x].success = true;
                                            vue_payroll.generate.employees[x].payslip_id = data.payslip_id;
                                            vue_payroll.success_count++;
                                            if(vue_payroll.success_count == vue_payroll.generate.employees.length){
                                                vue_payroll.getPayrolls();
                                            }
                                        }
                                    }
                                }
                            });
                        }
                        $("#view-modal").modal({backdrop: 'static', keyboard: false});
                        $("#view-modal").modal("show");
                        $("#generate_button").html("Compute Payroll");
                        $("#generate_button").prop('disabled',false);
                    }
                    else{
                        $("#generate_button").html("Compute Payroll");
                        $("#generate_button").prop('disabled',false);
                        toastr.error(data.errors);
                    }
                }
            });
        },
        generateReport:function(object){
            $.ajax({
                url: '../../payroll/generateReport',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: object,
                success: function (data) {
                    if(data.result === 'success')
                        $("#view-report-modal").modal("show");
                    else
                        toastr.error("Failed to Generate report");
                }
            });
        },
        generateOT:function(object){
            $.ajax({
                url: '../../payroll/generateOT',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: object,
                success: function (data) {
                    if(data.result === 'success')
                        $("#view-ot-modal").modal("show");
                    else
                        toastr.error("Failed to Generate report");
                }
            });
        },
        generateContributions:function(object){
            let u = this;
            $.ajax({
                url: '../../payroll/generateContributions',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: object,
                success: function (data) {
                    if(data.result === 'success'){
                        vue_payroll.contribution = data.files
                        $("#view-contribution-modal").modal("show");
                    }
                    else
                        toastr.error("Failed to Generate report");
                }
            });
        },
        regenerate:function (d) {
            for(var x=0;x<vue_payroll.generate.employees.length;x++){
                if(d.employee_id == vue_payroll.generate.employees[x].id){
                    if(vue_payroll.generate.employees[x].success === true)
                        vue_payroll.success_count--;


                    vue_payroll.generate.employees[x].success = null;
                }
            }

            $.ajax({
                url: '../../payroll/generatePayroll',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: d,
                success: function (data) {
                    for(var x=0;x<vue_payroll.generate.employees.length;x++){
                        if(data.id == vue_payroll.generate.employees[x].id){

                            vue_payroll.generate.employees[x].payslip_id = data.payslip_id;
                            if(vue_payroll.generate.employees[x].success !== true)
                                vue_payroll.success_count++;

                            vue_payroll.generate.employees[x].success = true;
                            if(vue_payroll.success_count == vue_payroll.generate.employees.length){
                                vue_payroll.getPayrolls();
                            }
                        }
                    }
                },
                error:function(xhr, status, message){
                    toastr.error(message);
                }
            });
        },
        generateNow:function(){
            $("#compute_button").html("Please wait...");
            $("#compute_button").prop('disabled',true);
            this.generate.employees = [];
            this.result = undefined;
            $.ajax({
                url: '../../payroll/generatePayroll',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: this.generate,
                success: function (data) {
                    if(data.result == 'failed'){
                        toastr.error(data.errors);
                        return;
                    }
                    if(data.generate_by !=undefined){
                        vue_payroll.result = data;
                        $("#view-modal").modal("show");
                    }
                    else
                        toastr.error("No result generated.");
                },
                error:function(jqXHR,textStatus){
                    toastr.error("Error " + textStatus + " occurred.");
                    vue_payroll.result = undefined;
                },
                complete:function(){
                    $("#compute_button").html("Compute Payroll");
                    $("#compute_button").prop('disabled',false);
                    vue_payroll.getPayrolls();
                }
            });
        },
        closeGeneration:function(){
            for(var x=0;x<this.generate.employees.length;x++){
                if(this.generate.employees[x].success == null){
                    alert("Unable to close this modal, operation currently in progress.");
                    return false
                }
            }
            $("#view-modal").modal("hide");
        },
        generatedBy:function(string){
            if(string == 'employees')
                return 'Employee';
            else if (string == 'companies')
                return 'Company';
            else if (string == 'branches')
                return 'Branch';
            else
                return 'Batch';
        },
        getValue:function(id,field){
            var target = (field=='employees'?'user_id':'id');
            var output = '';
            if(field == 'employees')
                output = 'name';
            else if(field == 'companies')
                output = 'company_name';
            else if(field == 'batches')
                output = 'batch_name';
            else if(field == 'branches')
                output = 'branch_name';

            for(var x=0;x<this[field].length;x++){
                if(id == this[field][x][target]){
                    return this[field][x][output];
                }
            }
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        draftPayroll:function(payroll){
            this.postData('../../payroll/draftPayroll',{id:payroll.id},function(){
                toastr.success("Successfully drafted payroll.");
                vue_payroll.getPayrolls();
            });
        },
        publishPayroll:function(payroll){
            this.postData('../../payroll/publishPayroll',{id:payroll.id},function(){
                toastr.success("Successfully published payroll.");
                vue_payroll.getPayrolls();
            });
        },
        deletePayroll:function(payroll){
            if(!confirm("Are you sure you want to delete this payroll?")){
                return false;
            }

            if(this.result != undefined){
                if(payroll.id == this.result.payslip_id){
                    this.result = undefined;
                }
            }

            this.postData('../../payroll/deletePayroll',{id:payroll.id},function(){
                toastr.success("Successfully deleted payroll.");
                vue_payroll.getPayrolls();
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
        viewRequest:function(request){
            this.view_request = {
                id:request.id,
                amount:request.request_data.amount,
                feedback:request.request_data.feedback,
                status:request.request_data.status,
                discrepancy:request.request_data.discrepancy,
                period:request.request_data.period,
                target: request.request_data.target===undefined?request.request_data.period:request.request_data.target,
            };
            $("#view-request-modal").modal("show");
        },
        updateRequest:function(){
            this.postData('../../requests/updateRequest',this.view_request,function(){
                toastr.success("Successfully updated request.");
                vue_payroll.getRequests();
                $("#view-request-modal").modal("hide");
            });
        },
        inArray:function(array, date_start){
            for(var x=0;x<array.length;x++) {
                if (array[x].date_start === date_start)
                    return true;
            }
            return false;
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    computed:{
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
        filtered:Pagination,
        filtered1:Pagination1,
        filtered_payrolls:function(){
            return this.payrolls.filter(function(item){
                return (item.status==vue_payroll.filter_status);
            });
        },
        filtered_requests:function(){
            return this.requests.filter(function(item){
                return item.request_data.status === vue_payroll.filter_request
            });
        }
    },
    mounted:function(){
        this.getEmployees();
        this.getCompanies();
        this.getBatches();
        this.getBranches();
        this.getPayrolls();
        this.getRequests();
    },
    watch:{
        payrolls:function(){
            this.reports = [];
            for(var x=0; x<this.payrolls.length;x++){
                if(!this.inArray(this.reports, this.payrolls[x].date_start )){
                    this.reports.push({
                        date_start:this.payrolls[x].date_start,
                        date_end:this.payrolls[x].date_end,
                    });
                }
            }
        }
    }
});