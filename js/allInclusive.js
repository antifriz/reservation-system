/**
 * Created by Ivan on 5.1.2015..
 */


function isInt(value) {
    return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
}

$.fn.bootstrapSwitch.defaults.onText = 'DA';
$.fn.bootstrapSwitch.defaults.offText = 'NE';
$.fn.bootstrapSwitch.defaults.onColor = 'success';
$.fn.bootstrapSwitch.defaults.offColor = 'danger';
$('[type="checkbox"]').bootstrapSwitch();

//TODO: uredi hrvatska imena
$.fn.datepicker.dates['hr'] = {
    days: ["Nedjelja", "Pondejeljak", "Utorak", "Srijeda", "Cetvrtak", "Petak", "Subota", "Nedjelja"],
    daysShort: ["Ned", "Pon", "Uto", "Sri", "Ce", "Pet", "Sub", "Ned"],
    daysMin: ["Ne", "Po", "Ut", "Sr", "Ce", "Pe", "Su", "Ne"],
    months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    today: "Danas",
    clear: "Prazno"
};
var TIMEPICKER_LANG = {am: 'am', pm: 'pm', AM: 'AM', PM: 'PM', decimal: '.', mins: 'minuta', hr: 'sat', hrs: 'sata'};


var kontroler = "/kontroler.php";
function posaljiPost(data, successFunction) {
    $.post(kontroler, data, successFunction);
}
function changeKolicina(node, add, minVal) {
    var i = $(node).closest('div').find('input');
    if (parseInt(i.val()) + add >= minVal)
        i.val(parseInt(i.val()) + add);
}
function osvjezi(err) {
    if (!err || err.length <= 0) {
        location.reload();
        return;
    }
    bootbox.alert(err);
}
function osvjeziOk(err) {
    if (err == 'OK') {
        location.reload();
        return;
    }
    bootbox.alert(err?err:"null");
}

var dropdown = $(".dropdown");