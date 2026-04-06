<?php $ord = 'spo_order_view'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'View Order' ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.sport_product_order.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>{{ __( 'Back' ) }}</span>
            </a>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Order summary card ────────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-inner">
                <h6 class="card-title mb-3">{{ __( 'Order Info' ) }}</h6>

                <div class="mb-2">
                    <span class="text-muted small">{{ __( 'Status' ) }}</span>
                    <div id="{{ $ord }}_status_badge" class="mt-1"></div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">{{ __( 'Date' ) }}</span>
                    <div id="{{ $ord }}_created_at" class="fw-medium">—</div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">{{ __( 'Customer' ) }}</span>
                    <div id="{{ $ord }}_user_name" class="fw-medium">—</div>
                    <div id="{{ $ord }}_user_email" class="text-muted small">—</div>
                    <div id="{{ $ord }}_user_phone" class="text-muted small"></div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">{{ __( 'Voucher' ) }}</span>
                    <div id="{{ $ord }}_voucher" class="fw-medium">—</div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">{{ __( 'Notes' ) }}</span>
                    <div id="{{ $ord }}_notes" class="text-muted small">—</div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">{{ __( 'Subtotal' ) }}</span>
                    <span id="{{ $ord }}_subtotal">—</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">{{ __( 'Discount' ) }}</span>
                    <span id="{{ $ord }}_discount" class="text-danger">—</span>
                </div>
                <div class="d-flex justify-content-between fw-bold">
                    <span>{{ __( 'Total' ) }}</span>
                    <span id="{{ $ord }}_total">—</span>
                </div>

                {{-- ── Status update ────────────────────────────────────────── --}}
                @can( 'edit sport_products' )
                <hr>
                <div class="mb-2">
                    <label class="form-label mb-1">{{ __( 'Update Status' ) }}</label>
                    <select class="form-select form-select-sm" id="{{ $ord }}_new_status">
                        @foreach( $data['status_labels'] as $key => $label )
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary btn-sm w-100" id="{{ $ord }}_update_status">
                    {{ __( 'Save Status' ) }}
                </button>
                @endcan

            </div>
        </div>
    </div>

    {{-- ── Order items card ──────────────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-inner p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-light">
                            <tr>
                                <th style="width:56px;"></th>
                                <th>{{ __( 'Product / Variant' ) }}</th>
                                <th class="text-end">{{ __( 'Price' ) }}</th>
                                <th class="text-center">{{ __( 'Qty' ) }}</th>
                                <th class="text-end">{{ __( 'Subtotal' ) }}</th>
                            </tr>
                        </thead>
                        <tbody id="{{ $ord }}_items">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __( 'template.loading' ) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    let statusMap = {
        10: ['warning',   '{{ __( 'Pending' ) }}'],
        20: ['primary',   '{{ __( 'Confirmed' ) }}'],
        30: ['success',   '{{ __( 'Completed' ) }}'],
        40: ['secondary', '{{ __( 'Cancelled' ) }}'],
    };

    function statusBadge( s ) {
        let [color, label] = statusMap[ s ] ?? ['light', s];
        return `<span class="badge bg-${ color }">${ label }</span>`;
    }

    function fmtMoney( v ) { return 'RM ' + parseFloat( v ?? 0 ).toFixed( 2 ); }

    // ── Load order ─────────────────────────────────────────────────────────────
    $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

    $.ajax( {
        url:  '{{ route( 'admin.sport_product_order.oneOrder' ) }}',
        type: 'POST',
        data: { '_token': '{{ csrf_token() }}', 'id': '{{ request( 'id' ) }}' },
        success: function( r ) {
            $( 'body' ).loading( 'stop' );

            $( '#{{ $ord }}_status_badge' ).html( statusBadge( r.status ) );
            $( '#{{ $ord }}_created_at'  ).text( r.created_at ? new Date( r.created_at ).toLocaleDateString( 'en-MY', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' } ) : '—' );
            $( '#{{ $ord }}_user_name'   ).text( r.user?.fullname ?? '—' );
            $( '#{{ $ord }}_user_email'  ).text( r.user?.email    ?? '' );
            $( '#{{ $ord }}_user_phone'  ).text( r.user?.phone_number?.trim() || '' );
            $( '#{{ $ord }}_voucher'     ).text( r.voucher_code ?? '—' );
            $( '#{{ $ord }}_notes'       ).text( r.notes        || '—' );
            $( '#{{ $ord }}_subtotal'    ).text( fmtMoney( r.subtotal ) );
            $( '#{{ $ord }}_discount'    ).text( r.discount > 0 ? '- ' + fmtMoney( r.discount ) : '—' );
            $( '#{{ $ord }}_total'       ).text( fmtMoney( r.total ) );

            // Pre-select current status
            $( '#{{ $ord }}_new_status' ).val( r.status );

            // Render items
            let rows = '';
            ( r.items ?? [] ).forEach( function( item ) {
                let img = item.image
                    ? `<img src="${ item.image }" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">`
                    : `<div style="width:40px;height:40px;background:#f5f6fa;border-radius:4px;"></div>`;
                rows += `<tr>
                    <td class="text-center">${ img }</td>
                    <td>${ item.name }</td>
                    <td class="text-end text-nowrap">${ fmtMoney( item.price ) }</td>
                    <td class="text-center">${ item.quantity }</td>
                    <td class="text-end text-nowrap fw-bold">${ fmtMoney( item.subtotal ) }</td>
                </tr>`;
            } );
            $( '#{{ $ord }}_items' ).html( rows || '<tr><td colspan="5" class="text-center text-muted py-3">No items</td></tr>' );
        },
        error: function() {
            $( 'body' ).loading( 'stop' );
            alert( 'Failed to load order.' );
        }
    } );

    // ── Update status ──────────────────────────────────────────────────────────
    @can( 'edit sport_products' )
    $( '#{{ $ord }}_update_status' ).on( 'click', function() {
        $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );
        $.ajax( {
            url:  '{{ route( 'admin.sport_product_order.updateOrderStatus' ) }}',
            type: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'id':     '{{ request( 'id' ) }}',
                'status': $( '#{{ $ord }}_new_status' ).val(),
            },
            success: function( r ) {
                $( 'body' ).loading( 'stop' );
                $( '#modal_success .caption-text' ).html( r.message );
                modalSuccess.toggle();
                // Refresh status badge
                let newStatus = parseInt( $( '#{{ $ord }}_new_status' ).val() );
                $( '#{{ $ord }}_status_badge' ).html( statusBadge( newStatus ) );
            },
            error: function( err ) {
                $( 'body' ).loading( 'stop' );
                $( '#modal_danger .caption-text' ).html( err.responseJSON?.message ?? 'Failed to update status.' );
                modalDanger.toggle();
            }
        } );
    } );
    @endcan

} );
</script>
