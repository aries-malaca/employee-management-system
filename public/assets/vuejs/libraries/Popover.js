var fillPopover = function(){
    $("a").each(function() {
        if (this.href.indexOf('/employee/') != -1) {
            var ind = this.href.substr(this.href.lastIndexOf("/")+1);
            var data = vue_home.employeeData(Number(ind));
            $(this).webuiPopover({
                placement:'auto',//values:
                container: null,
                width:'auto',
                height:'auto',
                trigger:'manual',
                style:'',
                selector: false,
                delay: {
                    show: null,
                    hide: 300
                },
                cache:false,
                multi:false,
                arrow:true,
                title:'',
                content:'<div style="width:20%"><img src="../../images/employees/'+ data.picture +'" style="width:50px"/></div><div style="width:70%">Name: '+ data.name+'</div>',
                closeable:false,
                padding:true,
                url:'',
                type:'html',
                direction: '',
                animation: null,
                template: '<div class="webui-popover">' +
                '<div class="arrow"></div>' +
                '<div class="webui-popover-inner">' +
                '<a href="#" class="close">x</a>' +
                '<h3 class="webui-popover-title"></h3>' +
                '<div class="webui-popover-content"></div>' +
                '</div>' +
                '</div>',
                backdrop: false,
                dismissible: true,
                onShow: null,
                onHide: null,
                abortXHR: true,
                autoHide: false,
                offsetTop: 0,
                offsetLeft: 0,
                iframeOptions: {
                    frameborder: '0',
                    allowtransparency: 'true',
                    id: '',
                    name: '',
                    scrolling: '',
                    onload: '',
                    height: '',
                    width: ''
                },
                hideEmpty: false
            });
        }
    });

}