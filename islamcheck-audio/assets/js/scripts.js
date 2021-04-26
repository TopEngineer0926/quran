function confirm_delete() {
    // console.log(arguments[0]);
    // return false;
    $('#confirm-delete').attr('action',arguments[0]);
}
$(function () {
    $('.timepicker').datetimepicker({
        format: 'Y-m-d H:i:s',

    });
});
$(window).load(function(){
    $('.loader-background').remove();
});

$(document).ready(function () {
    $(".active").parent().parent("li").attr("class", "active open");
});




/*-----------------------------------------------------------------------------------------------------------*/



// // Get the modal
// var modal = document.getElementById("myModal");
// //var modal = document.getElementsByClassName("modal");

// // Get the button that opens the modal
// var btn = document.getElementById("myBtn");

// // Get the <span> element that closes the modal
// var span = document.getElementsByClassName("close")[0];

// // When the user clicks on the button, open the modal
// if (btn != null) {
//     btn.onclick = function () {
//         modal.style.display = "block";
//     }
// }



// // When the user clicks on <span> (x), close the modal
// if (span != null) {
//     span.onclick = function () {
//         modal.style.display = "none";
//     }
// }


// // When the user clicks anywhere outside of the modal, close it
// window.onclick = function (event) {
//     if (event.target == modal) {
//         modal.style.display = "none";
//     }
// }

//--------------------------------------------------------------------------------------//

/*function editTender()
{
    $("#myModal").css("display", "block");
    alert(arguments[0]);

    $.getJSON(baseUrl + "api/getTenderCategory", function(result) {
        var options = $("#dropdown-categoryId");
        //don't forget error handling!
        $.each(result, function() {
            options.append($("<option />").val(this.id).text(this.name));
        });
        $options.val('2');
        console.log('2');

    });

    $.getJSON(baseUrl + "api/getTenderZone", function(result) {
        var options = $("#dropdown-zoneID");
        //don't forget error handling!
        $.each(result, function() {
            options.append($("<option />").val(this.id).text(this.name));
        });
       // options.val(arguments[0]);
    });
    //$("#dropdown-categoryId").val('2');
    console.log('123');


}*/
