var vue_payslips = new Vue({
    el:"#payslips",
    data:{
        pagination:[Filter('created_at'),Filter('created_at')],
        table:'payslips',
        table1:'transactions',
        payslips:[],
        transactions:[],
        requests:[]
    },
    methods:{
        getData:function(url, field){
            $.get(url, function(data){
                vue_payslips[field] = [];
                data.forEach(function(item, i){
                    vue_payslips[field].push(item);
                });
            });
        },
        getPayslips: function () {
            this.getData("../../payroll/getPayslips/"+$("#my_id").val(),"payslips");
        },

        setPage: SetPage,
        setOrderBy:SetOrderBy,
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
    },
    computed:{
        filtered:Pagination,
        filtered1:Pagination1
    },
    mounted:function(){
        this.getPayslips();
    }
});