<?php $venue_edit = 'venue_edit'; ?>

<style>
    .ck-content ul { list-style-type: disc; margin-left: 20px; }
    .ck-content ol { list-style-type: decimal; margin-left: 20px; }
    .ck-content ul li, .ck-content ol li { display: list-item; }
    .ck-editor__editable_inline { min-height: 200px; }
</style>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => 'Venue' ] ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.venue.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to List</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Venue Info ────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner">

        <h6 class="overline-title text-primary-alt mb-3">General Information</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_name" class="col-sm-3 col-form-label">Name <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_edit }}_name">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row d-none">
            <label for="{{ $venue_edit }}_slug" class="col-sm-3 col-form-label">Slug</label>
            <div class="col-sm-9">
                <input type="text" class="form-control bg-light" id="{{ $venue_edit }}_slug" readonly>
                <div class="form-text">Auto-generated from name.</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_description" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
                <textarea class="form-control" style="min-height:80px;" id="{{ $venue_edit }}_description"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="overline-title text-primary-alt mb-3">Address Details</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_address_1" class="col-sm-3 col-form-label">Address Line 1 <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_edit }}_address_1">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_address_2" class="col-sm-3 col-form-label">Address Line 2</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_edit }}_address_2">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">City &amp; State <span class="text-danger">*</span></label>
            <div class="col-sm-4 pe-sm-1 mb-2 mb-sm-0">
                <input type="text" class="form-control" id="{{ $venue_edit }}_city" placeholder="City">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-5 ps-sm-1">
                <select class="form-select" id="{{ $venue_edit }}_state">
                    <option value="">— Select State —</option>
                    @foreach( ['Johor','Kedah','Kelantan','Malacca','Negeri Sembilan','Pahang','Penang','Perak','Perlis','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Labuan','Putrajaya'] as $state )
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_postcode" class="col-sm-3 col-form-label">Postcode</label>
            <div class="col-sm-3">
                <input type="text" class="form-control" id="{{ $venue_edit }}_postcode" maxlength="10">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="overline-title text-primary-alt mb-1">GPS Coordinates <span class="text-muted fw-normal text-lowercase">(optional)</span></h6>
        <p class="text-muted small mb-3">Used for map display in the mobile app.</p>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Latitude / Longitude</label>
            <div class="col-sm-4 pe-sm-1 mb-2 mb-sm-0">
                <input type="number" step="any" class="form-control" id="{{ $venue_edit }}_latitude" placeholder="-90 to 90">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-5 ps-sm-1">
                <input type="number" step="any" class="form-control" id="{{ $venue_edit }}_longitude" placeholder="-180 to 180">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="overline-title text-primary-alt mb-3">Cover Image</h6>

        <div class="mb-3 row" id="{{ $venue_edit }}_current_image_row" style="display:none;">
            <label class="col-sm-3 col-form-label">Current Image</label>
            <div class="col-sm-9">
                <img id="{{ $venue_edit }}_image_preview" src="" class="rounded border mb-2 d-block" style="max-height:150px; max-width:300px;">
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Replace Image</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="{{ $venue_edit }}_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                <div class="form-text">{{ __( 'template.leave_blank' ) }}</div>
                <div class="invalid-feedback"></div>
                <div class="mt-2" id="{{ $venue_edit }}_new_image_preview_wrap" style="display:none;">
                    <img id="{{ $venue_edit }}_new_image_preview" src="" class="rounded border" style="max-height:120px; max-width:280px;">
                    <div class="form-text text-success">New image selected — will replace current on save.</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── About Us ─────────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">About Us</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_about_us" class="col-sm-3 col-form-label">About Us</label>
            <div class="col-sm-9">
                <textarea id="{{ $venue_edit }}_about_us"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Amenities ────────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-1">Amenities</h6>
        <p class="text-muted small mb-3">Select the facilities available at this venue.</p>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_amenities" class="col-sm-3 col-form-label">Amenities</label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $venue_edit }}_amenities" multiple data-placeholder="Search amenities..."></select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Opening Hours ────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Opening Hours</h6>

        <div class="mb-3">
            @foreach( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $day )
            <div class="row align-items-center mb-2">
                <div class="col-sm-3 col-form-label">{{ ucfirst( $day ) }}</div>
                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input oh-closed-check" type="checkbox" id="{{ $venue_edit }}_oh_{{ $day }}_closed" data-day="{{ $day }}">
                        <label class="form-check-label" for="{{ $venue_edit }}_oh_{{ $day }}_closed">Closed</label>
                    </div>
                </div>
                <div class="col-sm-3 pe-sm-1 oh-time-wrap" id="{{ $venue_edit }}_oh_{{ $day }}_wrap">
                    <input type="time" class="form-control" id="{{ $venue_edit }}_oh_{{ $day }}_open">
                    <div class="form-text">Opens</div>
                </div>
                <div class="col-sm-3 ps-sm-1 oh-time-wrap" id="{{ $venue_edit }}_oh_{{ $day }}_wrap2">
                    <input type="time" class="form-control" id="{{ $venue_edit }}_oh_{{ $day }}_close">
                    <div class="form-text">Closes</div>
                </div>
            </div>
            @endforeach
        </div>

        <hr class="my-4">

        {{-- ── Opening Hours ─────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Opening Hours &amp; Pricing</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_opening_hours_pricing" class="col-sm-3 col-form-label">Details</label>
            <div class="col-sm-9">
                <textarea id="{{ $venue_edit }}_opening_hours_pricing"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Venue Layout ─────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-1">Venue Layout</h6>
        <p class="text-muted small mb-3">Upload a floor plan or layout image of the venue.</p>

        <div class="mb-3 row" id="{{ $venue_edit }}_current_layout_row" style="display:none;">
            <label class="col-sm-3 col-form-label">Current Layout</label>
            <div class="col-sm-9">
                <img id="{{ $venue_edit }}_layout_preview" src="" class="rounded border mb-2 d-block" style="max-height:200px; max-width:400px;">
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Replace Layout</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="{{ $venue_edit }}_venue_layout" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                <div class="form-text">{{ __( 'template.leave_blank' ) }}</div>
                <div class="invalid-feedback"></div>
                <div class="mt-2" id="{{ $venue_edit }}_new_layout_preview_wrap" style="display:none;">
                    <img id="{{ $venue_edit }}_new_layout_preview" src="" class="rounded border" style="max-height:200px; max-width:400px;">
                    <div class="form-text text-success">New layout selected — will replace current on save.</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Venue Policy ─────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Venue Policy</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_venue_policy" class="col-sm-3 col-form-label">Policy</label>
            <div class="col-sm-9">
                <textarea id="{{ $venue_edit }}_venue_policy"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Navigation & Contact ─────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Navigation &amp; Contact</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_gmap_link" class="col-sm-3 col-form-label">Google Maps Link</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_edit }}_gmap_link">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_waze_link" class="col-sm-3 col-form-label">Waze Link</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_edit }}_waze_link">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Phone Number</label>
            <div class="col-sm-3 pe-sm-1 mb-2 mb-sm-0">
                <select class="form-select" id="{{ $venue_edit }}_calling_code">
                    <option value="+60">+60 Malaysia</option>
                    <option value="+65">+65 Singapore</option>
                    <option value="+62">+62 Indonesia</option>
                    <option value="+66">+66 Thailand</option>
                    <option value="+63">+63 Philippines</option>
                    <option value="+673">+673 Brunei</option>
                    <option value="+84">+84 Vietnam</option>
                    <option value="+95">+95 Myanmar</option>
                    <option value="+855">+855 Cambodia</option>
                    <option value="+856">+856 Laos</option>
                    <option value="+61">+61 Australia</option>
                    <option value="+64">+64 New Zealand</option>
                    <option value="+86">+86 China</option>
                    <option value="+81">+81 Japan</option>
                    <option value="+82">+82 South Korea</option>
                    <option value="+91">+91 India</option>
                    <option value="+44">+44 United Kingdom</option>
                    <option value="+1">+1 USA / Canada</option>
                    <option value="+971">+971 UAE</option>
                    <option value="+966">+966 Saudi Arabia</option>
                    <option value="+880">+880 Bangladesh</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-4 ps-sm-1">
                <input type="text" class="form-control" id="{{ $venue_edit }}_phone_number" maxlength="20">
                <div class="form-text">Number (without code)</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_edit }}_whatsapp_link" class="col-sm-3 col-form-label">WhatsApp Link</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_edit }}_whatsapp_link">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.venue.index' ) }}" class="btn btn-outline-secondary me-1">{{ __( 'template.cancel' ) }}</a>
            <button id="{{ $venue_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>

    </div>
