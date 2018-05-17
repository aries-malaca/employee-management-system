var vue_banks = new Vue({
    el:"#banks",
    data:{
        banks:[],
        employees:[],
        pagination:[Filter('name')],
        table:'employees',
        newBank:{
            id:0,
            bank_name:'',
            bank_shortname:''
        },
        editor:{
            id:0,
            bank_code:0,
            bank_number:'',
            name:''
        }
    },
    methods:{
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
        getData:function(url, field){
            $.get(url, function(data){
                vue_banks[field] = [];
                data.forEach(function(item, i){
                    vue_banks[field].push(item);
                });
            });
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees','employees');
        },
        getBanks:function(){
            this.getData('../../banks/getBanks','banks');
        },
        editBank:function(bank){
            this.newBank = {
                id:bank.id,
                bank_name:bank.bank_name,
                bank_shortname:bank.bank_shortname
            }
            $("#add-modal").modal("show");
        },
        deleteBank:function(bank){
            if(!confirm("Are you sure you want to delete this bank?"))
                return false;

            this.postData('../../banks/deleteBank',{id:bank.id}, function(){
                toastr.success(banks.bank_name + " has been deleted.");
                vue_banks.getBanks();
            });
        },
        addBank:function(){
            this.postData('../../banks/addBank',this.newBank, function(){
                toastr.success("Successfully added bank.");
                $("#add-modal").modal("hide");
                vue_banks.getBanks();
            });
        },
        updateBank:function(){
            this.postData('../../banks/updateBank',this.newBank, function(){
                toastr.success("Successfully updated bank.");
                $("#add-modal").modal("hide");
                vue_banks.getBanks();
            });
        },
        showAddModal:function(){
            if(this.newBank.id != 0){
                this.newBank = {
                    id:0,
                    bank_name:'',
                    bank_shortname:''
                }
            }
            $("#add-modal").modal("show");
        },
        bankName:function(bank_id){
            for(var x=0;x<this.banks.length;x++){
                if(bank_id == this.banks[x].id){
                    return this.banks[x].bank_name;
                }
            }
            return 'N/A';
        },
        editEmployee:function(employee){
            this.editor={
                id:employee.user_id,
                bank_code:employee.bank_code,
                bank_number:employee.bank_number,
                name:employee.name
            }
            $("#editor-modal").modal("show");
        },
        updateEmployee:function(){
            this.postData('../../banks/updateEmployeeAccount',this.editor, function(){
                toastr.success("Successfully updated employee account.");
                $("#editor-modal").modal("hide");
                vue_banks.getEmployees();
            });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    computed:{
        filtered:Pagination
    },
    mounted:function(){
        this.getBanks();
        this.getEmployees();
    }
});