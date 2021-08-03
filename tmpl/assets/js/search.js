jQuery(document).ready(function($){
    $('.nx-registrations').on('keyup','.search',function(){
        searchRegistrationsList($(this).val());
    });
});

function searchRegistrationsList(searchFor) {
    var filter, table, tr, td_name, td_ref, i, txtValue;
    filter = searchFor.toUpperCase();
    table = document.getElementById("registrationsTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td_name = tr[i].getElementsByTagName("td")[1];
        td_ref = tr[i].getElementsByTagName("td")[4];
        if (td_name) {
            td_name_txtValue = td_name.textContent || td_name.innerText;
            td_ref_txtValue = td_ref.textContent || td_ref.innerText;
            if (td_name_txtValue.toUpperCase().indexOf(filter) > -1 || td_ref_txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}