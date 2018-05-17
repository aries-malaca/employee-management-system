var vue_companies = new Vue({
    el:"#companies",
    data:{
        pagination:[Filter('name')],
        table:'display_employees',
        newCompany:{
            id:0,
            company_name:'',
            company_address:'',
            company_email:'',
            company_phone:'',
            company_logo:'',
            company_active:1,
            company_data:{
                sss_id:"",
                pagibig_id:"",
                philhealth_id:"",
                tin_id:""
            }
        },
        display:{
            company_name:'',
            id:0
        },
        companies:[],
        employees:[]
    },
    methods:{
        clearForm:function(){
            this.newCompany={
                id:0,
                company_name:'',
                company_address:'',
                company_email:'',
                company_phone:'',
                company_logo:'',
                company_active:1,
                company_data:{
                    sss_id:"",
                    pagibig_id:"",
                    philhealth_id:"",
                    tin_id:""
                }
            }
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_companies[field] = [];
                data.forEach(function(item, i){
                    vue_companies[field].push(item);
                });
            });
        },
        getCompanies:function(){
            this.getData('../../companies/getCompanies', 'companies');
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees', 'employees');
        },
        showAddModal:function(){
            if(this.newCompany.id != 0){
                this.clearForm();
            }
            $("#add-modal").modal("show");
        },
        showViewModal:function(company){
            this.display.company_name = company.company_name;
            this.display.id = company.id;
            $("#view-modal").modal("show");
        },
        editCompany:function(company){
            this.newCompany = {
                id:company.id,
                company_name:company.company_name,
                company_address:company.company_address,
                company_email:company.company_email,
                company_phone:company.company_phone,
                company_logo:'',
                company_active:company.company_active,
                company_data:{
                    sss_id:company.company_data.sss_id,
                    pagibig_id:company.company_data.pagibig_id,
                    philhealth_id:company.company_data.philhealth_id,
                    tin_id:company.company_data.tin_id
                }
            }
            $("#add-modal").modal("show");
        },
        saveCompany:function(){
            var url,msg;
            if(this.newCompany.id == 0){
                url = '../../companies/addCompany';
                msg = "Successfully added company.";
            }
            else{
                url = '../../companies/updateCompany';
                msg = "Successfully updated company.";
            }

            this.postData(url, this.newCompany, function(){
                toastr.success(msg);
                $("#add-modal").modal("hide");
                vue_companies.clearForm();
                vue_companies.getCompanies();
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
        activateCompany:function(company){
            this.postData('../../companies/activateCompany',{id:company.id}, function(){
                toastr.success(company.company_name + " activated.");
                vue_companies.getCompanies();
            });
        },
        deactivateCompany:function(company){
            this.postData('../../companies/deactivateCompany',{id:company.id}, function(){
                toastr.success(company.company_name + " deactivated.");
                vue_companies.getCompanies();
            });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    mounted:function(){
        this.getCompanies();
        this.getEmployees();
    },
    computed:{
        filtered1:Pagination,
        display_employees:function(){
            return this.employees.filter(function(item){
                if(item.company_id == vue_companies.display.id){
                    return true;
                }
            });
        }
    }
});