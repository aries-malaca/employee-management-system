var vue_news = new Vue({
    el:"#news",
    data:{
        newNews:{
            id:0,
            title:'',
            description:'',
            posted_by_id:0,
            is_active:1,
            priority:3
        },
        news:[],
        active_news:[],
    },
    methods:{
        formatDateTime:function(str,format){
            return moment(str).format(format);
        },
        getData:function(url, field){
            $.get(url, function(data){
                vue_news[field] = [];
                data.forEach(function(item, i){
                    vue_news[field].push(item);
                });
            });
        },
        getNews:function(){
            this.getData('../../news/getNews','news');
        },
        getActiveNews:function(){
            this.getData('../../news/getActiveNews','active_news');
        },
        showAddModal:function() {
            if(this.newNews.id!=0){
                this.newNews={
                    id:0,
                    title:'',
                    description:'',
                    posted_by_id:0,
                    is_active:1,
                    priority:3
                };
            }
            $("#add-modal").modal("show");
        },
        saveNews:function(){
            this.postData('../../news/addNews', this.newNews, function(){
                toastr.success("Successfully added news.");
                $("#add-modal").modal("hide");
                vue_news.getNews();
            });
        },
        updateNews:function(){
            this.postData('../../news/updateNews', this.newNews, function(){
                toastr.success("Successfully updated news.");
                $("#add-modal").modal("hide");
                vue_news.getNews();
            });
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
        editNews:function(news){
            this.newNews={
                id:news.id,
                title:news.title,
                description:news.description,
                posted_by_id:news.posted_by_id,
                is_active:news.is_active,
                priority:news.priority
            };
            $("#add-modal").modal("show");
        },
        deleteNews:function(news){
            if(!confirm("Are you sure you want to delete this news?")){
                return false;
            }
            this.postData('../../news/deleteNews', news, function(){
                toastr.success("Successfully deleted news.");
                vue_news.getNews();
            });
        }
    },
    mounted:function(){
        this.getNews();
        this.getActiveNews();
    }
});