var vue_trainees = new Vue({
    el:"#trainees",
    data:{
        trainees:[],
        pagination:[Filter('first_name')],
        table:'trainees',
        newTrainee:{}
    },
    methods:{
        getTrainees:function(){
            $.get('../../trainees/getTrainees', function(data){
                vue_trainees.trainees = [];
                data.forEach(function(item, i){
                    vue_trainees.trainees.push(item);
                });
            });
        },
        addTrainee:function(){
            $.ajax({
                url: '../../trainees/addTrainee',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: this.newTrainee,
                success: function (data) {
                    if (data.result == 'success'){
                        $("#add-trainee-modal").modal("hide");
                        vue_trainees.getTrainees();
                        toastr.success("Successfully Added Trainee");
                    }
                    else
                        toastr.error(data.errors);
                }
            });
        },
        updateTrainee:function(){
            $.ajax({
                url: '../../trainees/updateTrainee',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: this.newTrainee,
                success: function (data) {
                    if (data.result == 'success'){
                        $("#add-trainee-modal").modal("hide");
                        vue_trainees.getTrainees();
                        toastr.success("Successfully Updated Trainee");
                    }
                    else
                        toastr.error(data.errors);
                }
            });
        },
        showAddModal:function(){
            this.newTrainee = {
                id:0,
                first_name:'',
                biometric_no:0,
                last_name:'',
                middle_name:'',
                wave:0,
                classification:'company-owned',
                status:'active',
                assigned_id:0,
            };
            $("#add-trainee-modal").modal("show");
        },
        viewTrainee:function(trainee){
            this.newTrainee = {
                id:trainee.id,
                biometric_no:trainee.biometric_no,
                first_name:trainee.first_name,
                last_name:trainee.last_name,
                middle_name:trainee.middle_name,
                wave:trainee.wave,
                classification:trainee.classification,
                status:trainee.status,
                assigned_id:trainee.assigned_id,
            };
            $("#add-trainee-modal").modal("show");
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
        format_number:function(string){
            return string.toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})
        }
    },
    mounted:function(){
       this.getTrainees();
    },
    computed:{
        filtered:Pagination,
    }
});