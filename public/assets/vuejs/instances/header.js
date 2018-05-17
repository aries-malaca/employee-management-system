var vue_header = new Vue({
    el:"#header",
    data:{
        my_logs:[],
        employee_logs:[],
        notifications:[],
    },
    methods:{
        getData:function(url, field){
            $.get(url, function(data){
                vue_header[field] = [];
                data.forEach(function(item, i){
                    if(field=='notifications'){
                        item.notification_data = JSON.parse(item.notification_data);
                    }
                    vue_header[field].push(item);
                });
            });
        },
        getMyNotifications:function(){
            this.getData('../../notifications/getNotifications', 'notifications');
        },
        getMyLogs:function(){
            this.getData('../../logs/api/getMyLogs', 'my_logs');
        },
        getEmployeeLogs:function(){
            this.getData('../../logs/api/getEmployeeLogs', 'employee_logs');
        },
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        seenNotification:function(notification){
            if(notification.is_read == 1){
                return false;
            }
            $.ajax({
                url: '../../notifications/seenNotification',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('#token').val()},
                data: notification,
                success: function (data) {
                    if (data.result == 'success'){
                        vue_header.getMyNotifications();
                    }
                }
            });
        },
    },
    computed:{
        countPending:function(){
            var count = 0;

            for(var x =0;x<this.notifications.length;x++){
                if(this.notifications[x].is_read == 0){
                    count++;
                }
            }

            return count;
        }
    },
    mounted:function(){
        this.getMyLogs();
        this.getEmployeeLogs();
        this.getMyNotifications();
    }
});