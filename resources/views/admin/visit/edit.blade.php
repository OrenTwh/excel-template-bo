<?php
$visit_edit = 'visit_edit';
$id = request()->route( 'id' );
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.visits' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <form id="{{ $visit_edit }}">
            <input type="hidden" name="id" value="{{ $id }}">

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label" for="visit_date">{{ __( 'visit.visit_date' ) }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="visit_date" name="visit_date" placeholder="{{ __( 'visit.visit_date_placeholder' ) }}" required>
                        <div class="form-note">{{ __( 'visit.visit_date_note' ) }}</div>
                    </div>
                </div>

                <div class="col-lg-6 d-none">
                    <div class="form-group">
                        <label class="form-label" for="reference">{{ __( 'visit.reference' ) }}</label>
                        <input type="text" class="form-control" id="reference" name="reference" placeholder="{{ __( 'visit.reference_placeholder' ) }}">
                        <div class="form-note">{{ __( 'visit.reference_note' ) }}</div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label" for="user_id">{{ __( 'visit.user' ) }}</label>
                        <select class="form-control form-select" id="user_id" name="user_id">
                            <option value="">{{ __( 'visit.select_user' ) }}</option>
                        </select>
                        <div class="form-note">{{ __( 'visit.user_note' ) }}</div>
                    </div>
                </div>

                <div class="col-12">
                    <hr class="preview-hr">
                    <h6 class="title">{{ __( 'visit.details' ) }} <span class="text-danger">*</span></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="visit_details_table">
                            <thead>
                                <tr>
                                    <th width="40%">{{ __( 'visit.ticket_type' ) }}</th>
                                    <th width="15%">{{ __( 'visit.unit_price' ) }}</th>
                                    <th width="15%">{{ __( 'visit.quantity' ) }}</th>
                                    <th width="20%">{{ __( 'visit.total_price' ) }}</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody id="details_tbody">
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary mt-3"" id="add_detail_row"><em class="icon ni ni-plus"></em> {{ __( 'visit.add_ticket' ) }}</button>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary">{{ __( 'template.update' ) }}</button>
                        <a href="{{ route( 'admin.visit.index' ) }}" class="btn btn-lg btn-light">{{ __( 'template.cancel' ) }}</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>

let ticketTypes = [];
let rowIndex = 0;

