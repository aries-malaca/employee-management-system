var vue_tasks = new Vue({
    el:"#tasks",
    data:{
        newTask:{
            id:0,
            task_title:'',
            task_description:'',
            task_status:'open',
            task_started_date:moment().format("YYYY-MM-DD"),
            task_created_date:moment().format("YYYY-MM-DD"),
            task_target_completion_date:moment().format("YYYY-MM-DD"),
            task_completed_date:moment().format("YYYY-MM-DD"),
            task_approval:{
                is_confirmed:false
            },
            task_progress:0,
            task_priority:3
        },
        tasks:[]
    },
    methods:{
        showAddModal:function(){
            $("#add-task-modal").modal("show");
        },
        changeStatus:function(){
            if(this.newTask.task_status === "completed"){
                this.newTask.task_progress = 100;
            }
            else{
                this.newTask.task_progress = 0;
            }
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
        getData:function(url, field){
            $.get(url, function(data){
                vue_tasks[field] = [];
                data.forEach(function(item){
                    vue_tasks[field].push(item);
                });
            });
        },
        addTask:function(){
            this.postData('../../tasks/addTask',this.newTask, function(){
                toastr.success("Successfully added task.");
                $("#add-task-modal").modal("hide");
                vue_tasks.getTasks();
            });
        },
        getTasks:function(){
            this.getData('../../tasks/getMyTasks','tasks');
        }
    },
    mounted:function(){
        this.getTasks();
    }
});