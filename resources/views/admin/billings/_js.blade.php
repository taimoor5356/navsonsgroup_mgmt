<script>
    $(document).on('change', '.bill-field-form', function() {
        url = "{{url('admin/billings/update-bill-field')}}";
        var id = $(this).attr('data-bill-id');
        var fieldValue = $(this).val();
        var key = $(this).attr('data-bill-key-name');
        var data = {
            _token: "{{csrf_token()}}",
            bill_id: id,
            [key]: fieldValue,
        };
        ajaxFunction(url, "POST", data);
        setTimeout(function() {
            // refreshTable();
        }, 5000);
    });

    function refreshTable() {
        window._table.draw(false);
    }

    function ajaxFunction(url, method, data) {
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.status == true) {
                    $.toast('Successfull');
                } else {
                    $.toast('Something went wrong');
                }
            },
            error: function(error) {
                // alert('failed');
            }
        })
    }


    // $(document).on('change', '.selected_user_id', function() {
    //     var _this = $(this);
    //     var userId = $(this).val();
    //     var billId = _this.attr('data-bill-id');
    //     var url = "{{route('filter_all_data')}}";
        
    // });

    // Handle toggle column visibility
    $('input.toggle-vis').on('change', function(e) {
        let columnIdx = $(this).attr('data-column');
        let column = window._table.column(columnIdx);

        // Toggle the visibility
        column.visible(!column.visible());
    });

    // Handle dropdown toggle
    $('.dropdown-button').on('click', function() {
        $(this).parent('.dropdown').toggleClass('open');
    });

    // Close dropdown if clicked outside
    $(document).on('click', function(event) {
        if (!$(event.target).closest('.dropdown').length) {
            $('.dropdown').removeClass('open');
        }
    });

    // $(document).on('submit', '#export-excel-form', function (e) {
    //     e.preventDefault();
    //     $('#export-excel').attr('disabled', true);
    //     var url = $(this).attr('action');
    //     var data = {
    //         _token: "{{csrf_token()}}",
    //         // date_of_service: $('#date_of_service').val(),
    //         // date_of_birth: $('#date_of_birth').val(),
    //         // subscriber_dob: $('#subscriber_dob').val(),
    //         // submission_date: $('#submission_date').val(),
    //         received_date: $('#received_date').val(),
    //     };
    //     $.ajax({
    //         url: url,
    //         method: "POST",
    //         data: data,
    //         success: function(response) {
    //             if (response.status == true) {
    //                 $('#export-excel').attr('disabled', false);
    //                 // var downloadUrl = response.downloadUrl;
    //                 // var a = document.createElement('a');
    //                 // a.href = downloadUrl;
    //                 // a.download = 'exported_file.xlsx';  // Specify the desired file name here
    //                 // document.body.appendChild(a);
    //                 // a.click();
    //                 // document.body.removeChild(a);
    //                 // window.location.reload();
    //             } else {
    //                 alert('failed');
    //                 $('#export-excel').attr('disabled', false);
    //             }
    //         },
    //         error: function(error) {
    //             alert('failed');
    //         }
    //     });
    // });
</script>