var vue_contributions = new Vue({
    el:"#contributions",
    data:{
        contributions:[],
        tax_exemptions:[],
        setTaxExemption:{
            id:0,
            tax_exemption_name:''
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
                vue_contributions[field] = [];
                data.forEach(function(item){
                    if(field=='tax_exemptions')
                        item.tax_exemption_data = JSON.parse(item.tax_exemption_data);
                    if(field=='contributions')
                        item.contribution_data = JSON.parse(item.contribution_data);
                    vue_contributions[field].push(item);
                });
            });
        },
        getTaxExemptions:function(){
            this.getData('../../contributions/getTaxExemptions','tax_exemptions');
        },
        getContributions:function(){
            this.getData('../../contributions/getContributions','contributions');
        },
        editTaxExemption:function(tax_exemption){
            this.setTaxExemption = {
                id:tax_exemption.id,
                tax_exemption_name:tax_exemption.tax_exemption_name
            };
            $("#tax-exemption-modal").modal("show");
        },
        updateTaxExemption:function(){
            this.postData('../../contributions/updateTaxExemption', this.setTaxExemption, function(){
                toastr.success("Successfully updated tax exemption.");
                $("#tax-exemption-modal").modal("hide");
                vue_contributions.getTaxExemptions();
            });
        },
        format_number:function(string){
            return string.toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2})
        }
    },
    mounted:function(){
        this.getTaxExemptions();
        this.getContributions();
    }
});