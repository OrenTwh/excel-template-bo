<?php
    $orders = $data['orders']['orders'];
    $grades = $data['grades'];
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title">{{ __( 'template.sales_report' ) }}</h5>
            <button class="btn btn-sm btn-primary" id="export" type="button">{{ __( 'template.export' ) }}</button>
        </div>
        <hr>
        <div class="mb-3 row">
            <label for="order_date" class="col-sm-5 col-form-label">{{ __( 'order.order_date' ) }}</label>
            <div class="col-sm-7">
                <input type="text" class="form-control form-control-sm" id="order_date" placeholder="{{ __( 'datatables.search_x', [ 'title' => __( 'order.order_date' ) ] ) }}">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="6"></th>
                        <th colspan="3" class="text-center"><strong>{{ __('order.order_items') }}</strong></th>
                        <th colspan="3" class="text-center"><strong>{{ __('order.order_items') }}</strong></th>
                        <th colspan="3" class="text-center"><strong>{{ __('order.order_items') }}</strong></th>
                        <th colspan="3" class="text-center"><strong>{{ __('order.order_items') }}</strong></th>
                        <th colspan="2"></th>
                    </tr>
                    <tr>
                        <th><strong>{{ __('datatables.no') }}</strong></th>
                        <th><strong>{{ __('order.reference') }}</strong></th>
                        <th><strong>{{ __('order.order_date') }}</strong></th>
                        <th><strong>{{ __('order.owner') }}</strong></th>
                        <th><strong>{{ __('order.farm') }}</strong></th>
                        <th><strong>{{ __('order.buyer') }}</strong></th>
                        @foreach($grades as $grade)
                            <th><strong>{{ __('order.grade') }}</strong></th>
                            <th><strong>{{ __('order.rate') }}</strong></th>
                            <th><strong>{{ __('order.weight') }}</strong></th>
                        @endforeach
                        {{-- <th><strong>{{ __('order.subtotal') }}</strong></th> --}}
                        <th><strong>{{ __('order.total') }}</strong></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandSubtotalTotal = $grandTotalTotal = 0;
                        $grandRates['A']['rates'] = 0;
                        $grandRates['A']['weight'] = 0;
                        $grandRates['B']['rates'] = 0;
                        $grandRates['B']['weight'] = 0;
                        $grandRates['C']['rates'] = 0;
                        $grandRates['C']['weight'] = 0;
                        $grandRates['D']['rates'] = 0;
                        $grandRates['D']['weight'] = 0;
                    @endphp
                    @foreach ($orders as $order)
                        <tr class='clickable-row' data-href='{{ route( 'admin.order.edit', [ 'id' => $order['encrypted_id'] ] ) }}'>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ $order['reference'] }}</td>
                            <td>{{ $order['order_date'] }}</td>
                            <td>{{ $order['farm']['owner']['name'] }}</td>
                            <td>{{ $order['farm']['title'] }}</td>
                            <td>{{ $order['buyer']['name'] }}</td>
            
                            @php
                                $grandRates = [];
                            
                                foreach($grades as $grade) {
                                    $grandRates[$grade]['rates'] = 0;
                                    $grandRates[$grade]['weight'] = 0;
                                }
                            
                                foreach($order['order_items'] as $orderItem) {
                                    $grade = $orderItem['grade'];
                                    $grandRates[$grade]['rates'] += $orderItem['rate'];
                                    $grandRates[$grade]['weight'] += $orderItem['weight'];
                                }
                                
                            @endphp
                            
                            @foreach($grades as $grade)
                                <td>{{ $grade }}</td>
                                <td>{{ $grandRates[$grade]['rates'] != 0 ? $grandRates[$grade]['rates'] : '-' }}</td>
                                <td>{{ $grandRates[$grade]['weight'] != 0 ? $grandRates[$grade]['weight'] : '-' }}</td>                        
                            @endforeach
            
                            {{-- <td>{{ $order['subtotal'] }}</td> --}}
                            <td>{{ $order['total'] }}</td>
            
                            @php
                                $grandTotalTotal += $order['total'];
                                $grandSubtotalTotal += $order['subtotal'];
                            @endphp
                        </tr>
                    @endforeach
                </tbody>
                {{-- <tfoot>
                    <tr>
                        <td colspan="6">{{ __('datatables.grand_total') }}</td>
                        @foreach($grades as $grade)
                            <td colspan=""></td>
                            <td>{{ $grandRates[$grade]['rates'] }}</td>
                            <td>{{ $grandRates[$grade]['weight'] }}</td>
                        @endforeach
                        <td>{{ $grandSubtotalTotal }}</td>
                        <td>{{ $grandTotalTotal }}</td>
                    </tr>
                </tfoot> --}}
            </table>
        </div>
    </div>
</div>

<!-- ... Your HTML code ... -->

<script>
    document.addEventListener('DOMContentLoaded', function () {

        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
        });

        function appendDataToTable(data) {
            var tableBody = document.querySelector('.table-bordered tbody');

            data.forEach(function (row) {
                var newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${row.no}}</td>
                    <td>${row.reference}}</td>
                    <td>${row.order_date}}</td>
                    <td>${row.owner}}</td>
                    <td>${row.farm}}</td>
                    <td>${row.buyer}}</td>
                    <td>${row.grade}}</td>
                    <td>${row.rate}}</td>
                    <td>${row.weight}}</td>
                    <td>${row.subtotal}}</td>
                    <td>${row.total}}</td>
                `;
                tableBody.appendChild(newRow);
            });
        }

        $( '#export' ).on( 'click', function() {
            window.location.href = '{{ route( 'admin.order.export' ) }}?date=' + $( '#order_date' ).val();
        } );

        $('#order_date').flatpickr({
            disableMobile: true,
            defaultDate: '{{ request('date') ? request('date') : date('Y m') }}',
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y m",
                })
            ],
            onClose: function( selected, dateStr, instance ) {
                window.location.href = '{{ route( 'admin.order.salesReport' ) }}?date=' + dateStr;
            }
        });

        
    });
</script>
