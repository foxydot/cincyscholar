jQuery(document).ready(function($) {
    $('.nav-primary .menu-item.logged-in.donor').remove();
    if(!isDateSupported()){
        $('input[type=date]').datepicker({
            changeMonth: true,
            changeYear: true
        });
    }
});

var isDateSupported = function () {
    var input = document.createElement('input');
    var value = 'a';
    input.setAttribute('type', 'date');
    input.setAttribute('value', value);
    return (input.value !== value);
};