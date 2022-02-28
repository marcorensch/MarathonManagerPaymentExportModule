jQuery(document).ready(function ($){
    $('#nx-export-switcher').on('click','.event',function(){
        eventId = $(this).attr('data-event-id');
        $('#teams table tbody').html('');
        $('#eventname').text($(this).attr('data-event-name'));
        $('#nx-export-switcher .exportBtn').attr('data-event-id',eventId);
        $('#nx-export-switcher .waiter').show();

        $.when(getRegistrations(eventId)).done(function(data){
            if(data.success){
                console.log(data);
                for(let registration of data.data){
                    console.log(registration);
                    let checked = parseInt(registration.paid) ? 'checked' : '';
                    let row = '<tr>'+
                                    '<td>'+registration.id+'</td>'+
                                    '<td><strong>'+registration.teamname+'</strong></td>'+
                                    '<td><a href="mailto:'+registration.contactemail+'"><i class="fas fa-envelope"></i></a><span style="margin-left:6px; display: inline-block">'+registration.contactemail+'</span></td>'+
                                    '<td>'+registration.created+'</td>'+
                                    '<td>'+registration.emergency_number+'</td>'+
                                    '<td>'+registration.reference_num+'</td>'+
                                    '<td><label data-registration-id="'+registration.id+'" class="paidswitch el-switch">'+
                                        '<input type="checkbox" name="switch" value="1" '+checked+' hidden>'+
                                        '<span class="el-switch-style"></span>'+
                                        '</label>'+
                                    '</td>'+
                                '</tr>';
                    $('#teams table tbody').append(row);
                }
                setTimeout(function(){
                    $('#nx-export-switcher .waiter').fadeOut('slow');
                },500);
            }else{
                console.log('AJAX CALL failed');
                console.error(data);
            }
        }).fail(function (err){
            console.log('catched error');
            console.error(err);
        });
    });

    $(document).on('change','.paidswitch',function(){
        let registrationId = $(this).attr('data-registration-id');
        let $checkbox = $(this).find("input[type='checkbox']");
        let message;
        if ($checkbox.is(":checked")){
            $.when(setPaymentStatus(registrationId, 1)).then(function(response){
                if(response.success && response.data === true){
                    UIkit.notification({
                        message: 'Gespeichert',
                        status: 'success',
                        pos: 'top-right',
                        timeout: 3000
                    });
                }else{
                    if(typeof response.data === 'string'){
                        message = '<b>Fehler</b><br>'+response.data;
                    }else{
                        message = '<b>Fehler</b><br>Bitte Konsole prüfen';
                    }
                    UIkit.notification({
                        message: message,
                        status: 'danger',
                        pos: 'top-right',
                        timeout: 3000
                    });
                    console.error(response.data);
                }
            });
        }else{
            $.when(setPaymentStatus(registrationId, 0)).then(function(response){
                if(response.success && response.data === true){
                    UIkit.notification({
                        message: 'Gespeichert',
                        status: 'success',
                        pos: 'top-right',
                        timeout: 3000
                    });
                }else{
                    if(typeof response.data === 'string'){
                        message = '<b>Fehler</b><br>'+response.data;
                    }else{
                        message = '<b>Fehler</b><br>Bitte Konsole prüfen';
                    }
                    UIkit.notification({
                        message: message,
                        status: 'danger',
                        pos: 'top-right',
                        timeout: 3000
                    });
                    console.error(response.data);
                }
            });
        }
    });

    $('#nx-export-switcher').on('click','.exportBtn', function(){
        console.log($(this).attr('data-event-id'));
        $.when(createExport($(this).attr('data-event-id'))).then(function(response){
            console.log(response);
            if(typeof response.data === 'string'){
                if(response.data.includes('http')){
                    $('#export-download').attr('href',response.data);
                    $('#export-download')[0].click();
                }
            }
        });
    });
});
