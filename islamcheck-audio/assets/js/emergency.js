// var pusher = new Pusher('ecfcf91d961449012c63', {//api key and cluster to be changed accordingly here
//     cluster: 'ap2',
//     encrypted: true //change to true when in deployment <----
// });

// // Enable pusher logging - don't include this in production <----
// Pusher.logToConsole = true;

// var channel = pusher.subscribe('admin');

// channel.bind('App\\Events\\HelpEvent', function (data) {
//     var title;
//     var message = data.name + " wants help. Click here for more information";
//     if (data.type == helpTypes['emergency']) {
//         title = 'Alert!! Emergency Help';
//     }
//     else {
//         title = 'Alert!! Protective Mode Help';
//     }
//     notifyMe(title, message);
//     toastr.error(message, title, {
//         closeButton: true,
//         tapToDismiss: false,
//         toastClass: 'toast',
//         containerId: 'toast-container',
//         debug: false,

//         showMethod: 'fadeIn', //fadeIn, slideDown, and show are built into jQuery
//         showDuration: 300,
//         showEasing: 'swing', //swing and linear are built into jQuery
//         onShown: undefined,
//         hideMethod: 'fadeOut',
//         hideDuration: 1000,
//         hideEasing: 'swing',
//         onHidden: undefined,
//         closeMethod: false,
//         closeDuration: false,
//         closeEasing: false,

//         extendedTimeOut: 1000,
//         iconClasses: {
//             error: 'toast-error',
//             info: 'toast-info',
//             success: 'toast-success',
//             warning: 'toast-warning'
//         },
//         iconClass: 'toast-error',
//         positionClass: 'toast-top-right',
//         timeOut: 0, // Set timeOut and extendedTimeOut to 0 to make it sticky
//         extendedTimeOut: 0,
//         titleClass: 'toast-title',
//         messageClass: 'toast-message',
//         escapeHtml: false,
//         target: 'body',
//         closeHtml: '<button type="button">&times;</button>',
//         newestOnTop: true,
//         preventDuplicates: false,
//         progressBar: false,
//         onclick: function () {
//             var win = window.open(baseUrl+'admin/help/'+data.id, '_blank');
//             if (win) {
//                 //Browser has allowed it to be opened
//                 win.focus();
//             } else {
//                 //Browser has blocked it
//                 alert('Please allow popups for this website');
//             }
//         },
//     });
// });
