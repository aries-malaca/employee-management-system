var vue_positions = new Vue({
    el:"#positions",
    data:{
        pagination:[Filter('position_name')],
        table:'positions',
        newPosition:{
            id:0,
            position_name:'',
            position_desc:'',
            position_active:1,
            is_department_head:0,
            department_id:0,
            position_data:{
                salary_frequency:'monthly',
                grace_period_minutes:15,
                grace_period_per_month:3,
                standard_days:11,
                branch_aware:false
            },
            leave_data:[],
            reporting_lines:[],
            original_reporting_lines:[],
            audience_data:[]
        },
        display:{
            position_name:'',
            id:0,
        },
        positions:[],
        departments:[],
        employees:[],
        leaves:[],
        generated_down_lines:[]
    },
    methods:{
        clearForm:function(){
            this.newPosition={
                id:0,
                position_name:'',
                position_desc:'',
                position_active:1,
                is_department_head:0,
                department_id:0,
                position_data:{
                    salary_frequency:'monthly',
                    grace_period_minutes:15,
                    grace_period_per_month:3,
                    standard_days:11,
                    branch_aware:false
                },
                leave_data:[],
                reporting_lines:[],
                original_reporting_lines:[],
                audience_data:[]
            }
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_positions[field] = [];
                data.forEach(function(item, i){
                    vue_positions[field].push(item);
                });
            });
        },
        getPositions:function(){
            this.getData('../../positions/getPositions', 'positions');
        },
        getDepartments:function(){
            this.getData('../../departments/getDepartments', 'departments');
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees', 'employees');
        },
        getLeaveTypes:function(){
            this.getData('../../leave_types/getLeaveTypes', 'leaves');
        },
        showAddModal:function(){
            if(this.newPosition.id != 0){
                this.clearForm();
            }
            $("#add-modal").modal("show");
        },
        showViewModal:function(department){
            this.display.department_name = department.department_name;
            this.display.id = department.id;
            $("#view-modal").modal("show");
        },
        toggleSelection:function(key,field){
            this.newPosition[field][key].ruling = this.getRulingEmployees();
        },
        getRulingEmployees:function(){
            var array = [];
            for(var x=0;x<this.employees.length;x++){
                if(this.employees[x].position_id == this.newPosition.id)
                    array.push({
                                    employee_id:this.employees[x].user_id,
                                    supervisor_id:0,
                                    name:this.employees[x].name
                            });
            }
            console.log(array);
            return array;
        },
        editPosition:function(position){
            var position_data = JSON.parse(position.position_data);
            this.newPosition = {
                id:position.id,
                position_name: position.position_name,
                position_desc:position.position_desc,
                position_active:position.position_active,
                is_department_head:position.is_department_head,
                department_id:position.department_id,
                position_data:{
                    salary_frequency: (position_data.salary_frequency !== undefined ? position_data.salary_frequency:'monthly'),
                    grace_period_minutes:(position_data.grace_period_minutes !== undefined ? position_data.grace_period_minutes:15),
                    grace_period_per_month:(position_data.grace_period_per_month !== undefined ? position_data.grace_period_per_month:3),
                    standard_days:(position_data.standard_days !== undefined ? position_data.standard_days:11),
                    branch_aware: (position_data.branch_aware=="true")
                },
                leave_data:[],
                reporting_lines:[],
                audience_data:[],
                original_reporting_lines:[]
            };
            if(position_data.reporting_lines !== undefined && position_data.reporting_lines !== null  ){
                for(var x=0; x<position_data.reporting_lines.length;x++){
                    this.newPosition.reporting_lines.push({
                        position_id: Number(position_data.reporting_lines[x].position_id),
                        selection: position_data.reporting_lines[x].selection !== undefined? position_data.reporting_lines[x].selection:'position',
                        ruling: position_data.reporting_lines[x].ruling !== undefined? position_data.reporting_lines[x].ruling:undefined
                    });
                    this.newPosition.original_reporting_lines.push({
                        position_id: Number(position_data.reporting_lines[x].position_id),
                        selection: position_data.reporting_lines[x].selection !== undefined? position_data.reporting_lines[x].selection:'position',
                        ruling: position_data.reporting_lines[x].ruling !== undefined? position_data.reporting_lines[x].ruling:undefined
                    });
                }
            }
            if(position_data.leave_data !== undefined && position_data.leave_data !== null  ){
                for(var x=0; x<position_data.leave_data.length;x++){
                    this.newPosition.leave_data.push({
                        leave_id: Number(position_data.leave_data[x].leave_id),
                        leave_count: Number(position_data.leave_data[x].leave_count)
                    });
                }
            }
            if(position_data.audience_data !== undefined && position_data.audience_data !== null  ){
                for(var x=0; x<position_data.audience_data.length;x++){
                    this.newPosition.audience_data.push({
                        position_id: Number(position_data.audience_data[x].position_id),
                        selection: position_data.audience_data[x].selection !== undefined? position_data.audience_data[x].selection:'position',
                        ruling: position_data.audience_data[x].ruling !== undefined? position_data.audience_data[x].ruling:undefined
                    });
                }
            }

            $("#add-modal").modal("show");
        },
        savePosition:function(){
            var url,msg;
            if(this.newPosition.id == 0){
                url = '../../positions/addPosition';
                msg = "Successfully added position.";
            }
            else{
                url = '../../positions/updatePosition';
                msg = "Successfully updated position.";
            }
            for(var x=0;x<this.newPosition.reporting_lines.length;x++){
                if(this.newPosition.reporting_lines[x].position_id == 0){
                    toastr.error("Please select positions for reporting lines.");
                    return false;
                }
            }
            for(var x=0;x<this.newPosition.leave_data.length;x++){
                if(this.newPosition.leave_data[x].position_id == 0){
                    toastr.error("Please select leave type and count for custom leaves.");
                    return false;
                }
            }
            for(var x=0;x<this.newPosition.audience_data.length;x++){
                if(this.newPosition.audience_data[x].position_id == 0){
                    toastr.error("Please select position for audiences.");
                    return false;
                }
            }
            if(this.isModified() && this.newPosition.id != 0)
                if(!confirm("You're about to update reporting lines, pending requests will be revoked.")){
                    return false;
                }

            this.postData(url, this.newPosition, function(){
                toastr.success(msg);
                $("#add-modal").modal("hide");
                vue_positions.clearForm();
                vue_positions.getPositions();
            });
        },
        isModified:function(){
            if(this.newPosition.original_reporting_lines.length != this.newPosition.reporting_lines.length){
                return true;
            }
            for(var x=0;x<this.newPosition.reporting_lines.length;x++){
                if(this.newPosition.reporting_lines[x].position_id != this.newPosition.original_reporting_lines[x].position_id){
                    return true;
                }
            }
            return false;
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
        activatePosition:function(position){
            this.postData('../../positions/activatePosition',{id:position.id}, function(){
                toastr.success(position.position_name + " activated.");
                vue_positions.getPositions();
            });
        },
        deactivatePosition:function(position){
            this.postData('../../positions/deactivatePosition',{id:position.id}, function(){
                toastr.success(position.position_name + " deactivated.");
                vue_positions.getPositions();
            });
        },
        addReportingLine:function(){
            this.newPosition.reporting_lines.push({
                position_id: 0,
                selection:'position'
            });
        },
        removeReportingLine:function(key){
            this.newPosition.reporting_lines.splice(key,1);
        },
        addCustomLeave:function(){
            this.newPosition.leave_data.push({
                leave_id: 0,
                leave_count: 0
            });
        },
        addAudience:function(){
            this.newPosition.audience_data.push({
                position_id: 0,
                selection:'position'
            });
        },
        removeAudience:function(key){
            this.newPosition.audience_data.splice(key,1);
        },
        removeCustomLeave:function(key){
            this.newPosition.leave_data.splice(key,1);
        },
        isAvailableReportingLine:function(position_id,key){
            for(var y=0; y<this.newPosition.reporting_lines.length;y++){
                if((this.newPosition.reporting_lines[y].position_id == position_id
                        && key != y)
                        || this.newPosition.id == position_id){
                    return false;
                }
            }
            return true;
        },
        isAvailableAudience:function(position_id,key){
            for(var y=0; y<this.newPosition.reporting_lines.length;y++){
                if((this.newPosition.reporting_lines[y].position_id == position_id)){
                    return false;
                }
            }
            for(var y=0; y<this.newPosition.audience_data.length;y++){
                if((this.newPosition.audience_data[y].position_id == position_id && key!=y )){
                    return false;
                }
            }
            return true;
        },
        isAvailableLeave:function(leave_id,key){
            for(var y=0; y<this.newPosition.leave_data.length;y++){
                if(this.newPosition.leave_data[y].leave_id == leave_id && key != y){
                    return false;
                }
            }
            return true;
        },
        showViewModal:function(position){
            this.display.id = position.id;
            this.display.position_name = position.position_name;
            $("#view-modal").modal("show");
        },
        getPosition:function(position_id, what){
            for(var x=0;x<this.positions.length;x++){
                if(this.positions[x].id == position_id){
                    if(what == ''){
                        return this.positions[x];
                    }
                    else if(what == 'data'){
                        return JSON.parse(this.positions[x].position_data);
                    }
                }
            }
        },
        getEmployeesByPosition:function(position_id){
            var all = [];
            for(var x=0;x<this.employees.length;x++){
                if(position_id == this.employees[x].position_id){
                    all.push({ employee_id:this.employees[x].user_id, name: this.employees[x].name });
                }
            }
            return all;
        },
        getDownLines:function(position_id){
            var positions = [];
            var all = [];
            for(var x=0;x<this.positions.length;x++){
                var data = this.getPosition(this.positions[x].id,'data');

                if(data.reporting_lines!= null && data.reporting_lines != undefined) {
                    for (var y = 0; y < data.reporting_lines.length; y++) {
                        if (Number(data.reporting_lines[y].position_id) == position_id) {
                            if (positions.indexOf(this.positions[x].id) == -1)
                                positions.push(this.positions[x].id);
                        }
                    }
                }
                if(data.audience_data!= null && data.audience_data != undefined){
                    for(var y=0;y<data.audience_data.length;y++){
                        if(Number(data.audience_data[y].position_id) == position_id){
                            if(positions.indexOf(this.positions[x].id) == -1)
                                positions.push(this.positions[x].id);
                        }
                    }
                }

            }
            for(var x=0;x<positions.length;x++){
                all.push({position_name: this.getPosition(positions[x],'').position_name,
                          position_id: positions[x],
                          employees: this.getEmployeesByPosition(positions[x])
                        });
            }

            return all;
        },
        showOrgChart:function(){
            $.get('../../positions/getOrgChart', function(result){
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Name');
                data.addColumn('string', 'Manager');
                data.addColumn('string', 'ToolTip');
                // For each orgchart box, provide the name, manager, and tooltip to show.
                data.addRows(result);
                // Create the chart.
                var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
                // Draw the chart, setting the allowHtml option to true for the tooltips.
                chart.draw(data, {allowHtml:true});
            });
            $("#orgchart-modal").modal("show");
        },
        uplines:function(id){
            if(id == 0)
                return [];

            var position_data = this.getPosition(id,'data');
            var all = [];

            if(position_data.reporting_lines!= null && position_data.reporting_lines != undefined){
                for(var x=0;x<position_data.reporting_lines.length;x++){
                    var position_id = position_data.reporting_lines[x].position_id;
                    var position_name = this.getPosition(position_id,'').position_name;
                    var employees = this.getEmployeesByPosition(position_id);

                    if(all.indexOf({position_name:position_name, position_id:position_id, employees:employees }) == -1){
                        all.push({position_name:position_name, position_id:position_id, employees:employees });
                    }
                }
            }

            return all;
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    mounted:function(){
        this.getPositions();
        this.getDepartments();
        this.getEmployees();
        this.getLeaveTypes();
        google.charts.load('current', {packages:["orgchart"]});
    },
    computed:{
        filtered:Pagination,
        currentDepartmentHead:function(){
            for(var x=0;x<this.positions.length;x++){
                if(this.positions[x].department_id == this.newPosition.department_id
                        && this.positions[x].is_department_head == 1
                        && this.positions[x].id != this.newPosition.id){
                    return this.positions[x].position_name;
                }
            }
            return false;
        },
        reportingLines:function(){
            if(this.display.id == 0)
                return [];

            var position_data = this.getPosition(this.display.id,'data');
            var all = [];

            if(position_data.reporting_lines!= null && position_data.reporting_lines != undefined){
                for(var x=0;x<position_data.reporting_lines.length;x++){
                    var position_id = position_data.reporting_lines[x].position_id;
                    var position_name = this.getPosition(position_id,'').position_name;
                    var employees = this.getEmployeesByPosition(position_id);

                    if(all.indexOf({position_name:position_name, position_id:position_id, employees:employees }) == -1){
                        all.push({position_name:position_name, position_id:position_id, employees:employees });
                    }
                }
            }

            return all;
        },
        downLines:function() {
            if(this.display.id == 0)
                return [];

            return this.getDownLines(this.display.id);

        }

    }
});