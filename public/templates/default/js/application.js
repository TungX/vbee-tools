/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var $action;
$(document).ready(function () {
    $.ajax({
        url: "/dictionary/create-phonetics",
        method: 'POST'
    });
    $('.active').removeClass('active');
    $('li .' + $('.wrap-content').data('controller')).addClass('active');
    init();
    catchEventSearch();
    $('#show-control').on('click', function () {
        if ($('.form-horizontal').hasClass('show-less')) {
            $('.form-horizontal').removeClass('show-less');
            $('#show-control').html('Show less');
        } else {
            $('.form-horizontal').addClass('show-less');
            $('#show-control').html('Show more');
        }
    });

    $('#modal_info').on('hidden.bs.modal', function () {
        $('#modal_info .modal-body input').css('border-color', '');
        $('#modal_info .non-required').removeClass('non-required');
        $('#modal_info .modal-body input').prop('checked', false);
        $('.save_object').prop('disabled', false);
        $('.message-required').remove();
        $('#modal_info .modal-title').html('');
        $('#modal_info .modal-body input:not([type=hidden])').val('');
        $('#modal_info .error-notifice').html('');
    });
});

function init() {
    $('#search-notify').html('');
    catchEventAdd();
    catchEventEdit();
    catchEventRemove();
    catchEventSaveObject();
    catchEventDeleteObject();
    catchEventSelectUpdate();
    if (typeof loadEventOfServer != 'undefined') {
        loadEventOfServer();
        loadButtonControl();
    }
    if (typeof catchEventSynchronize != 'undefined') {
        catchEventSynchronize();
        catchEventRequestSynchronize();
    }

    if (typeof loadEventSubtitle != 'undefined') {
        loadEventSubtitle();
    }

}

function catchEventSelectUpdate() {
    $('.synchronize_selected').off("click").on('click', function () {
        console.log('update');
        if (this.checked) {
            $('.synchronize_selected').each(function () {
                $(this).prop('checked', false);
            });
            $(this).prop('checked', true);
        }
    });
}

function catchEventEdit() {
    $('.edit_object').on('click', function () {
        $action = 'update';
        var $row = $(this).parents('tr');
        $('#modal_info .modal-body input').each(function () {
            if ($(this).attr('type') !== 'file') {
                $(this).val($row.find('td.' + $(this).attr('name')).text().trim());
            }
        });
        $('#modal_info .modal-title').html('Sửa thông tin ' + $row.find('td.title').text());
        $('#modal_info .only-edit').show();
        $('#modal_info').modal('show');
    });
}

function catchEventAdd() {
    $('#add_object').on('click', function () {
        $('#modal_info .only-edit').hide();
        $('#modal_info').modal('show');
        $action = 'create';
    });
}

function catchEventRemove() {
    $('.remove_object').on('click', function (event) {
        event.preventDefault();
        var $row = $(this).parents('tr');
        $('#modal_delete .modal-body').html('Bạn chắc chắn muốn xóa ' + $row.find('td.title').text() + '?');
        $('#modal_delete').data('id', $row.find('td.id').text());
        $('#modal_delete').modal('show');
    });
}
function catchEventChangeField() {
    $('#modal_info .modal-body input').on('keypress', function () {
        console.log('on change');
        $(this).css('border-color', '');
        $(this).parent().find('.message-required').remove();
        $('.save_object').prop('disabled', false);
    });
}
function catchEventSaveObject() {
    $('.save_object').on('click', function () {
        var $data = new Object();
        var hasFieldEmpty = false;
        $('#modal_info .modal-body input').each(function () {
            var value = $(this).val().trim();
            if ($(this).is(':checkbox')) {
                value = +$(this).is(':checked');
            }
            $data[$(this).attr('name')] = value;
            if ($(this).is(':visible') && value.length === 0 && !($(this).hasClass('non-required') || $(this).is(":disabled"))) {
                $(this).css('border-color', 'red');
                $(this).parent().append('<p class="message-required">*This field is required!</p>');
                $('.save_object').prop('disabled', true);
                hasFieldEmpty = true;
            }
        });
        if (hasFieldEmpty) {
            catchEventChangeField();
            return;
        }
        if ($('#modal_info form').attr('action').length > 0) {
            $url = $('#modal_info form').attr('action');
            $('#modal_info form').attr('action', $url + $action);
            $('#modal_info form').submit();
        }
        $('.save_object').prop('disabled', true);
        var url = "/" + $('#modal_info').data('controller-name') + "/" + $action;
        $.ajax({
            url: url,
            data: $data,
            method: 'GET'
        }).done(function (respone) {
            console.log(respone);
            try {
                var result = JSON.parse(respone);
                if (result.status === 2) {
                    $('#modal_info .error-notifice').html(result.message);
                    catchEventChangeField();
                    return;
                }
                if ($action === 'create') {
                    $row = $('.info-pattern').clone().removeClass('info-pattern');
                    $row.attr('id', $('#modal_info').data('controller-name') + '-' + result.id);
                    $('#modal_info .modal-body input').each(function () {
                        $row.find('.' + $(this).attr('name')).html($(this).val());
                    });
                    $row.find('.id').html(result.id);
                    $row.find('.order').html(+$row.find('.order').text() + 1);
                    $('tbody').append($row);
                    catchEventEdit();
                    catchEventRemove();
                } else if ($action === 'update') {
                    var $row = $('#' + $('#modal_info').data('controller-name') + '-' + $('#modal_info .modal-body input[name=id]').val());
                    $('#modal_info .modal-body input').each(function () {
                        $row.find('.' + $(this).attr('name')).html($(this).val());
                    });
                }
            } catch (e) {
            }
            $('#modal_info').modal('hide');
        });
    });
}

function catchEventDeleteObject() {
    $('.delete-object').on('click', function () {

        var $data = new Object();
        $data['id'] = $('#modal_delete').data('id');
        $.ajax({
            url: $('#modal_info').data('controller-name') + '/destroy',
            data: $data,
            method: 'POST'
        }).done(function (data) {
            $('#' + $('#modal_info').data('controller-name') + '-' + $('#modal_delete').data('id')).remove();
            $('#modal_delete').modal('hide');
        });
    });
}

function catchEventSearch() {
    $('#search').on('click', function () {
        var data = new Object();
        console.log(data);
        $('.search .form-control').each(function () {
            var value = $(this).val().trim();
            if (value.length > 0) {
                data[$(this).attr('name')] = value;
            }
        });
        $('tbody').html('');
        $('#search').prop('disabled', true);
        $.ajax({
            url: $('#modal_info').data('controller-name') + '/search',
            data: data,
            method: 'POST'
        }).done(function (data) {
            var result = JSON.parse(data);
            try {
                if (result.length > 0) {
                    $.each(result, function (index) {
                        var row = $('.info-pattern').clone().removeClass('info-pattern');
                        $.each(this, function (key, value) {
                            row.find('td.' + key).html(value);
                        });
                        row.find('td.order').html(index + 1);
                        row.attr('id', row.attr('id') + this.id);
                        $('tbody').append(row);
                    });
                    init();
                } else {
                    $('#search-notify').html('<h1>Không có kết quả phù hợp!</h1>');
                }
            } catch (e) {
            }

            $('#search').prop('disabled', false);
        });
    });
}
