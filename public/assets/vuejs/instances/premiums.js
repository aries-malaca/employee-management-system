var vue_premiums = new Vue({
    el:"#premiums",
    data:{
        setPremium:{
            regular_overtime:0,
            restday_overtime:0,
            restday_beyond_overtime:0,
            regular_nightdiff:0,
            restday_nightdiff:0
        }
    },
    methods:{
        updatePremiumSettings:function(){
            $.ajax({url: '../premiums/updatePremiumSettings',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: vue_premiums.setPremium,
                success: function (data) {
                    if (data.result == 'success') {
                        toastr.success("Successfully updated profile.");
                    }
                    else {
                        toastr.error(data.errors);
                    }
                }
            });
        },
        getPremiumSettings:function(){
            $.get('../../premiums/getPremiumSettings', function(data){
                vue_premiums.setPremium = data;
            });
        }
    },
    mounted:function(){
        this.getPremiumSettings();
    }
});