document.addEventListener( 'DOMContentLoaded', function() {

    // Initialize flatpickr for visit_date
    let visitDatePicker = $( '#visit_date' ).flatpickr({
        disableMobile: true,
        dateFormat: "Y-m-d",
        minDate: "today",
    });

    // Load users for dropdown
    function loadUsers( selectedUserId = null ) {
        $.ajax({
            url: '{{ route( "admin.user.allUsers" ) }}',
            type: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'length': -1,
            },
            success: function( response ) {
                let userSelect = $( '#user_id' );
                if ( response.users && response.users.length > 0 ) {
                    $.each( response.users, function( index, user ) {

                        const callingCode = user.calling_code ?? '';
                        const phoneNumber = user.phone_number ?? '';

                        const phoneDisplay = ( callingCode || phoneNumber )
                            ? callingCode + phoneNumber
                            : '-';

                        let selected = selectedUserId && user.id == selectedUserId ? 'selected' : '';
                        userSelect.append(
                            '<option value="' + user.id + '" ' + selected + '>' +
                                phoneDisplay +
                                ' (' + ( user.email ?? '-' ) + ')' +
                            '</option>'
                        );

                        // userSelect.append( '<option value="' + user.id + '" ' + selected + '>' + user.calling_code + user.phone_number + ' (' + user.email + ')</option>' );
                    });
                }
            }
        });
    }

    // Load ticket types
    function loadTicketTypes( callback ) {
        $.ajax({
            url: '{{ route( "admin.ticket_type.all" ) }}',
            type: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'length': -1,
            },
            success: function( response ) {
                if ( response.ticket_types && response.ticket_types.length > 0 ) {
                    ticketTypes = response.ticket_types;
                    if ( callback ) callback();
                }
            }
        });
    }

    // Load existing data
    function loadData() {
        $.ajax({
            url: '{{ route( "admin.visit.one" ) }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: { id: '{{ $id }}' },
            success: function( response ) {
                let data = response.data;

                visitDatePicker.setDate( data.visit_date );
                $( '#reference' ).val( data.reference );

                // Load users and select the current user
                loadUsers( data.user.id );

                // Load ticket types then populate details
                loadTicketTypes( function() {
                    // Populate visit details
                    if ( data.details && data.details.length > 0 ) {
                        $( '#details_tbody' ).empty();
                        $.each( data.details, function( index, detail ) {
                            addDetailRow( detail );
                        });
                    } else {
                        addDetailRow();
                    }
                    updateRemoveButtons();
                });
            },
            error: function( xhr ) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __( "template.error" ) }}',
                    text: xhr.responseJSON.message || '{{ __( "template.something_went_wrong" ) }}',
                }).then(function() {
                    window.location.href = '{{ route( "admin.visit.index" ) }}';
                });
            }
        });
    }

    function addDetailRow( detail = null ) {
        let newRow = $( `
            <tr class="detail-row">
                <td>
                    <select class="form-control form-select ticket-type-select" name="details[${rowIndex}][ticket_type_id]" required>
                        <option value="">{{ __( 'visit.select_ticket_type' ) }}</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control unit-price" readonly>
                </td>
                <td>
                    <input type="number" class="form-control quantity-input" name="details[${rowIndex}][quantity]" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" class="form-control total-price-input" name="details[${rowIndex}][total_price]" step="0.01" min="0" readonly required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-row"><em class="icon ni ni-trash"></em></button>
                </td>
            </tr>
        ` );

        $( '#details_tbody' ).append( newRow );

        // Populate ticket types dropdown
        let select = newRow.find( '.ticket-type-select' );
        $.each( ticketTypes, function( index, ticket ) {
            select.append( '<option value="' + ticket.encrypted_id + '">' + ticket.name + ' - RM ' + ticket.price + '</option>' );
        });

        // If detail data is provided, populate the row
        if ( detail ) {
            select.val( detail.ticket_type_id );
            newRow.find( '.unit-price' ).val( detail.ticket_type_price );
            newRow.find( '.quantity-input' ).val( detail.quantity );
            newRow.find( '.total-price-input' ).val( detail.total_price );
        }

        rowIndex++;
    }

    // Add new detail row
    $( document ).on( 'click', '#add_detail_row', function() {
        addDetailRow();
        updateRemoveButtons();
    });

    // Remove detail row
    $( document ).on( 'click', '.remove-row', function() {
        $( this ).closest( 'tr' ).remove();
        updateRemoveButtons();
    });

    // Handle ticket type selection
    $( document ).on( 'change', '.ticket-type-select', function() {
        let row = $( this ).closest( 'tr' );
        let selectedId = $( this ).val();

        if ( selectedId ) {
            let ticket = ticketTypes.find( t => t.encrypted_id === selectedId );
            if ( ticket ) {
                row.find( '.unit-price' ).val( ticket.price );
                calculateRowTotal( row );
            }
        } else {
            row.find( '.unit-price' ).val( '' );
            row.find( '.total-price-input' ).val( '' );
        }
    });

    // Handle quantity change
    $( document ).on( 'input', '.quantity-input', function() {
        let row = $( this ).closest( 'tr' );
        calculateRowTotal( row );
    });

    function calculateRowTotal( row ) {
        let unitPrice = parseFloat( row.find( '.unit-price' ).val() ) || 0;
        let quantity = parseInt( row.find( '.quantity-input' ).val() ) || 0;
        let total = unitPrice * quantity;
        row.find( '.total-price-input' ).val( total.toFixed(2) );
    }

    function updateRemoveButtons() {
        let rowCount = $( '.detail-row' ).length;
        if ( rowCount === 1 ) {
            $( '.remove-row' ).prop( 'disabled', true );
        } else {
            $( '.remove-row' ).prop( 'disabled', false );
        }
    }

    loadData();

    $( '#{{ $visit_edit }}' ).on( 'submit', function( e ) {
        e.preventDefault();

        let form = $( this );
        let formData = new FormData( form[0] );
        let submitBtn = form.find( 'button[type="submit"]' );

        submitBtn.prop( 'disabled', true ).html( '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __( "template.loading" ) }}' );

        $.ajax({
            url: '{{ route( "admin.visit.update" ) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function( response ) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __( "template.success" ) }}',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = '{{ route( "admin.visit.index" ) }}';
                });
            },
            error: function( xhr ) {
                submitBtn.prop( 'disabled', false ).html( '{{ __( "template.update" ) }}' );

                if ( xhr.status === 422 ) {

                    let resp = xhr.responseJSON;
                    let errorMessage = '';

                    // if Laravel validation response (errors object exists)
                    if ( resp.errors ) {
                        $.each( resp.errors, function( key, value ) {
                            errorMessage += value[0] + '<br>';
                        });
                    }
                    // if simple message exists
                    else if ( resp.message ) {
                        errorMessage = resp.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: '{{ __( "template.validation_error" ) }}',
                        html: errorMessage,
                    });
                }
                    else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __( "template.error" ) }}',
                        text: xhr.responseJSON.message || '{{ __( "template.something_went_wrong" ) }}',
                    });
                }
            }
        });
    });
});

</script>
