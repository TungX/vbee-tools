/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function loadEventSubtitle() {
    catchEventSynthesize();
    catchEventAcion();
    catchEventEditSubtitle();
}

function catchEventSynthesize() {
    $('.synthesize').on('click', function () {
        $row = $(this).parents('tr');
        $('#modal_control').data('id', $row.find('td.id').text());
        $('#modal_control .modal-title').html('Tổng hợp phim ' + $row.find('.name').text());
        $('#modal_control .modal-body').html('Bạn chắn chắn muốn tổng hợp phim <strong>' + $row.find('.name').text() + '</strong>');
        $('#modal_control .action').html('Tổng hợp');
        $('#modal_control').modal('show');
    });
}

function catchEventEditSubtitle() {
    $('.edit_subtitle').on('click', function () {
        $action = 'update';
        var $row = $(this).parents('tr');
        var status = $row.data('status');
        var input_disables = ['voice_name','subtitle_server', 'callback', 'subtitle_path'];
        $('#modal_info .modal-body input').each(function () {
            if(input_disables.indexOf($(this).attr('name')) > -1 && status == 0){
                $(this).prop('disabled', true);
            }
            if ($(this).attr('type') !== 'file') {
                $(this).val($row.find('td.' + $(this).attr('name')).text().trim());
            }else{
                $(this).addClass('non-required');
            }
        });
        $('#modal_info .modal-title').html('Sửa thông tin ' + $row.find('td.title').text());
        $('#modal_info .only-edit').show();
        $('#modal_info').modal('show');
    });
}

function catchEventAcion() {
    $('.action').on('click', function () {
        $row = $('#index-' + $('#modal_control').data('id'));
        $data = new Object();
        $data['id'] = $('#modal_control').data('id');
        $data['status'] = 0;
        $.ajax({
            url: '/subtitle/synthesize',
            data: $data,
            method: 'POST'
        }).done(function (respone) {
        });
        location.reload();
    });

}