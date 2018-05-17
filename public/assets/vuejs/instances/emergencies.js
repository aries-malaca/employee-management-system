var vue_emergencies = new Vue({
    el:"#emergencies",
    data:{
        pagination:[Filter('emergency_name')],
        table:'emergencies',
        emergencies:[],
        newEmergency:{
            id:0,
            emergency_name:'',
            date_start:moment().format("YYYY-MM-DD"),
            date_end:moment().format("YYYY-MM-DD"),
            notes:'',
            branch_covered:[],
            exempted_employees:[],
        },
        branches:[],
        employees:[]
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
                vue_emergencies[field] = [];
                data.forEach(function(item){
                    if(field=='emergencies'){
                        item.branch_covered = JSON.parse(item.branch_covered);
                        item.exempted_employees = JSON.parse(item.exempted_employees);
                    }
                    vue_emergencies[field].push(item);
                });
            });
        },
        editEmergency:function(emergency) {
            this.newEmergency = {
                id: emergency.id,
                date_start: emergency.date_start,
                date_end: emergency.date_end,
                emergency_name: emergency.emergency_name,
                notes: emergency.notes,
                branch_covered: [],
                exempted_employees:[],
            };

            emergency.branch_covered.forEach(function(item){
                vue_emergencies.newEmergency.branch_covered.push(Number(item));
            });
            emergency.exempted_employees.forEach(function(item){
                vue_emergencies.newEmergency.exempted_employees.push(Number(item));
            });
            $("#add-modal").modal("show");
        },
        addEmergency:function(){
            this.postData('../../emergencies/addEmergency', this.newEmergency, function(){
                toastr.success("Successfully added emergency.");
                $("#add-modal").modal("hide");
                vue_emergencies.getData('../../emergencies/getEmergencies','emergencies');
            });
        },
        deleteEmergency:function(emergency){
            if(!confirm("Are you sure you want to delete this emergency?")){
                return false;
            }
            this.postData('../../emergencies/deleteEmergency', emergency, function(){
                toastr.success("Successfully deleted emergency.");
                vue_emergencies.getData('../../emergencies/getEmergencies','emergencies');
            });
        },
        updateEmergency:function(){
            this.postData('../../emergencies/updateEmergency', this.newEmergency, function(){
                toastr.success("Successfully updated emergency.");
                $("#add-modal").modal("hide");
                vue_emergencies.getData('../../emergencies/getEmergencies','emergencies');
            });
        },
        showAddModal:function(){
            this.newEmergency={
                id:0,
                emergency_name:'',
                date_start:moment().format("YYYY-MM-DD"),
                date_end:moment().format("YYYY-MM-DD"),
                notes:'',
                branch_covered:[],
                exempted_employees:[],
            };

            $("#add-modal").modal("show");
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy,
        formatDateTime:function(str,format){
            return moment(str).format(format);
        }
    },
    computed:{
        filtered:Pagination
    },
    mounted:function(){
        this.getData('../../emergencies/getEmergencies','emergencies');
        this.getData('../../branches/getBranches','branches');
        this.getData('../../employees/getEmployees','employees');
    }
});