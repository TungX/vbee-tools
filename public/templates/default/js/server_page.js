/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function loadButtonControl() {
    $('tbody tr').each(function () {
        showButtonControl(this);
    });
}

function showButtonControl(row) {
    if ($(row).find('td.status').text() === 'Running') {
        $(row).find('td.control .start').hide();
        $(row).find('td.control .stop').show();
        $(row).find('td.restart .restart').show();
    } else if ($(row).find('td.status').text() === 'Stopped') {
        $(row).find('td.control .start').show();
        $(row).find('td.control .stop').hide();
        $(row).find('td.restart .restart').hide();
    } else {
        $(row).find('td.control .btn').hide();
        $(row).find('td.restart .restart').hide();
    }
}

function loadEventOfServer() {
    catchEventStartServer();
    catchEventStopServer();
    catchEventRestartServer();
    catchEventAcion();
}

function catchEventStartServer() {
    $('.start').on('click', function () {
        $row = $(this).parents('tr');
        $('#modal_control').data('id', $row.find('td.id').text());
        $('#modal_control .modal-title').html('Khởi động server');
        $('#modal_control .modal-body').html('Bạn chắn chắn muốn khởi động server ' + $row.find('.name').html());
        $('#modal_control .action').html('Start');
        $('#modal_control').modal('show');
    });
}

function catchEventStopServer() {
    $('.stop').on('click', function () {
        $row = $(this).parents('tr');
        $('#modal_control').data('id', $row.find('td.id').text());
        $('#modal_control .modal-title').html('Tắt server');
        $('#modal_control .modal-body').html('Bạn chắn chắn muốn dừng server ' + $row.find('.name').html());
        $('#modal_control .action').html('Stop');
        $('#modal_control').modal('show');
    });
}

function catchEventRestartServer() {
    $('.restart').on('click', function () {
        $row = $(this).parents('tr');
        $('#modal_control').data('id', $row.find('td.id').text());
        $('#modal_control .modal-title').html('Khởi động lại server');
        $('#modal_control .modal-body').html('Bạn chắn chắn muốn khởi động lại server ' + $row.find('.name').html());
        $('#modal_control .action').html('Restart');
        $('#modal_control').modal('show');
    });
}

function catchEventAcion() {
    $('.action').on('click', function () {
        $row = $('#index-' + $('#modal_control').data('id'));
        $data = new Object();
        $data['manager_action'] = $('#modal_control .action').text().toLowerCase();
        $data['id'] = $('#modal_control').data('id');
        $.ajax({
            url: '/index/update',
            data: $data,
            method: 'POST'
        }).done(function (data) {
            var result = JSON.parse(data);
            $row.find('td.status').html(result.message);
            showButtonControl($row);
            $('#modal_control').modal('hide');
        });
    });
}