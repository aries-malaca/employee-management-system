
  // request permission on page load
  document.addEventListener('DOMContentLoaded', function () {
    if (Notification.permission !== "granted")
      Notification.requestPermission();
  });

  function notifyMe() {
    if (!Notification) {
      alert('Desktop notifications not available in your browser. Try Chromium.'); 
      return;
    }

    if (Notification.permission !== "granted")
      Notification.requestPermission();
    else {
      var notification = new Notification(document.getElementById("notification_title").value, {
        icon: document.getElementById("the_icon").value,
        body: document.getElementById("notification_message").value,
      });

      notification.onclick = function () {
        window.focus();  
        setTimeout(function(){
          
          if(document.getElementById("current_chat_id").value != document.getElementById("notification_sender_id").value){
            $(".dropdown-quick-sidebar-toggler a").click(); $("#quick_sidebar_tab_1").addClass("page-quick-sidebar-content-item-shown");
            toggleChat(document.getElementById("notification_sender_id").value);
          }

        },1000);  
        notification.close();  
      };
      
    }

  }
