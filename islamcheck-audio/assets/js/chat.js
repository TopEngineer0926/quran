$(document).ready(function () {
    /////////////////chat scripts start here//////////////// --> Remove all logs when in deployment
    var loader = `<div class="loader"></div>`;
    $("#complaint-view").hide();
    var pusher = new Pusher('ecfcf91d961449012c63', {//api key and cluster to be changed accordingly here
        cluster: 'ap2',
        encrypted: true //change to true when in deployment <----
    });

    // Enable pusher logging - don't include this in production <----
    Pusher.logToConsole = true;
    // Subscribe to the channel we specified in our Laravel Event
    var channel = pusher.subscribe(my_id);

    console.log("id = " + my_id);


    getActiveChats(); //get active chats when page loads




    // Bind a function to a Event (the full Laravel class)
    channel.bind('App\\Events\\MessageSent', function (data) { //callback function when a new message arrives
        //following values can be retreived by the data variable in the callback
        var name = data.username;
        var user_id = data.user_id;
        var message = data.message;
        var complaint_id = data.compliant_id;
        var formatedTime = getDateTime();
        console.log(name + "--" + user_id + "--" + message + "--" + complaint_id);

        console.log("Incoming Message");
        if (user_id == $(".display-user-id").children(".display").html() && complaint_id == $(".display-complaint-id").children(".display").html()) {//if the user chat is already active add it to chat window
            $(".message-content").append(
                `<div class="chat-container">
                        <img src="https://centrik.in/wp-content/uploads/2017/02/user-image-.png" alt="Avatar">
                        <p>`+ message + `</p>
                        <span class="time-right">`+ formatedTime + `</span>
                        </div>`
            );
            scrollToBottom();
        }

        if (!$("#" + user_id + `-` + complaint_id)[0]) { //check if user with the specfic complaint is not already in message list
            $.post("activechats/add", { admin_id: my_id, user_id: user_id, user_name: name, complaint_id: complaint_id }).done(function () { console.log("Added to Active Chat") });
            $(".messages-list").prepend(
                `<li class="messages-item" id="` + user_id + `-` + complaint_id + `" style = "background-color:#B1D8F3">
                <img alt="" src="https://centrik.in/wp-content/uploads/2017/02/user-image-.png" class="messages-item-avatar">
                <span class="messages-item-from">`+ name + `</span>
                <div class="messages-item-time">
                    <span class="text">`+ formatedTime + `</span>
                    <div class="messages-item-actions">
                    <a data-toggle="dropdown" title="Delete" href="javascript:void(0)"><i class="fa fa-trash-o"></i></a>
                    </div>
                </div>
                <span class="messages-item-subject"><hr></span>
                <span class="messages-item-preview">`+ message.substr(0, 30) + `.....</span>
                <input class = "complaint_id" value=`+ complaint_id + ` style="display:none">
                <input class = "user_id" value=`+ user_id + ` style="display:none">

            </li>`
            );
        }
        else {//user already exists in list
            $("#" + user_id + `-` + complaint_id).children(".messages-item-time").children("span").html(formatedTime);
            $("#" + user_id + `-` + complaint_id).children(".messages-item-preview").html(message.substr(0, 30) + ".....");
            var newmessage = $("#" + user_id + `-` + complaint_id);
            if (!newmessage.hasClass("active")) {//check if user chat is in focus
                newmessage.attr("style", "background-color:#B1D8F3"); //to be replaced by a class name eg:unread(use addClass)
            }
            $(".messages-list").prepend(newmessage);
        }
        notifyMe("New Message From "+name,message);
    });




    function scrollToBottom() {//function to scroll a div to bottom
        $(".message-content").animate({ scrollTop: $('.message-content').prop("scrollHeight") }, 1000);
    }

    function getDateTime() { // get date time in correct format (DD-MM-YYYY)
        var d = new Date(Date.now());
        var formatedTime = d.getFullYear() + "-" + ('0' + (d.getMonth() + 1)).slice(-2) + "-" + ('0' + d.getDate()).slice(-2) + " " + ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2) + ":" + ('0' + d.getSeconds()).slice(-2);
        return formatedTime;
    }
    function getActiveChats() {//this will get active chats for user
        $(".messages-list").html(loader);
        $.get("activechats/get/" + my_id, function (data) {
            $(".messages-list").html('');
            console.log(data);
            $.each(data, function (key, value) {
                $(".messages-list").prepend(
                    `<li class="messages-item" id="` + value.list.user_id + `-` + value.list.complaint_id + `">
            <img alt="" src="https://centrik.in/wp-content/uploads/2017/02/user-image-.png" class="messages-item-avatar">
            <span class="messages-item-from">`+ value.list.user_name + `</span>
            <div class="messages-item-time">
                <span class="text">`+ value.chat.created_at + `</span>
                <div class="messages-item-actions">
                <a data-toggle="dropdown" title="Delete" href="javascript:void(0)"><i class="fa fa-trash-o"></i></a>
                </div>
            </div>
            <span class="messages-item-subject"><hr></span>
            <span class="messages-item-preview">`+ value.chat.message.substr(0, 30) + `.....</span>
            <input class = "complaint_id" value=`+ value.list.complaint_id + ` style="display:none">
            <input class = "user_id" value=`+ value.list.user_id + ` style="display:none">
        </li>`
                );
            });
        }).done(function () {
            $(".messages-list").children(".messages-item").first().click();
        });

    }


    /////////////////////////////////////////////////////////////////////////////

    // $("#send").click(function () {
    //     //simulation of an incoming message to be removed later on
    //     var name = $("#name").val();
    //     var user_id = $("#user_id").val();
    //     var message = $("#message").val();
    //     var complaint_id = $("#complaint_id").val();
    //     var target = $("#target").val();
    //     //using following to send a message
    //     $.post("sendmessage", { username: name, user_id: user_id, target: target, message: message, complaint_id: complaint_id }).done(function (data) {
    //         console.log(data);
    //     });
    // });
    /////////////////////////////////////////////////////////////////////////////
    $(".messages-list").on('click', ".messages-item", function () {//when user presses a chat from the list
        $("#complaint-view").hide();

        $(".message-content").html(loader);
        $(this).attr("style", "");
        $(".active").attr("class", "messages-item");
        $(this).attr("class", "messages-item active");
        var active_id = $(".active").children(".user_id").val();
        console.log("Active chat user: " + active_id);
        $(".display-complaint-id").children(".display").html($(this).children(".complaint_id").val());
        $(".display-user-id").children(".display").html($(this).children(".user_id").val());
        $(".display-user-name").children(".display").html($(this).children(".messages-item-from").html());
        var complaint_id = $(this).children(".complaint_id").val();
        var request = "chat/get/" + my_id + "/" + active_id + "/" + complaint_id + "/15";
        console.log(request);

        $.get(request, function (data) {
            $(".message-content").html('');
            console.log(data);
            $.each(data, function (key, value) {
                if (value.sender_id == my_id) {
                    $(".message-content").prepend(
                        `<div class="chat-container darker">
                        <img src="https://centrik.in/wp-content/uploads/2017/02/user-image-.png" alt="Avatar" class="right">
                        <p>`+ value.message + `</p>
                        <span class="time-left">`+ value.created_at + `</span>
                        </div>`
                    );
                }
                else {
                    $(".message-content").prepend(
                        `<div class="chat-container">
                        <img src="https://centrik.in/wp-content/uploads/2017/02/user-image-.png" alt="Avatar">
                        <p>`+ value.message + `</p>
                        <span class="time-right">`+ value.created_at + `</span>
                        </div>`
                    );
                }

            });
        }).done(function () {
            scrollToBottom();
            getComplaintDescription();
        });
    });

    $(".messages-list").on('click', ".fa-trash-o", function (e) {//to remove a chat from chat list
        e.stopPropagation();
        var parent_chat = $(this).parents(".messages-item");
        var user_id = parent_chat.children(".user_id").val();
        var complaint_id = parent_chat.children(".complaint_id").val();
        $.get("activechats/remove/" + my_id + "/" + user_id + "/" + complaint_id).done(function () {
            console.log("removed from list");
            console.log(parent_chat);
            parent_chat.remove();
        });
    });

    $("#send-message").click(function () {//when admin presses the send button

        var name = "Admin";
        var message = $("#send-text").val();
        if (message.length != 0) {
        $("#send-text").attr("disabled", "disabled");
        $("#send-message").attr("disabled", "disabled");
        }
        else {
            console.log("no text: exiting.....");
            return false;
        }
        var user_id = $(".display-user-id").children(".display").html();
        var complaint_id = $(".display-complaint-id").children(".display").html();
        var formatedTime = getDateTime();
        $.post("sendmessage", { username: name, user_id: my_id, target: user_id, message: message, complaint_id: complaint_id })
            .done(function (data) {
                console.log(data);
                $(".message-content").append(
                    `<div class="chat-container darker">
                <img src="https://centrik.in/wp-content/uploads/2017/02/user-image-.png" alt="Avatar" class="right">
                <p>`+ message + `</p>
                <span class="time-left">`+ formatedTime + `</span>
                </div>`);

                $("#send-text").val('');
                $("#" + user_id + `-` + complaint_id).children(".messages-item-time").children("span").html(formatedTime);
                $("#" + user_id + `-` + complaint_id).children(".messages-item-preview").html(message.substr(0, 30) + ".....");
                scrollToBottom();
                $("#send-text").removeAttr("disabled");
                $("#send-message").removeAttr("disabled");
                $("#send-text").focus();
            }
            );
    });
    $('#send-text').keypress(function (e) {
        var key = e.which;
        if (key == 13 && !e.shiftKey) {
            $("#send-message").click();
            return false;
        }
    });
    /////////////////chat scripts end here////////////////
    function getComplaintDescription() {
        var complaint_id = $(".display-complaint-id").children(".display").html();
        $.get('getcomplaintdescription/' + complaint_id, function (data) {
            $("#complaint-view").show();
            $("#complaint-view").attr("data-content", data.description);
        });
    }
});
