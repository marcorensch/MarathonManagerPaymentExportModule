function getRegistrations(eventId){
    let request = {
        'option': 'com_ajax',
        'module': 'mmanager_export',
        'method': 'getRegistrations',
        'data': eventId,
        'format': 'json'
    };

    return jQuery.ajax({
        type: 'POST',
        data: request
    });
}

function setPaymentStatus(registrationId, status){
    let data = {
        'registrationId' : registrationId,
        'status' : status
    };
    let str_data = JSON.stringify(data);
    let request = {
        'option': 'com_ajax',
        'module': 'mmanager_export',
        'method': 'setPayment',
        'data': str_data,
        'format': 'json'
    };

    return jQuery.ajax({
        type: 'POST',
        data: request
    });
}

function createExport(eventId){
    let request = {
        'option': 'com_ajax',
        'module': 'mmanager_export',
        'method': 'createXls',
        'data': eventId,
        'format': 'json'
    };

    return jQuery.ajax({
        type: 'POST',
        data: request
    });
}