<?php
    $exclusive_deals = $data['exclusive_deals'];
?>

<style>
    .sortable-placeholder {
        background: #f8f9fa;
        border: 2px dashed #ccc;
        height: 100px;
    }
    .deal-img {
        width: 100%;
        max-width: 150px;
        object-fit: cover;
    }
    .list-group-item {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    #deal-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 0;
    }
    #deal-list .list-group-item {
        width: 100%;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
    }

    #deal-list li:hover {
        background: #e9ecef;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .sortable-placeholder {
        background: #dee2e6;
        border: 2px dashed #6c757d;
        height: 130px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .deal-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .deal-actions .btn {
        min-width: 80px;
        padding: 6px 12px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .deal-actions .btn em {
        font-size: 14px;
    }
</style>

<?php $deal_create = 'deal_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.exclusive_deals' ) ) ] ) }}</h3>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
        <div class="mb-3">
            <label>{{ __( 'exclusive_deal.image' ) }}</label>
            <div class="dropzone mb-3" id="{{ $deal_create }}_image" style="min-height: 0px;">
                <div class="dz-message needsclick">
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                </div>
            </div>
            <div class="invalid-feedback"></div>
        </div>
        <ul id="deal-list" class="list-group">
            @foreach($exclusive_deals as $deal)
                <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="{{ $deal->id }}">
                    <img src="{{ asset('storage/' . $deal->image) }}" class="deal-img rounded">

                    <div class="deal-actions">
                        <button class="btn btn-primary btn-sm edit-deal" data-id="{{ $deal->id }}">
                            <em class="icon ni ni-edit"></em>
                            <span>Edit</span>
                        </button>
                        <button class="btn btn-danger btn-sm delete-deal" data-id="{{ $deal->id }}">
                            <em class="icon ni ni-trash"></em>
                            <span>Delete</span>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

    let fc = '#{{ $deal_create }}', fileID = '';

    if (Dropzone.instances.length > 0) {
        Dropzone.instances.forEach(dz => dz.destroy());
    }

    if (!$(fc + '_image').hasClass("dz-clickable")) {
        Dropzone.autoDiscover = false;
        let myDropzone = new Dropzone(fc + '_image', {
            url: "{{ route('admin.exclusive_deal.createExclusiveDeal') }}",
            maxFiles: 1,
            acceptedFiles: "image/jpg,image/jpeg,image/png",
            addRemoveLinks: true,
            params: {
                _token: "{{ csrf_token() }}",
            },
            success: function(file, response) {
                if (response.status == 200) {
                    let newDeal = $(`
                        <li class="list-group-item d-flex flex-column align-items-center justify-content-center position-relative" data-id="${response.data.id}">
                            <img src="${response.data.url}" class="deal-img rounded">

                            <div class="deal-actions">
                                <button class="btn btn-primary btn-sm edit-deal" data-id="${response.data.id}">
                                    <em class="icon ni ni-edit"></em>
                                    <span>Edit</span>
                                </button>
                                <button class="btn btn-danger btn-sm delete-deal" data-id="${response.data.id}">
                                    <em class="icon ni ni-trash"></em>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </li>
                    `);
                    $("#deal-list").append(newDeal);

                    myDropzone.removeFile(file);
                }
            }
        });
    }

    let sortableList = new Sortable(document.getElementById('deal-list'), {
        animation: 200,
        handle: ".list-group-item",
        ghostClass: "sortable-placeholder",
        onEnd: function(evt) {
            let sortedIDs = [];
            $("#deal-list li").each(function() {
                if( $(this).data("id") ){
                    sortedIDs.push($(this).data("id"));
                }
            });

            $.ajax({
                url: "{{ route('admin.exclusive_deal.updateOrder') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order: sortedIDs
                },
                success: function(response) {
                    console.log("Exclusive deal order updated successfully!");
                },
                error: function(error) {
                    console.error("Error updating exclusive deal order", error);
                }
            });
        }
    });

    $(document).on("click", ".edit-deal", function() {
        let dealId = $(this).data("id");
        window.location.href = '{{ route( 'admin.exclusive_deal.edit' ) }}?id=' + dealId;
    });

    $(document).on("click", ".delete-deal", function() {
        let dealId = $(this).data("id");
        let dealItem = $(this).closest(".list-group-item");

        $( 'body' ).loading( {
            message: '{{ __( 'template.loading' ) }}'
        } );

        $.post('{{ route("admin.exclusive_deal.updateExclusiveDealStatus") }}', {
            _token: '{{ csrf_token() }}',
            id: dealId
        }).done(function(response) {
            $( 'body' ).loading( 'stop' );

            dealItem.fadeOut(300, function() {
                $(this).remove();
            });
        }).fail(function() {
            $( 'body' ).loading( 'stop' );

            alert("Error occurred. Please check your connection.");
        });
    });

});
</script>