</div>

{{-- ── Venue Sports ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-inner">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="card-title mb-0">Configured Sports</h5>
                <p class="text-muted small mb-0 mt-1">Sports available at this venue. Each sport has its own schedule and pricing.</p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" id="btn_add_venue_sport">
                <em class="icon ni ni-plus"></em> Add Sport
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="venue_sports_table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sport</th>
                        <th>Slot</th>
                        <th>Price / Slot</th>
                        <th>Hours</th>
                        <th>Operating Days</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="venue_sports_tbody">
                    <tr id="venue_sports_empty_row">
                        <td colspan="7" class="text-center text-muted py-3">{{ __( 'datatables.zeroRecords' ) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Add Sport Modal ──────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal_add_venue_sport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sport to Venue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Sport <span class="text-danger">*</span></label>
                    <select class="form-select" id="add_vs_sport_ids">
                        <option value="">— Select Sport —</option>
                        @foreach( $data['sports'] as $sport )
                            <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback d-block" id="add_vs_sport_ids_error"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pricing Method <span class="text-danger">*</span></label>
                    <select class="form-select" id="add_vs_pricing_method">
                        <option value="">— Select Pricing Method —</option>
                        @foreach( \App\Models\Sport::pricingMethodOptions() as $value => $label )
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback d-block" id="add_vs_pricing_method_error"></div>
                </div>

                <div class="row mb-3" id="add_vs_slot_price_row">
                    <div class="col-6" id="add_vs_slot_duration_col">
                        <label class="form-label">Slot Duration <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="add_vs_slot_duration" min="15" step="15" placeholder="60">
                            <span class="input-group-text">min</span>
                        </div>
                        <div class="form-text">Multiples of 15.</div>
                        <div class="invalid-feedback d-block" id="add_vs_slot_duration_error"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label" id="add_vs_price_per_slot_label">Price / Slot <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" id="add_vs_price_per_slot" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="invalid-feedback d-block" id="add_vs_price_per_slot_error"></div>
                    </div>
                </div>

                <div class="mb-3" id="add_vs_price_per_person_row" style="display:none;">
                    <label class="form-label">Price / Person <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" class="form-control" id="add_vs_price_per_person" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="invalid-feedback d-block" id="add_vs_price_per_person_error"></div>
                </div>

                <div class="mb-3" id="add_vs_price_per_night_row" style="display:none;">
                    <label class="form-label">Price / Night <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" class="form-control" id="add_vs_price_per_night" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="invalid-feedback d-block" id="add_vs_price_per_night_error"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Opens <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="add_vs_open_time">
                        <div class="invalid-feedback d-block" id="add_vs_open_time_error"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Closes <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="add_vs_close_time">
                        <div class="invalid-feedback d-block" id="add_vs_close_time_error"></div>
                    </div>
                    <div class="col-12 mt-1">
                        <span class="badge bg-light text-dark border" id="add_vs_slot_count" style="display:none;"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Operating Days <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-sm mb-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="add_vs_days_all">All</button>
                        <button type="button" class="btn btn-outline-secondary" id="add_vs_days_weekdays">Weekdays</button>
                        <button type="button" class="btn btn-outline-secondary" id="add_vs_days_weekends">Weekends</button>
                        <button type="button" class="btn btn-outline-secondary" id="add_vs_days_none">Clear</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $day )
                        <div class="form-check form-check-inline">
                            <input class="form-check-input add_vs_day" type="checkbox" id="add_vs_day_{{ $day }}" value="{{ $day }}">
                            <label class="form-check-label" for="add_vs_day_{{ $day }}">{{ ucfirst( $day ) }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="invalid-feedback d-block" id="add_vs_operating_days_error"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_add_venue_sport_submit">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Sport Modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal_edit_venue_sport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sport — <span id="edit_vs_sport_name" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_vs_id">

                <div class="mb-3">
                    <label class="form-label">Pricing Method <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_vs_pricing_method">
                        <option value="">— Select Pricing Method —</option>
                        @foreach( \App\Models\Sport::pricingMethodOptions() as $value => $label )
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback d-block" id="edit_vs_pricing_method_error"></div>
                </div>

                <div class="row mb-3" id="edit_vs_slot_price_row">
                    <div class="col-6" id="edit_vs_slot_duration_col">
                        <label class="form-label">Slot Duration <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit_vs_slot_duration" min="15" step="15">
                            <span class="input-group-text">min</span>
                        </div>
                        <div class="form-text">Multiples of 15.</div>
                        <div class="invalid-feedback d-block" id="edit_vs_slot_duration_error"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label" id="edit_vs_price_per_slot_label">Price / Slot <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" id="edit_vs_price_per_slot" min="0" step="0.01">
                        </div>
                        <div class="invalid-feedback d-block" id="edit_vs_price_per_slot_error"></div>
                    </div>
                </div>

                <div class="mb-3" id="edit_vs_price_per_person_row" style="display:none;">
                    <label class="form-label">Price / Person <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" class="form-control" id="edit_vs_price_per_person" min="0" step="0.01">
                    </div>
                    <div class="invalid-feedback d-block" id="edit_vs_price_per_person_error"></div>
                </div>

                <div class="mb-3" id="edit_vs_price_per_night_row" style="display:none;">
                    <label class="form-label">Price / Night <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" class="form-control" id="edit_vs_price_per_night" min="0" step="0.01">
                    </div>
                    <div class="invalid-feedback d-block" id="edit_vs_price_per_night_error"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Opens <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="edit_vs_open_time">
                        <div class="invalid-feedback d-block" id="edit_vs_open_time_error"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Closes <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="edit_vs_close_time">
                        <div class="invalid-feedback d-block" id="edit_vs_close_time_error"></div>
                    </div>
                    <div class="col-12 mt-1">
                        <span class="badge bg-light text-dark border" id="edit_vs_slot_count" style="display:none;"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Operating Days <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-sm mb-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="edit_vs_days_all">All</button>
                        <button type="button" class="btn btn-outline-secondary" id="edit_vs_days_weekdays">Weekdays</button>
                        <button type="button" class="btn btn-outline-secondary" id="edit_vs_days_weekends">Weekends</button>
                        <button type="button" class="btn btn-outline-secondary" id="edit_vs_days_none">Clear</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $day )
                        <div class="form-check form-check-inline">
                            <input class="form-check-input edit_vs_day" type="checkbox" id="edit_vs_day_{{ $day }}" value="{{ $day }}">
                            <label class="form-check-label" for="edit_vs_day_{{ $day }}">{{ ucfirst( $day ) }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="invalid-feedback d-block" id="edit_vs_operating_days_error"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_edit_venue_sport_submit">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        const venueId   = '{{ request( 'id' ) }}';
        let ve          = '#{{ $venue_edit }}';
        let modalAddVS  = new bootstrap.Modal( document.getElementById( 'modal_add_venue_sport' ) );
        let modalEditVS = new bootstrap.Modal( document.getElementById( 'modal_edit_venue_sport' ) );

        // ── Day quick-select helpers ───────────────────────────────────────────
        let weekdays = ['monday','tuesday','wednesday','thursday','friday'];
        let weekends = ['saturday','sunday'];
        let allDays  = weekdays.concat( weekends );

        function setDays( prefix, days ) {
            $( '.' + prefix + '_vs_day' ).prop( 'checked', false );
            days.forEach( function( d ) { $( '#' + prefix + '_vs_day_' + d ).prop( 'checked', true ); } );
        }

        // Add modal day buttons
        $( '#add_vs_days_all' ).click( function() { setDays( 'add', allDays ); } );
        $( '#add_vs_days_weekdays' ).click( function() { setDays( 'add', weekdays ); } );
        $( '#add_vs_days_weekends' ).click( function() { setDays( 'add', weekends ); } );
        $( '#add_vs_days_none' ).click( function() { setDays( 'add', [] ); } );

        // Edit modal day buttons
        $( '#edit_vs_days_all' ).click( function() { setDays( 'edit', allDays ); } );
        $( '#edit_vs_days_weekdays' ).click( function() { setDays( 'edit', weekdays ); } );
        $( '#edit_vs_days_weekends' ).click( function() { setDays( 'edit', weekends ); } );
        $( '#edit_vs_days_none' ).click( function() { setDays( 'edit', [] ); } );

        // ── Slot count badges ─────────────────────────────────────────────────
        function updateSlotCount( openId, closeId, durationId, badgeId ) {
            let openVal     = $( openId ).val();
            let closeVal    = $( closeId ).val();
            let durationVal = parseInt( $( durationId ).val() );
            let badge       = $( badgeId );

            if ( openVal && closeVal && durationVal > 0 ) {
                let [oh, om] = openVal.split(':').map( Number );
                let [ch, cm] = closeVal.split(':').map( Number );
                let totalMin  = (ch * 60 + cm) - (oh * 60 + om);
                if ( totalMin > 0 ) {
                    let slots = Math.floor( totalMin / durationVal );
                    badge.text( slots + ' slot' + ( slots !== 1 ? 's' : '' ) + '/day' ).css( 'display', 'inline-block' );
                    return;
                }
            }
            badge.hide();
        }

        $( '#add_vs_open_time, #add_vs_close_time, #add_vs_slot_duration' ).on( 'change input', function() {
            updateSlotCount( '#add_vs_open_time', '#add_vs_close_time', '#add_vs_slot_duration', '#add_vs_slot_count' );
        } );

        $( '#edit_vs_open_time, #edit_vs_close_time, #edit_vs_slot_duration' ).on( 'change input', function() {
            updateSlotCount( '#edit_vs_open_time', '#edit_vs_close_time', '#edit_vs_slot_duration', '#edit_vs_slot_count' );
        } );

        // ── Amenities Select2 ─────────────────────────────────────────────────
        $( ve + '_amenities' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search amenities...',
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.sports_tag.all' ) }}',
                dataType: 'json',
                delay: 250,
                data: function( params ) {
                    return {
                        name:   params.term,
                        start:  0,
                        length: 30,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function( data ) {
                    return {
                        results: data.sports_tags.map( function( tag ) {
                            return { id: tag.id, text: tag.name };
                        } )
                    };
                }
            }
        } );

        // ── Closed-day toggle (opening hours) ────────────────────────────────
        $( '.oh-closed-check' ).on( 'change', function() {
            let day   = $( this ).data( 'day' );
            let pfx   = ve.slice(1);
            let wrap1 = $( '#' + pfx + '_oh_' + day + '_wrap' );
            let wrap2 = $( '#' + pfx + '_oh_' + day + '_wrap2' );
            if ( $( this ).is( ':checked' ) ) {
                wrap1.hide(); wrap2.hide();
            } else {
                wrap1.show(); wrap2.show();
            }
        } );

        // ── Venue layout preview ──────────────────────────────────────────────
        $( ve + '_venue_layout' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( ve + '_new_layout_preview' ).attr( 'src', e.target.result );
                    $( ve + '_new_layout_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( ve + '_new_layout_preview_wrap' ).hide();
            }
        } );

        // ── New image preview ─────────────────────────────────────────────────
        $( ve + '_image' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( ve + '_new_image_preview' ).attr( 'src', e.target.result );
                    $( ve + '_new_image_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( ve + '_new_image_preview_wrap' ).hide();
            }
        } );

        // ── Venue form submit ─────────────────────────────────────────────────
        $( ve + '_submit' ).click( function() {

            resetInputValidation();
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'id',             venueId );
            formData.append( 'name',           $( ve + '_name' ).val() );
            formData.append( 'slug',           $( ve + '_slug' ).val() );
            formData.append( 'description',    $( ve + '_description' ).val() );
            formData.append( 'about_us',               window.editor_venue_about_us ? window.editor_venue_about_us.getData()   : $( ve + '_about_us' ).val() );
            formData.append( 'opening_hours_pricing', window.editor_venue_ohp       ? window.editor_venue_ohp.getData()         : $( ve + '_opening_hours_pricing' ).val() );
            formData.append( 'venue_policy',   window.editor_venue_policy ? window.editor_venue_policy.getData() : $( ve + '_venue_policy' ).val() );
            formData.append( 'gmap_link',      $( ve + '_gmap_link' ).val() );
            formData.append( 'waze_link',      $( ve + '_waze_link' ).val() );
            formData.append( 'calling_code',   $( ve + '_calling_code' ).val() );
            formData.append( 'phone_number',   $( ve + '_phone_number' ).val() );
            formData.append( 'whatsapp_link',  $( ve + '_whatsapp_link' ).val() );
            formData.append( 'address_1',      $( ve + '_address_1' ).val() );
            formData.append( 'address_2',      $( ve + '_address_2' ).val() );
            formData.append( 'city',           $( ve + '_city' ).val() );
            formData.append( 'state',          $( ve + '_state' ).val() );
            formData.append( 'postcode',       $( ve + '_postcode' ).val() );
            formData.append( 'latitude',       $( ve + '_latitude' ).val() );
            formData.append( 'longitude',      $( ve + '_longitude' ).val() );

            // Amenities (Select2)
            let amenities = $( ve + '_amenities' ).val() || [];
            amenities.forEach( function( id ) { formData.append( 'amenities[]', id ); } );

            // Opening hours — build JSON
            let ohDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            let oh = {};
            let pfx = ve.slice(1);
            ohDays.forEach( function( day ) {
                let closed = $( '#' + pfx + '_oh_' + day + '_closed' ).is( ':checked' );
                oh[day] = {
                    closed: closed,
                    open:   closed ? null : $( '#' + pfx + '_oh_' + day + '_open' ).val(),
                    close:  closed ? null : $( '#' + pfx + '_oh_' + day + '_close' ).val(),
                };
            } );
            formData.append( 'opening_hours', JSON.stringify( oh ) );

            let allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            let imageFile = $( ve + '_image' )[0].files[0];
            if ( imageFile ) {
                if ( !allowedTypes.includes( imageFile.type ) ) {
                    $( 'body' ).loading( 'stop' );
                    $( ve + '_image' ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( 'Only image files (JPG, PNG, GIF, WEBP) are allowed.' );
                    return;
                }
                formData.append( 'image', imageFile );
            }

            let layoutFile = $( ve + '_venue_layout' )[0].files[0];
            if ( layoutFile ) {
                if ( !allowedTypes.includes( layoutFile.type ) ) {
                    $( 'body' ).loading( 'stop' );
                    $( ve + '_venue_layout' ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( 'Only image files (JPG, PNG, GIF, WEBP) are allowed.' );
                    return;
                }
                formData.append( 'venue_layout', layoutFile );
            }

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.venue.updateVenue' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( ve + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        // ── Load venue data ───────────────────────────────────────────────────
        getVenue();

        function getVenue() {
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.venue.oneVenue' ) }}',
                type: 'POST',
                data: { 'id': venueId, '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    $( ve + '_name' ).val( response.name );
                    $( ve + '_slug' ).val( response.slug );
                    $( ve + '_description' ).val( response.description );
                    if ( window.editor_venue_about_us ) { window.editor_venue_about_us.setData( response.about_us || '' ); } else { $( ve + '_about_us' ).val( response.about_us ); }
                    if ( window.editor_venue_ohp )       { window.editor_venue_ohp.setData( response.opening_hours_pricing || '' ); } else { $( ve + '_opening_hours_pricing' ).val( response.opening_hours_pricing ); }
                    if ( window.editor_venue_policy ) { window.editor_venue_policy.setData( response.venue_policy || '' ); } else { $( ve + '_venue_policy' ).val( response.venue_policy ); }
                    $( ve + '_gmap_link' ).val( response.gmap_link );
                    $( ve + '_waze_link' ).val( response.waze_link );
                    $( ve + '_calling_code' ).val( response.calling_code );
                    $( ve + '_phone_number' ).val( response.phone_number );
                    $( ve + '_whatsapp_link' ).val( response.whatsapp_link );
                    $( ve + '_address_1' ).val( response.address_1 );
                    $( ve + '_address_2' ).val( response.address_2 );
                    $( ve + '_city' ).val( response.city );
                    $( ve + '_state' ).val( response.state );
                    $( ve + '_postcode' ).val( response.postcode );
                    $( ve + '_latitude' ).val( response.latitude );
                    $( ve + '_longitude' ).val( response.longitude );

                    // Amenities — pre-populate Select2 from amenity_details
                    let amenitySelect = $( ve + '_amenities' );
                    amenitySelect.find( 'option' ).remove();
                    let amenityDetails = response.amenity_details || [];
                    amenityDetails.forEach( function( tag ) {
                        let opt = new Option( tag.name, tag.id, true, true );
                        amenitySelect.append( opt );
                    } );
                    amenitySelect.trigger( 'change' );

                    // Opening hours
                    let ohDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                    let ohData = response.opening_hours || {};
                    let pfx = ve.slice(1);
                    ohDays.forEach( function( day ) {
                        let dayData = ohData[day] || {};
                        let closed  = !!dayData.closed;
                        $( '#' + pfx + '_oh_' + day + '_closed' ).prop( 'checked', closed );
                        if ( closed ) {
                            $( '#' + pfx + '_oh_' + day + '_wrap' ).hide();
                            $( '#' + pfx + '_oh_' + day + '_wrap2' ).hide();
                        } else {
                            $( '#' + pfx + '_oh_' + day + '_open' ).val( dayData.open || '' );
                            $( '#' + pfx + '_oh_' + day + '_close' ).val( dayData.close || '' );
                        }
                    } );

                    if ( response.image ) {
                        $( ve + '_image_preview' ).attr( 'src', '{{ asset( 'storage' ) }}/' + response.image );
                        $( ve + '_current_image_row' ).show();
                    }

                    if ( response.venue_layout ) {
                        $( ve + '_layout_preview' ).attr( 'src', '{{ asset( 'storage' ) }}/' + response.venue_layout );
                        $( ve + '_current_layout_row' ).show();
                    }

                    $( 'body' ).loading( 'stop' );
                    loadVenueSports();
                },
            } );
        }

        // ── Venue Sports table ────────────────────────────────────────────────
        function loadVenueSports() {
            $.ajax( {
                url: '{{ route( 'admin.venue.allVenueSports' ) }}',
                type: 'POST',
                data: { 'venue_id': venueId, '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    let tbody = $( '#venue_sports_tbody' );
                    tbody.empty();

                    if ( !response.venue_sports || response.venue_sports.length === 0 ) {
                        tbody.html( '<tr><td colspan="7" class="text-center text-muted py-3">{{ __( 'datatables.zeroRecords' ) }}</td></tr>' );
                        return;
                    }

                    $.each( response.venue_sports, function( i, vs ) {
                        let days = Array.isArray( vs.operating_days )
                            ? vs.operating_days.map( d => d.charAt(0).toUpperCase() + d.slice(1,3) ).join( ', ' )
                            : '-';
                        let row = `<tr data-id="${vs.encrypted_id}">
                            <td>${ i + 1 }</td>
                            <td><strong>${ vs.sport ? vs.sport.name : '-' }</strong></td>
                            <td>${ vs.slot_duration } min</td>
                            <td>RM ${ parseFloat( vs.price_per_slot ).toFixed(2) }</td>
                            <td>${ vs.open_time ? vs.open_time.substring(0,5) : '-' } – ${ vs.close_time ? vs.close_time.substring(0,5) : '-' }</td>
                            <td>${ days }</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning btn-edit-vs me-1"
                                    data-id="${ vs.encrypted_id }"
                                    data-sport="${ vs.sport ? vs.sport.name : '' }"
                                    data-pricing-method="${ vs.pricing_method ?? '' }"
                                    data-slot="${ vs.slot_duration }"
                                    data-price="${ vs.price_per_slot }"
                                    data-price-person="${ vs.price_per_person ?? '' }"
                                    data-price-night="${ vs.price_per_night ?? '' }"
                                    data-open="${ vs.open_time ? vs.open_time.substring(0,5) : '' }"
                                    data-close="${ vs.close_time ? vs.close_time.substring(0,5) : '' }"
                                    data-days='${ JSON.stringify( vs.operating_days ) }'>
                                    <em class="icon ni ni-edit"></em>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-remove-vs" data-id="${ vs.encrypted_id }">
                                    <em class="icon ni ni-trash"></em>
                                </button>
                            </td>
                        </tr>`;
                        tbody.append( row );
                    } );
                },
            } );
        }

        // ── Add Venue Sport ───────────────────────────────────────────────────
        $( '#btn_add_venue_sport' ).click( function() {
            $( '#add_vs_sport_ids, #add_vs_pricing_method, #add_vs_slot_duration, #add_vs_price_per_slot, #add_vs_price_per_person, #add_vs_price_per_night, #add_vs_open_time, #add_vs_close_time' ).val( '' ).removeClass( 'is-invalid' );
            $( '.add_vs_day' ).prop( 'checked', false );
            $( '[id^="add_vs_"][id$="_error"]' ).text( '' );
            $( '#add_vs_slot_count' ).hide();
            $( '#add_vs_slot_price_row' ).show();
            $( '#add_vs_slot_duration_col' ).show();
            $( '#add_vs_price_per_slot_label' ).html( 'Price / Slot <span class="text-danger">*</span>' );
            $( '#add_vs_price_per_person_row, #add_vs_price_per_night_row' ).hide();
            modalAddVS.show();
        } );

        $( '#btn_add_venue_sport_submit' ).click( function() {
            $( '#add_vs_sport_ids, #add_vs_pricing_method, #add_vs_slot_duration, #add_vs_price_per_slot, #add_vs_price_per_person, #add_vs_price_per_night, #add_vs_open_time, #add_vs_close_time' ).removeClass( 'is-invalid' );
            $( '[id^="add_vs_"][id$="_error"]' ).text( '' );

            let days = [];
            $( '.add_vs_day:checked' ).each( function() { days.push( $( this ).val() ); } );

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.venue.addVenueSport' ) }}',
                type: 'POST',
                data: {
                    'venue_id':         venueId,
                    'sport_ids':        [ $( '#add_vs_sport_ids' ).val() ],
                    'pricing_method':   $( '#add_vs_pricing_method' ).val(),
                    'slot_duration':    $( '#add_vs_slot_duration' ).val(),
                    'price_per_slot':   $( '#add_vs_price_per_slot' ).val(),
                    'price_per_person': $( '#add_vs_price_per_person' ).val(),
                    'price_per_night':  $( '#add_vs_price_per_night' ).val(),
                    'open_time':        $( '#add_vs_open_time' ).val(),
                    'close_time':       $( '#add_vs_close_time' ).val(),
                    'operating_days':   days,
                    '_token':           '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    modalAddVS.hide();
                    loadVenueSports();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            let n = key.replace( /\.\*$/, '' );
                            $( '#add_vs_' + n ).addClass( 'is-invalid' );
                            $( '#add_vs_' + n + '_error' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        // ── Pricing Method — show/hide price fields ────────────────────────────
        $( '#add_vs_pricing_method' ).on( 'change', function() {
            let pm = $( this ).val();
            $( '#add_vs_slot_price_row' ).show();
            $( '#add_vs_slot_duration_col' ).show();
            $( '#add_vs_price_per_person_row, #add_vs_price_per_night_row' ).hide();
            $( '#add_vs_price_per_slot, #add_vs_price_per_person, #add_vs_price_per_night' ).val( '' );

            if ( pm === 'per_person' ) {
                $( '#add_vs_slot_price_row' ).hide();
                $( '#add_vs_price_per_person_row' ).show();
            } else if ( pm === 'per_night' ) {
                $( '#add_vs_slot_price_row' ).hide();
                $( '#add_vs_price_per_night_row' ).show();
            } else if ( pm === 'per_hour' ) {
                $( '#add_vs_slot_duration_col' ).hide();
                $( '#add_vs_price_per_slot_label' ).html( 'Price / Hour <span class="text-danger">*</span>' );
            } else {
                $( '#add_vs_price_per_slot_label' ).html( 'Price / Slot <span class="text-danger">*</span>' );
            }
        } );

        $( '#edit_vs_pricing_method' ).on( 'change', function() {
            let pm = $( this ).val();
            $( '#edit_vs_slot_price_row' ).show();
            $( '#edit_vs_slot_duration_col' ).show();
            $( '#edit_vs_price_per_person_row, #edit_vs_price_per_night_row' ).hide();

            if ( pm === 'per_person' ) {
                $( '#edit_vs_slot_price_row' ).hide();
                $( '#edit_vs_price_per_person_row' ).show();
            } else if ( pm === 'per_night' ) {
                $( '#edit_vs_slot_price_row' ).hide();
                $( '#edit_vs_price_per_night_row' ).show();
            } else if ( pm === 'per_hour' ) {
                $( '#edit_vs_slot_duration_col' ).hide();
                $( '#edit_vs_price_per_slot_label' ).html( 'Price / Hour <span class="text-danger">*</span>' );
            } else {
                $( '#edit_vs_price_per_slot_label' ).html( 'Price / Slot <span class="text-danger">*</span>' );
            }
        } );

        // ── Edit Venue Sport ──────────────────────────────────────────────────
        $( document ).on( 'click', '.btn-edit-vs', function() {
            let btn = $( this );
            let days = btn.data( 'days' ) || [];
            let pm   = btn.data( 'pricing-method' ) || '';

            $( '#edit_vs_id' ).val( btn.data( 'id' ) );
            $( '#edit_vs_sport_name' ).text( btn.data( 'sport' ) );
            $( '#edit_vs_pricing_method' ).val( pm );
            $( '#edit_vs_slot_duration' ).val( btn.data( 'slot' ) );
            $( '#edit_vs_price_per_slot' ).val( btn.data( 'price' ) );
            $( '#edit_vs_price_per_person' ).val( btn.data( 'price-person' ) );
            $( '#edit_vs_price_per_night' ).val( btn.data( 'price-night' ) );
            $( '#edit_vs_open_time' ).val( btn.data( 'open' ) );
            $( '#edit_vs_close_time' ).val( btn.data( 'close' ) );

            $( '.edit_vs_day' ).prop( 'checked', false );
            $.each( days, function( i, d ) { $( '#edit_vs_day_' + d ).prop( 'checked', true ); } );

            $( '#edit_vs_pricing_method, #edit_vs_slot_duration, #edit_vs_price_per_slot, #edit_vs_price_per_person, #edit_vs_price_per_night, #edit_vs_open_time, #edit_vs_close_time' ).removeClass( 'is-invalid' );
            $( '[id^="edit_vs_"][id$="_error"]' ).text( '' );

            // apply show/hide for pricing method
            $( '#edit_vs_pricing_method' ).trigger( 'change' );

            updateSlotCount( '#edit_vs_open_time', '#edit_vs_close_time', '#edit_vs_slot_duration', '#edit_vs_slot_count' );

            modalEditVS.show();
        } );

        $( '#btn_edit_venue_sport_submit' ).click( function() {
            $( '#edit_vs_pricing_method, #edit_vs_slot_duration, #edit_vs_price_per_slot, #edit_vs_price_per_person, #edit_vs_price_per_night, #edit_vs_open_time, #edit_vs_close_time' ).removeClass( 'is-invalid' );
            $( '[id^="edit_vs_"][id$="_error"]' ).text( '' );

            let days = [];
            $( '.edit_vs_day:checked' ).each( function() { days.push( $( this ).val() ); } );

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.venue.updateVenueSport' ) }}',
                type: 'POST',
                data: {
                    'id':               $( '#edit_vs_id' ).val(),
                    'pricing_method':   $( '#edit_vs_pricing_method' ).val(),
                    'slot_duration':    $( '#edit_vs_slot_duration' ).val(),
                    'price_per_slot':   $( '#edit_vs_price_per_slot' ).val(),
                    'price_per_person': $( '#edit_vs_price_per_person' ).val(),
                    'price_per_night':  $( '#edit_vs_price_per_night' ).val(),
                    'open_time':        $( '#edit_vs_open_time' ).val(),
                    'close_time':       $( '#edit_vs_close_time' ).val(),
                    'operating_days':   days,
                    '_token':           '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    modalEditVS.hide();
                    loadVenueSports();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            let n = key.replace( /\.\*$/, '' );
                            $( '#edit_vs_' + n ).addClass( 'is-invalid' );
                            $( '#edit_vs_' + n + '_error' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        // ── Remove Venue Sport ────────────────────────────────────────────────
        $( document ).on( 'click', '.btn-remove-vs', function() {
            if ( !confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) return;

            let id = $( this ).data( 'id' );
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.venue.removeVenueSport' ) }}',
                type: 'POST',
                data: { 'id': id, '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    loadVenueSports();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            } );
        } );

    } );
</script>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script>
document.addEventListener( 'DOMContentLoaded', function() {
    ClassicEditor.create( document.getElementById( '{{ $venue_edit }}_about_us' ) )
        .then( function( e ) { window.editor_venue_about_us = e; } )
        .catch( function( e ) { console.error( e ); } );

    ClassicEditor.create( document.getElementById( '{{ $venue_edit }}_opening_hours_pricing' ) )
        .then( function( e ) { window.editor_venue_ohp = e; } )
        .catch( function( e ) { console.error( e ); } );

    ClassicEditor.create( document.getElementById( '{{ $venue_edit }}_venue_policy' ) )
        .then( function( e ) { window.editor_venue_policy = e; } )
        .catch( function( e ) { console.error( e ); } );
} );
</script>
