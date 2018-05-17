var vue_transactions = new Vue({
    el: "#transactions",
    data: {
        pagination:[Filter('name'), Filter('name')],
        table:'transaction_codes',
        table1:'filtered_transactions',
        transaction_codes: [],
        employees:[],
        transactions:[],
        newCode:{
            id:0,
            transaction_name:'',
            is_taxable:0,
            is_regular_transaction:0,
            cutoff:'first cutoff',
            transaction_type:'addition'
        },
        newTransaction:{
            id:0,
            start_date:moment().format("YYYY-MM-")+"01",
            end_date:moment().format("YYYY-MM-") + moment().daysInMonth(),
            amount:0,
            employee_id:0,
            transaction_code_id:0,
            cutoff:'first cutoff',
            frequency:'once',
            notes:''
        },
        filter_by:'active-recurring',
        filter_type:'addition'
    },
    methods: {
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
                vue_transactions[field] = [];
                data.forEach(function(item, i){
                    vue_transactions[field].push(item);
                });
            });
        },
        getTransactions:function(){
            this.getData('../../transactions/getTransactions','transactions');
        },
        getEmployees:function(){
            this.getData('../../employees/getEmployees','employees');
        },
        getTransactionCodes:function(){
            this.getData('../../transactions/getTransactionCodes','transaction_codes');
        },
        showAddModal:function(){
            if(this.newCode.id != 0){
                this.clearCodeForm();
            }
            $("#add-code-modal").modal("show");
        },
        showAddTransactionModal:function(){
            if(this.newTransaction.id != 0){
                this.clearTransactionForm();
            }
            $("#add-transaction-modal").modal("show");
        },
        clearCodeForm:function(){
            this.newCode = {
                id:0,
                transaction_name:'',
                is_taxable:0,
                is_regular_transaction:0,
                cutoff:'first cutoff',
                transaction_type:'addition'
            }
        },
        clearTransactionForm:function(){
            this.newTransaction={
                id:0,
                start_date:moment().format("YYYY-MM-")+"01",
                end_date:moment().format("YYYY-MM-") + moment().daysInMonth(),
                amount:0,
                employee_id:0,
                transaction_code_id:0,
                cutoff:'first cutoff',
                frequency:'once',
                notes:''
            }
        },
        addTransactionCode:function(){
            this.postData('../../transactions/addTransactionCode', this.newCode, function(){
                toastr.success('Successfully added transaction code.');
                $("#add-code-modal").modal("hide");
                vue_transactions.clearCodeForm();
                vue_transactions.getTransactionCodes();
            });
        },
        updateTransactionCode:function(){
            this.postData('../../transactions/updateTransactionCode', this.newCode, function(){
                toastr.success('Successfully updated transaction code.');
                $("#add-code-modal").modal("hide");
                vue_transactions.clearCodeForm();
                vue_transactions.getTransactionCodes();
            });
        },
        editTransactionCode:function(code){
            this.newCode = {
                id:code.id,
                transaction_name:code.transaction_name,
                is_taxable:code.is_taxable,
                is_regular_transaction:code.is_regular_transaction,
                cutoff:code.cutoff,
                transaction_type:code.transaction_type
            };
            $("#add-code-modal").modal("show");
        },
        deleteTransactionCode:function(code){
            if(!confirm("Are you sure you want to delete transaction code?")){
                return false;
            }
            this.postData('../../transactions/deleteTransactionCode', {id:code.id}, function(){
                toastr.success('Successfully deleted transaction code.');
                vue_transactions.clearCodeForm();
                vue_transactions.getTransactionCodes();
            });
        },
        getTransactionTimes:function(transaction){
            var start = new Date(transaction.start_date).getTime()/1000;
            var end = new Date(transaction.end_date).getTime()/1000;
            var times = 0;

            if(transaction.frequency == 'once'){
                return 1;
            }

            while(start<end){
                if(transaction.cutoff == 'every cutoff'){
                    if(new Date(start * 1000).getDate() == 1 || new Date(start * 1000).getDate() == 16){
                        times++;
                    }
                }

                if(transaction.cutoff == 'first cutoff'){
                    if(new Date(start * 1000).getDate() == 1){
                        times++;
                    }
                }

                if(transaction.cutoff == 'second cutoff'){
                    if(new Date(start * 1000).getDate() == 16){
                        times++;
                    }
                }

                start += 86400;
            }


            return times;
        },
        getTransaction:function(transaction_id){
            for(var x=0;x<this.transaction_codes.length;x++){
                if(transaction_id == this.transaction_codes[x].id){
                    return this.transaction_codes[x];
                }
            }
            return {transaction_type:''}
        },
        format_number:function(string){
            return string.toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})
        },
        formatDateTime:function(string, format){
            return moment(string).format(format);
        },
        isValidRange:function(data){
            if(Number(moment(data.start_date).format('X')) > Number(moment(data.end_date).format('X')) ){
                return false;
            }
            if(data.frequency == 'once'){
                if(data.cutoff == 'every cutoff')
                    return false;

                if(data.cutoff == 'first cutoff'){
                    if(Number(moment(data.start_date).format('D')) != 1 || Number(moment(data.end_date).format('D')) != 15
                        || Number(moment(data.start_date).format('M')) != Number(moment(data.end_date).format('M'))
                        || Number(moment(data.start_date).format('YYYY')) != Number(moment(data.end_date).format('YYYY'))){
                        return false;
                    }
                }

                if(data.cutoff == 'second cutoff'){
                    if(Number(moment(data.start_date).format('D')) != 16 || Number(moment(data.end_date).format('D')) != moment(data.end_date).daysInMonth()
                        || Number(moment(data.start_date).format('M')) != Number(moment(data.end_date).format('M'))
                        || Number(moment(data.start_date).format('YYYY')) != Number(moment(data.end_date).format('YYYY'))){
                        return false;
                    }
                }
            }
            else{
                
            }
            return true;
        },
        addTransaction:function(){
            if(!this.isValidRange(this.newTransaction)){
                toastr.error("Invalid cutoff/range for the selected frequency. If once, please select specific cutoff and date range for the cutoff. If recurring, please start with first day then end with end of month day or 15.");
                return false;
            }
            this.postData('../../transactions/addTransaction',this.newTransaction, function(){
                toastr.success("Successfully added transaction.");
                $("#add-transaction-modal").modal("hide");
                vue_transactions.clearTransactionForm();
                vue_transactions.getTransactions();
            });
        },
        updateTransaction:function(){
            if(!this.isValidRange(this.newTransaction)){
                toastr.error("Invalid cutoff/range for the selected frequency. If once, please select specific cutoff and date range for the cutoff. If recurring, please start with first day then end with end of month day or 15.");
                return false;
            }
            this.postData('../../transactions/updateTransaction',this.newTransaction, function(){
                toastr.success("Successfully updated transaction.");
                $("#add-transaction-modal").modal("hide");
                vue_transactions.clearTransactionForm();
                vue_transactions.getTransactions();
            });
        },
        editTransaction:function(transaction){
            this.newTransaction = {
                id:transaction.transaction_id,
                start_date:moment(transaction.start_date).format("YYYY-MM-DD"),
                end_date:moment(transaction.end_date).format("YYYY-MM-DD"),
                amount:transaction.amount,
                employee_id:transaction.user_id,
                transaction_code_id:transaction.transaction_code_id,
                cutoff:transaction.cutoff,
                frequency:transaction.frequency,
                notes:transaction.notes
            }
            $("#add-transaction-modal").modal("show");
        },
        cloneTransaction:function(transaction){
            this.newTransaction = {
                id:0,
                start_date:moment(transaction.start_date).format("YYYY-MM-DD"),
                end_date:moment(transaction.end_date).format("YYYY-MM-DD"),
                amount:transaction.amount,
                employee_id:0,
                transaction_code_id:transaction.transaction_code_id,
                cutoff:transaction.cutoff,
                frequency:transaction.frequency,
                notes:transaction.notes
            }
            $("#add-transaction-modal").modal("show");
        },
        deleteTransaction:function(id){
            if(!confirm("Are you sure you want to delete this transaction?")){
                return false;
            }
            this.postData('../../transactions/deleteTransaction',{id:id}, function(){
                toastr.success("Successfully deleted transaction.");
                vue_transactions.getTransactions();
            });
        },
        setPage: SetPage,
        setOrderBy:SetOrderBy
    },
    mounted:function(){
        this.getEmployees();
        this.getTransactions();
        this.getTransactionCodes();
    },
    computed:{
        filtered_transactions:function(){
            return this.transactions.filter(function(item){
                if(vue_transactions.filter_by == 'active-recurring' && item.frequency == 'recurring'
                        && moment(item.end_date).format('X')>moment().format('X')){
                    return item.transaction_type==vue_transactions.filter_type;
                }

                if(vue_transactions.filter_by == 'inactive-recurring' && item.frequency == 'recurring'
                    && moment(item.end_date).format('X')<moment().format('X')){
                    return item.transaction_type==vue_transactions.filter_type;
                }

                if(vue_transactions.filter_by == 'active-nonrecurring' && item.frequency == 'once'
                    && moment(item.end_date).format('X')>moment().format('X')){
                    return item.transaction_type==vue_transactions.filter_type;
                }

                if(vue_transactions.filter_by == 'inactive-nonrecurring' && item.frequency == 'once'
                    && moment(item.end_date).format('X')<moment().format('X')){
                    return item.transaction_type==vue_transactions.filter_type;
                }

                return false;
            });
        },
        filtered:Pagination,
        filtered1:Pagination1
    }
});