var vue_departments = new Vue({
    el:"#departments",
    data:{
        pagination:[Filter('department_name'),Filter('name')],
        table:'departments',
        table1:'display_employees',
        newDepartment:{
            id:0,
            department_name:'',
            department_desc:'',
            department_active:1
        },
        display:{
            department_name:'',
            id:0
        },
        departments:[],
        employees:[]
    },
    methods:{
        clearForm:function(){
            this.newDepartment={
                id:0,
                department_name:'',
                department_desc:'',
                department_active:1
            }
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_departments[field] = [];
                data.forEach(function(item, i){
                    vue_departments[field].push(item);
                });
            });
        },
        getDepartments:function(){
            this.getData('../../departments/getDepartments', 'departments');
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees', 'employees');
        },
        showAddModal:function(){
            if(this.newDepartment.id != 0){
                this.clearForm();
            }
            $("#add-modal").modal("show");
        },
        showViewModal:function(department){
            this.display.department_name = department.department_name;
            this.display.id = department.id;
            $("#view-modal").modal("show");
        },
        editDepartment:function(department){
            this.newDepartment = {
                id:department.id,
                department_name:department.department_name,
                department_desc:department.department_desc,
                department_active:department.department_active
            }
            $("#add-modal").modal("show");
        },
        saveDepartment:function(){
            var url,msg;
            if(this.newDepartment.id == 0){
                url = '../../departments/addDepartment';
                msg = "Successfully added department.";
            }
            else{
                url = '../../departments/updateDepartment';
                msg = "Successfully updated department.";
            }

            this.postData(url, this.newDepartment, function(){
                toastr.success(msg);
                $("#add-modal").modal("hide");
                vue_departments.clearForm();
                vue_departments.getDepartments();
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
        activateDepartment:function(department){
            this.postData('../../departments/activateDepartment',{id:department.id}, function(){
                toastr.success(department.department_name + " activated.");
                vue_departments.getDepartments();
            });
        },
        deactivateDepartment:function(department){
            this.postData('../../departments/deactivateDepartment',{id:department.id}, function(){
                toastr.success(department.department_name + " deactivated.");
                vue_departments.getDepartments();
            });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy

    },
    mounted:function(){
        this.getDepartments();
        this.getEmployees();
    },
    computed:{
        filtered:Pagination,
        filtered1:Pagination1,
        display_employees:function(){
            return this.employees.filter(function(item){
                if(item.department_id == vue_departments.display.id){
                    return true;
                }
            });
        }
    }
});