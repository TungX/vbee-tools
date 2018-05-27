/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var $progressing = false;
$(document).ready(function () {
    $('.type-action .active').removeClass('active');
    if ($('#type-action').data('type-name').length > 0)
        $('li .' + $('#type-action').data('type-name')).addClass('active');
    $('#modal_control').on('hidden.bs.modal', function () {
        if (!$progressing) {
            $('#synchronize').prop('disabled', false);
        }
    });
});
var $servers = new Object();
var $server_lengths = 0;
var $server_checked = new Object();
function catchEventSynchronize() {
    $('#synchronize').on('click', function () {
        if ($('.synchronize_selected').length > 0 && $('.synchronize_selected:checked').length == 0) {
            $('#modal_control .error-notifice').text("bạn cần lựa chọn phần để đồng bộ");
            $('#modal_control .action').prop('disabled', true);
        } else {
            $('#modal_control .error-notifice').text("");
            $('#modal_control .action').prop('disabled', false);
        }
        $('#modal_control').modal('show');
        $('#synchronize').prop('disabled', true);
    });
}
function catchEventRequestSynchronize() {
    $('#modal_control .action').on('click', function () {
        $progressing = true;
        initSynchronize();
        $.ajax({
            url: $('#modal_info').data('controller-name') + '/beforesynchronize?type=' + $('#type-action').data('type-name'),
            method: 'POST'
        }).done(function (data) {
            console.log(data);
            result = JSON.parse(data);
            if (result.status === 1) {
                $servers = result.servers;
                $server_lengths = Object.keys($servers).length;
                synchronize();
                checkSynchronize();
            } else {
                $progressing = false;
                alert(result.message);
            }
        });
    });
}
function synchronize() {
    var id = -1;
    if ($('.synchronize_selected').length > 0) {
        id = +$('.synchronize_selected:checked').val();
        
    }
    $.ajax({
        url: $('#modal_info').data('controller-name')
                + '/synchronize?type=' + $('#type-action').data('type-name')
                + '&restart=' + +$(':input[name=restart]').is(':checked') + '&id=' + id,
        method: 'POST'
    }).done(function (data) {
        console.log(data);
    });
    ;
}

function checkSynchronize() {
    console.log('checkSynchronize');
    $.ajax({
        url: $('#modal_info').data('controller-name') + '/checksynchronize?type=' + $('#type-action').data('type-name'),
        method: 'POST'
    }).done(function (data) {
        result = JSON.parse(data);
        $.each(result, function () {
            if ($server_checked[result.id] == undefined) {
                if (this.status == 0) {
                    $('.syn-result').append('<p>' + $servers[this.id] + ': Not connect</p>');
                } else if (this.status == 2) {
                    $('.syn-result').append('<p>' + $servers[this.id] + ': Synchronize complete</p>');
                }
                $server_checked[this.id] = this;
            }
        });
        if (result.length < $server_lengths) {
            rate = Math.floor(result.length / $server_lengths * 100) + '%';
            $('.progress-syn .progress-bar').css('width', rate).html(rate);
            checkSynchronize();
        } else {
            $('.progress-syn .progress-bar').css('width', '100%').html('100%');
            $('#synchronize').prop('disabled', false);
            $progressing = false;
        }
    });
}

function initSynchronize() {
    $('.progress-syn, .syn-result').css('display', 'block');
    $('.progress-syn .progress-bar').css('width', '0%').html('');
    $('.syn-result').html('');
    $('#modal_control').modal('hide');
}
