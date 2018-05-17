var vue_reports = new Vue({
    el:"#reports",
    data:{
        employees:[],
        companies:[],
        batches:[],
        branches:[],
        generate:{
            generate_by:'companies',
            batch_id:0,
            employee_id:0,
            company_id:0,
            branch_id:0,
            date_start:moment().format("YYYY-MM-01"),
            date_end:moment().format("YYYY-MM-DD"),
            report_type:'timesheet',
            format:'PDF'
        },
        result:undefined,
    },
    methods:{
        getData:function(url, field){
            $.get(url, function(data){
                vue_reports[field] = [];
                data.forEach(function(item, i){
                    if(field=='branches'){
                        item.branch_data = JSON.parse(item.branch_data);
                    }
                    vue_reports[field].push(item);
                });
            });
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees', 'employees');
        },
        getCompanies:function(){
            this.getData('../../companies/getCompanies', 'companies');
        },
        getBranches:function(){
            this.getData('../../branches/getBranches', 'branches');
        },
        getBatches:function(){
            this.getData('../../batches/getBatches', 'batches');
        },
        getMonthName:function(n){
            return moment("2000-"+ n +"-01").format("MMMM");
        },
        lastDayInMonth:function(year,month){
            return moment(year+"-"+month+"-01").daysInMonth();
        },
        generateNow:function(){
            $("#generate_button").html("Please wait...");
            $("#generate_button").prop('disabled',true);
            this.result = undefined;
            $.ajax({
                url: '../../reports/generateReport',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: this.generate,
                success: function (data) {
                    if(data.result == 'failed'){
                        toastr.error(data.errors);
                        return;
                    }
                    if(data.generate_by !=undefined)
                        vue_reports.result = data;
                    else
                        toastr.error("No result generated.");
                },
                error:function(jqXHR,textStatus){
                    toastr.error("Error " + textStatus + " occurred.");
                    vue_reports.result = undefined;
                },
                complete:function(){
                    $("#generate_button").html("Generate");
                    $("#generate_button").prop('disabled',false);
                }
            });
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
            else if(field == 'branches')
                output = 'branch_name';
            else
                output = 'batch_name';

            for(var x=0;x<this[field].length;x++){
                if(id == this[field][x][target]){
                    return this[field][x][output];
                }
            }
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
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
        identifyFormat:function(report){
            if(report.indexOf('.pdf') != -1)
                return 'PDF';
            else if(report.indexOf('.xlsx') != -1 || report.indexOf('.xls') != -1)
                return 'EXCEL';
            else
                return '';
        }
    },
    mounted:function(){
        this.getEmployees();
        this.getCompanies();
        this.getBatches();
        this.getBranches();
    }
});