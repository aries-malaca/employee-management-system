var vue_leave_types = new Vue({
    el:"#leave_types",
    data:{
        newLeave:{
            id:0,
            leave_type_name:'',
            leave_type_description:'',
            leave_type_max:10,
            paid:true,
            gender:'both',
            allow_half_day:true,
            is_staggered:true,
            tax_exemptions:[],
            limit_per_lifetime:0,
            within_condition:'',
            extra_message:'',
        },
        leave_types:[],
        tax_exemptions:[],
        within:[{value:'',label:'--None--'},{value:'birth_month',label:'Within Birth Month'}],
        selected:[],
        employees:[],
        custom_leaves:[],
        newCustom:{}
    },
    methods:{
        clearForm:function(){
            this.newLeave={
                id:0,
                leave_type_name:'',
                leave_type_description:'',
                leave_type_max:10,
                paid:true,
                gender:'both',
                allow_half_day:true,
                is_staggered:true,
                tax_exemptions:[],
                limit_per_lifetime:0,
                within_condition:'',
                extra_message:''
            };
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_leave_types[field] = [];
                data.forEach(function(item, i){
                    vue_leave_types[field].push(item);
                    if(field == 'leave_types'){
                        vue_leave_types.leave_types[i].leave_type_data = JSON.parse(item.leave_type_data);
                    }
                    if(field == 'employees'){
                        vue_leave_types.employees[i].status = 'viewing';
                        vue_leave_types.employees[i].leave_max = 0;
                    }
                });
            });
        },
        getLeaveTypes:function(){
            this.getData('../../leave_types/getLeaveTypes', 'leave_types');
        },
        getTaxExemptions:function(){
            this.getData('../../contributions/getTaxExemptions', 'tax_exemptions');
        },
        showAddModal:function(){
            this.selected=[];
            vue_leave_types.tax_exemptions.forEach(function(item,i){
                vue_leave_types.selected.push({
                    id:item.id,
                    checked:false,
                    name:item.tax_exemption_name
                });
            });

            if(this.newLeave.id != 0){
                this.clearForm();
            }
            $("#add-modal").modal("show");
        },
        editLeaveType:function(leave_type){
            this.newLeave={
                id:leave_type.id,
                leave_type_name:leave_type.leave_type_name,
                leave_type_description:leave_type.leave_type_description,
                leave_type_max:leave_type.leave_type_max,
                paid:(leave_type.leave_type_data.paid == 'true'),
                gender:leave_type.leave_type_data.gender,
                allow_half_day:(leave_type.leave_type_data.allow_half_day == 'true'),
                is_staggered:(leave_type.leave_type_data.is_staggered == 'true'),
                tax_exemptions:[],
                limit_per_lifetime:leave_type.leave_type_data.limit_per_lifetime,
                within_condition:leave_type.leave_type_data.within_condition,
                extra_message:leave_type.leave_type_data.extra_message
            };
            $("#add-modal").modal("show");
            this.selected=[];
            vue_leave_types.tax_exemptions.forEach(function(item,i){
                var t = false;
                if(leave_type.leave_type_data.tax_exemptions != null || leave_type.leave_type_data.tax_exemptions != undefined){
                    t = (leave_type.leave_type_data.tax_exemptions.indexOf(item.id+"") != -1) ;
                }
                vue_leave_types.selected.push({
                    id:item.id,
                    checked:t,
                    name:item.tax_exemption_name
                });
            });

            this.getData('../../leave_types/getCustomLeaves/'+leave_type.id, 'custom_leaves');

        },
        editCustomLeave:function(employee){
            this.newCustom = {
                leave_type_id:this.newLeave.id,
                employee_id:employee.user_id,
                max_leave:this.getCustomMax(employee.user_id, this.newLeave.id),
                year:moment().format("YYYY"),
            }
        },
        getCustomMax:function(employee_id, leave_type_id){
            for(var x=0;x<this.custom_leaves.length;x++){
                if(this.custom_leaves[x].leave_type_id===leave_type_id && this.custom_leaves[x].employee_id===employee_id){
                    return this.custom_leaves[x].max_leave;
                }
            }
            return 0;
        },
        cancelCustomLeave:function(){
            this.newCustom = {};
        },
        saveCustomLeave:function(){
            this.postData('../../leave/updateLeaveCredit', this.newCustom ,function(){
                toastr.success("Successfully updated custom leave.");

                vue_leave_types.getLeaveTypes();
                vue_leave_types.getData('../../leave_types/getCustomLeaves/'+ vue_leave_types.newCustom.leave_type_id, 'custom_leaves');
                vue_leave_types.cancelCustomLeave();
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
        saveLeaveType:function(){
            if(this.newLeave.id == 0){
                this.postData('../../leave_types/addLeaveType', this.newLeave,function(){
                    toastr.success("Successfully added leave type.");
                    $("#add-modal").modal("hide");
                    vue_leave_types.clearForm();
                    vue_leave_types.getLeaveTypes();
                });
            }
            else{
                this.postData('../../leave_types/updateLeaveType', this.newLeave,function(){
                    toastr.success("Successfully updated leave type.");
                    $("#add-modal").modal("hide");
                    vue_leave_types.clearForm();
                    vue_leave_types.getLeaveTypes();
                });
            }
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees', 'employees');
        },
    },
    computed:{
        resolveSelect:function(){
            var selected_items = [];
            this.selected.forEach(function(item){
                if(item.checked){
                    selected_items.push(item.id);
                }
            });

            this.newLeave.tax_exemptions = selected_items;
        }
    },
    mounted:function(){
        this.getLeaveTypes();
        this.getTaxExemptions();
        this.getEmployees();
    }
});