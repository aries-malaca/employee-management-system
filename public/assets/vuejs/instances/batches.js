var vue_batches = new Vue({
    el:"#batches",
    data:{
        batches:[],
        employees:[],
        pagination:[Filter('name')],
        table:'display_employees',
        newBatch:{
            id:0,
            batch_name:''
        },
        display:{
            id:0,
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
                vue_batches[field] = [];
                data.forEach(function(item, i){
                    vue_batches[field].push(item);
                });
            });
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees','employees');
        },
        getBatches:function(){
            this.getData('../../batches/getBatches','batches');
        },
        editBatch:function(batch){
            this.newBatch = {
                id:batch.id,
                batch_name:batch.batch_name
            },
            $("#add-modal").modal("show");
        },
        deleteBatch:function(batch){
            if(!confirm("Are you sure you want to delete this batch?"))
                return false;

            this.postData('../../batches/deleteBatch',{id:batch.id}, function(){
                toastr.success(batches.batch_name + " has been deleted.");
                vue_batches.getBatches();
            });
        },
        addBatch:function(){
            this.postData('../../batches/addBatch',this.newBatch, function(){
                toastr.success("Successfully added batch.");
                $("#add-modal").modal("hide");
                vue_batches.getBatches();
            });
        },
        updateBatch:function(){
            this.postData('../../batches/updateBatch',this.newBatch, function(){
                toastr.success("Successfully updated batch.");
                $("#add-modal").modal("hide");
                vue_batches.getBatches();
            });
        },
        showAddModal:function(){
            if(this.newBatch.id != 0){
                this.newBatch = {
                    id:0,
                    batch_name:''
                }
            }
            $("#add-modal").modal("show");
        },
        showViewModal:function(batch){
            this.display.id = batch.id;
            this.display.name = batch.batch_name;
            $("#view-modal").modal("show");
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    computed:{
        filtered:Pagination,
        display_employees:function(){
            return this.employees.filter(function(item){
                return (vue_batches.display.id == item.batch_id);
            });
        }
    },
    mounted:function(){
        this.getBatches();
        this.getEmployees();
    }
});