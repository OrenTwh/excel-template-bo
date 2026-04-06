<?php

namespace App\Services;

use App\Models\TenancyAgreement;
use App\Models\TenancyAgreementVersion;
use App\Models\TenancyAgreementSection;
use App\Models\TenancyTemplate;
use App\Models\PropertyRental;
use App\Models\PropertyShortRental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Helper;

class TenancyAgreementService
{
    /**
     * Get all tenancy agreements with filters for DataTables
     */
    public static function allTenancyAgreements($request)
    {
        $model = TenancyAgreement::with([
            'creator',
            'rental.property',
            'rental.propertyUnit',
            'rental.user',
            'shortRental',
            'template',
            'latestVersion'
        ]);

        // Apply filters
        list($model, $filtered) = self::filter($request, $model);

        // Get total records
        $recordsFiltered = $model->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $agreements = $model->skip($start)->take($length)->get();

        // Format data
        $data = $agreements->map(function ($agreement) {
            return [
                'encrypted_id' => $agreement->encrypted_id,
                'agreement_number' => $agreement->agreement_number,
                'template_name' => $agreement->template ? $agreement->template->name : '-',
                'rental_info' => self::getRentalInfo($agreement),
                'version' => $agreement->latestVersion ? $agreement->latestVersion->version : 1,
                'created_at' => $agreement->created_at->format('Y-m-d H:i'),
                'status' => $agreement->status,
            ];
        });

        return response()->json([
            'agreements' => $data,
            'recordsTotal' => TenancyAgreement::count(),
            'recordsFiltered' => $recordsFiltered,
        ]);
    }

    /**
     * Get rental info for display
     */
    private static function getRentalInfo($agreement)
    {
        if ($agreement->property_rental_id && $agreement->rental) {
            $rental = $agreement->rental;
            return [
                'type' => 'Long Rental',
                'property' => $rental->property ? $rental->property->property_name . '(' . $rental->propertyUnit->unit_number . ')' : '-',
                'tenant' => $rental->user->email,
            ];
        }

        return [
            'type' => '-',
            'property' => '-',
            'tenant' => '-',
        ];
    }

    /**
     * Get single tenancy agreement with latest version and sections
     */
    public static function oneTenancyAgreement($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agreement = TenancyAgreement::with([
            'creator',
            'template',
            'rental.property',
            'rental.user',
            'rental.propertyUnit',
            'shortRental.property',
            'latestVersion.sections'
        ])->where('id', Helper::decode( $request->id ))->first();

        if (!$agreement) {
            return response()->json(['message' => __('tenancy_agreement.not_found')], 404);
        }

        $sections = $agreement->latestVersion && $agreement->latestVersion->sections
            ? $agreement->latestVersion->sections->sortBy('sort_order')->map(function ($section) {
                return [
                    'encrypted_id' => $section->encrypted_id,
                    'section_key' => $section->section_key,
                    'title' => $section->title,
                    'content' => $section->content,
                    'sort_order' => $section->sort_order,
                    'status' => $section->status,
                ];
            })->values()
            : collect([]);

        // Get tenant info with email
        $tenantInfo = '-';
        if ($agreement->rental) {
            $name = $agreement->rental->name ?? $agreement->rental->tenant_name ?? '';
            $email = $agreement->rental->email ?? $agreement->rental->tenant_email ?? '';
            $tenantInfo = $name . ($email ? ' (' . $email . ')' : '');
        } elseif ($agreement->shortRental) {
            $name = $agreement->shortRental->name ?? $agreement->shortRental->tenant_name ?? '';
            $email = $agreement->shortRental->email ?? $agreement->shortRental->tenant_email ?? '';
            $tenantInfo = $name . ($email ? ' (' . $email . ')' : '');
        }

        // Determine current version
        $currentVersionNumber = $agreement->latestVersion ? $agreement->latestVersion->version_number : 1;

        return response()->json([
            'agreement' => [
                'encrypted_id' => $agreement->encrypted_id,
                'agreement_number' => $agreement->agreement_number,
                'tenancy_template_id' => $agreement->tenancy_template_id,
                'template_name' => $agreement->template ? $agreement->template->name : '-',
                'rental_type' => 'long',
                'rental_price' => $agreement->rental->propertyUnit ? Helper::numberFormatV2( ( $agreement->rental->propertyUnit->rental_price ), 2, true ) : '-',
                'tenant_name' => $tenantInfo,
                'property_rental_id' => $agreement->property_rental_id,
                'status' => $agreement->status,
                'current_version' => $currentVersionNumber,
                'created_at' => $agreement->created_at->format('Y-m-d H:i'),
                'versions' => $agreement->versions->sortByDesc('version_number')->map(function ($version) use ($currentVersionNumber) {
                    return [
                        'version' => $version->version_number,
                        'created_at' => $version->created_at->format('Y-m-d H:i'),
                        'is_current' => $version->version_number == $currentVersionNumber,
                    ];
                })->values(),
            ],
            'sections' => $sections,
            'template' => $agreement->template,
            'rental' => $agreement->rental,
            'short_rental' => $agreement->shortRental,
        ]);
    }

    /**
     * Create new tenancy agreement from template
     */
    public static function createTenancyAgreement($request)
    {
        $validator = Validator::make($request->all(), [
            'tenancy_template_id' => 'nullable|exists:tenancy_templates,id',
            'rental_id' => 'required',
            'status' => 'required|in:1,10,20,21',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Load template with sections
            $template = TenancyTemplate::with('sections')->find($request->tenancy_template_id);
            // if (!$template) {
            //     return response()->json(['message' => __('tenancy_template.not_found')], 404);
            // }

            // Validate rental exists
            if ($request->rental_type === 'long') {
                $rental = PropertyRental::find($request->rental_id);
                if (!$rental) {
                    return response()->json(['message' => __('property_rental.not_found')], 404);
                }
            } else {
                $rental = PropertyShortRental::find($request->rental_id);
                if (!$rental) {
                    return response()->json(['message' => __('property_short_rental.not_found')], 404);
                }
            }

            // Generate agreement number
            $agreementNumber = self::generateAgreementNumber();

            // Create agreement
            $agreement = TenancyAgreement::create([
                'agreement_number' => $agreementNumber,
                'tenancy_template_id' => $template ? $template->id : null,
                'property_rental_id' => $request->rental_id,
                // 'property_short_rental_id' => $request->rental_type === 'short' ? $request->rental_id : null,
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            // Create first version
            $version = TenancyAgreementVersion::create([
                'tenancy_agreement_id' => $agreement->id,
                'version' => 1,
                'created_by' => Auth::id(),
            ]);

            // Get placeholder data
            $placeholderData = self::getPlaceholderData($rental, $request->rental_type, $agreement);

            // Create sections with replaced placeholders
            if( $template ){
                foreach ($template->sections as $templateSection) {
                    // $content = self::replacePlaceholders($templateSection->content, $placeholderData);
                    // $title = self::replacePlaceholders($templateSection->title, $placeholderData);
                    $content = $templateSection->content;
                    $title = $templateSection->title;
    
                    TenancyAgreementSection::create([
                        'tenancy_agreement_version_id' => $version->id,
                        'section_key' => $templateSection->section_key,
                        'title' => $title,
                        'content' => $content,
                        'sort_order' => $templateSection->sort_order,
                        'status' => $templateSection->status,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => __('template.new_x_created', ['title' => __('template.tenancy_agreement')]),
                'agreement_id' => $agreement->encrypted_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update tenancy agreement (creates new version)
     */
    public static function updateTenancyAgreement($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'tenancy_template_id' => 'nullable|exists:tenancy_templates,id',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'required|string',
            'status' => 'required|in:1,10,20,21',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $agreement = TenancyAgreement::where('id', Helper::decode($request->id))->first();
            if (!$agreement) {
                return response()->json(['message' => __('tenancy_agreement.not_found')], 404);
            }

            // Update agreement
            $updateData = [
                'status' => $request->status,
                'last_edited_by' => Auth::id(),
            ];

            if ($request->filled('tenancy_template_id')) {
                $updateData['tenancy_template_id'] = $request->tenancy_template_id;
            }

            $agreement->update($updateData);

            // Get latest version number
            $latestVersion = $agreement->versions()->latest('version_number')->first();
            $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

            // Create new version
            $version = TenancyAgreementVersion::create([
                'tenancy_agreement_id' => $agreement->id,
                'version_number' => $newVersionNumber,
                'created_by' => Auth::id(),
            ]);

            // Create sections for new version
            foreach ($request->sections as $index => $sectionData) {
                TenancyAgreementSection::create([
                    'tenancy_agreement_version_id' => $version->id,
                    'section_key' => $sectionData['section_key'] ?? null,
                    'title' => $sectionData['title'],
                    'content' => $sectionData['content'],
                    'sort_order' => $sectionData['sort_order'] ?? ($index + 1),
                    'status' => $sectionData['status'] ?? 10,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => __('template.x_updated', ['title' => __('template.tenancy_agreement')]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete tenancy agreement
     */
    public static function deleteTenancyAgreement($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agreement = TenancyAgreement::where('encrypted_id', $request->id)->first();
        if (!$agreement) {
            return response()->json(['message' => __('tenancy_agreement.not_found')], 404);
        }

        $agreement->delete();

        return response()->json([
            'message' => __('template.x_deleted', ['title' => __('template.tenancy_agreement')]),
        ]);
    }

    /**
     * Get sections from specific version
     */
    public static function getVersionSections($request)
    {
        $validator = Validator::make($request->all(), [
            'agreement_id' => 'required',
            'version_number' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agreement = TenancyAgreement::where('id', Helper::decode($request->agreement_id))->first();
        if (!$agreement) {
            return response()->json(['message' => __('tenancy_agreement.not_found')], 404);
        }

        $version = TenancyAgreementVersion::where('tenancy_agreement_id', $agreement->id)
            ->where('version_number', $request->version_number)
            ->with('sections')
            ->first();

        if (!$version) {
            return response()->json(['message' => __('tenancy_agreement.version_not_found')], 404);
        }

        $sections = $version->sections->sortBy('sort_order')->map(function ($section) {
            return [
                'encrypted_id' => $section->encrypted_id,
                'section_key' => $section->section_key,
                'title' => $section->title,
                'content' => $section->content,
                'sort_order' => $section->sort_order,
                'status' => $section->status,
            ];
        })->values();

        return response()->json([
            'sections' => $sections,
            'version_number' => $version->version_number,
        ]);
    }

    /**
     * Update tenancy agreement status
     */
    public static function updateTenancyAgreementStatus($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|in:1,10,20,21',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agreement = TenancyAgreement::where('encrypted_id', $request->id)->first();
        if (!$agreement) {
            return response()->json(['message' => __('tenancy_agreement.not_found')], 404);
        }

        $agreement->update(['status' => $request->status]);

        return response()->json([
            'message' => __('template.x_updated', ['title' => __('template.tenancy_agreement')]),
        ]);
    }

    /**
     * Generate unique agreement number
     */
    private static function generateAgreementNumber()
    {
        $prefix = 'TA';
        $date = date('Ymd');
        $count = TenancyAgreement::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    /**
     * Get placeholder data from rental
     */
    private static function getPlaceholderData($rental, $rentalType, $agreement)
    {
        $property = $rental->property;
        $data = [
            // Tenant Information
            'tenant_fullname' => $rental->name ?? '-',
            'tenant_email' => $rental->email ?? '-',
            'tenant_phone' => $rental->phone ?? '-',
            'tenant_id_number' => $rental->id_number ?? '-',
            'tenant_address' => $rental->address ?? '-',

            // Property Information
            'property_name' => $property->name ?? '-',
            'property_address' => $property->address ?? '-',
            'property_unit_number' => $property->unit_number ?? '-',
            'property_block_number' => $property->block_number ?? '-',
            'property_floor' => $property->floor ?? '-',
            'property_bedrooms' => $property->bedroom ?? '-',
            'property_bathrooms' => $property->bathroom ?? '-',
            'property_built_up_area' => $property->built_up_area ?? '-',

            // Rental Details
            'rental_price' => number_format($rental->rental_price ?? 0, 2),
            'rental_deposit' => number_format($rental->deposit ?? 0, 2),
            'rental_duration' => $rental->duration ?? '-',
            'rental_start_date' => $rental->start_date ?? '-',
            'rental_end_date' => $rental->end_date ?? '-',
            'maintenance_fee' => number_format($rental->maintenance_fee ?? 0, 2),

            // Owner/Agent Information
            'owner_name' => $property->owner->name ?? '-',
            'owner_email' => $property->owner->email ?? '-',
            'owner_phone' => $property->owner->phone ?? '-',
            'agent_name' => $rental->agent->name ?? '-',
            'agent_email' => $rental->agent->email ?? '-',
            'agent_phone' => $rental->agent->phone ?? '-',
            'company_name' => config('app.name', '-'),

            // Date & Time
            'current_date' => date('Y-m-d'),
            'current_datetime' => date('Y-m-d H:i:s'),
            'current_year' => date('Y'),

            // Agreement Details
            // 'agreement_number' => $agreement->agreement_number,
            'agreement_date' => date('Y-m-d'),
            'agreement_version' => '1',
        ];

        return $data;
    }

    /**
     * Replace placeholders in content
     */
    private static function replacePlaceholders($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Filter agreements
     */
    private static function filter($request, $model)
    {
        $filtered = false;

        // Filter by agreement number
        if ($request->filled('agreement_number')) {
            $model = $model->where('agreement_number', 'like', '%' . $request->agreement_number . '%');
            $filtered = true;
        }

        // Filter by status
        if ($request->filled('status')) {
            $model = $model->where('status', $request->status);
            $filtered = true;
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $model = $model->where('tenancy_template_id', $request->template_id);
            $filtered = true;
        }

        // Filter by template name
        if ($request->filled('template_name')) {
            $templateName = $request->template_name;
            $model = $model->whereHas('template', function ($query) use ($templateName) {
                $query->where('name', 'like', '%' . $templateName . '%');
            });
            $filtered = true;
        }

        // Filter by property
        if ($request->filled('property')) {
            $property = $request->property;
            $model = $model->where(function ($query) use ($property) {
                $query->whereHas('rental.property', function ($q) use ($property) {
                    $q->where('property_name', 'like', '%' . $property . '%');
                })->orWhereHas('rental.propertyUnit', function ($q) use ($property) {
                    $q->where('unit_number', 'like', '%' . $property . '%');
                });
            });
            $filtered = true;
        }

        // Filter by tenant name
        if ($request->filled('tenant_name')) {
            $tenantName = $request->tenant_name;
            $model = $model->whereHas('rental.user', function ($query) use ($tenantName) {
                $query->where('email', 'like', '%' . $tenantName . '%')
                      ->orWhere('fullname', 'like', '%' . $tenantName . '%');
            });
            $filtered = true;
        }

        // Custom search
        if ($request->filled('custom_search')) {
            $search = $request->custom_search;
            $model = $model->where(function ($query) use ($search) {
                $query->where('agreement_number', 'like', '%' . $search . '%')
                    ->orWhereHas('template', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('rental.property', function ($q) use ($search) {
                        $q->where('property_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('rental.propertyUnit', function ($q) use ($search) {
                        $q->where('unit_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('rental.user', function ($q) use ($search) {
                        $q->where('email', 'like', '%' . $search . '%')
                          ->orWhere('fullname', 'like', '%' . $search . '%');
                    });
            });
            $filtered = true;
        }

        // Order by
        $model = $model->orderBy('created_at', 'desc');

        return [$model, $filtered];
    }

    /**
     * Download tenancy agreement as PDF
     */
    public static function downloadPdf($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'version' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $agreement = TenancyAgreement::with([
            'template',
            'rental.property',
            'rental.propertyUnit',
            'rental.user',
            'rental.agent',
            'shortRental.property',
            'shortRental.propertyUnit',
            'shortRental.user',
            'latestVersion.sections',
            'versions.sections'
        ])->where('id', Helper::decode($request->id))->first();

        if (!$agreement) {
            abort(404, __('tenancy_agreement.not_found'));
        }

        // Get rental data
        $rental = $agreement->rental ?? $agreement->shortRental;
        $property = $rental?->property;
        $propertyUnit = $rental?->propertyUnit ?? $rental?->property_unit;

        // Determine which version to export
        $requestedVersion = $request->version;
        $targetVersion = null;

        if ($requestedVersion) {
            // Find the requested version
            $targetVersion = $agreement->versions()
                ->where('version_number', $requestedVersion)
                ->with('sections')
                ->first();

            if (!$targetVersion) {
                abort(404, __('tenancy_agreement.version_not_found'));
            }
        } else {
            // Use latest version if no version specified
            $targetVersion = $agreement->latestVersion;
        }

        // Get version number
        $currentVersion = $targetVersion ? $targetVersion->version_number : 1;

        // Build placeholder data
        $placeholderData = self::buildPlaceholderData($agreement, $rental, $property, $propertyUnit, $currentVersion);

        // Get sections from target version and replace placeholders
        $sections = collect([]);
        if ($targetVersion && $targetVersion->sections) {
            $sections = $targetVersion->sections->sortBy('sort_order')->map(function ($section) use ($placeholderData) {
                return (object) [
                    'title' => self::replacePlaceholders($section->title, $placeholderData),
                    'content' => self::replacePlaceholders($section->content, $placeholderData),
                ];
            });
        }

        // Prepare data for PDF
        $data = [
            'agreement' => $agreement,
            'sections' => $sections,
            'tenant_name' => $rental?->fullname ?? '-',
            'tenant_email' => $rental?->email ?? '-',
            'tenant_phone' => ($rental?->calling_code && $rental?->phone_number)
                ? $rental->calling_code . ' ' . $rental->phone_number
                : '-',
            'tenant_id' => $rental?->identification_number ?? '-',
            'property_name' => $property?->property_name ?? '-',
            'property_address' => self::buildPropertyAddress($property),
            'property_unit' => $propertyUnit?->unit_number ?? '-',
            'rental_price' => $propertyUnit?->rental_price ? Helper::numberFormatV2($propertyUnit->rental_price, 2, true) : '-',
            'rental_deposit' => '0',
            'start_date' => $rental?->start_date ?? '-',
            'end_date' => $rental?->end_date ?? '-',
            'owner_name' => 'Xpark creation',
            'current_version' => $currentVersion,
            'created_date' => $agreement->created_at->format('Y-m-d'),
        ];

        // Load PDF view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.tenancy_agreement.pdf', $data);

        // Set paper size
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = 'Tenancy_Agreement_' . $agreement->agreement_number . '_v' . $currentVersion . '.pdf';

        // Download PDF
        return $pdf->download($filename);
    }

    /**
     * Build placeholder data for replacement
     */
    private static function buildPlaceholderData($agreement, $rental, $property, $propertyUnit, $currentVersion)
    {
        return [
            // Tenant Placeholders
            'tenant_fullname' => $rental?->fullname ?? '-',
            'tenant_email' => $rental?->email ?? '-',
            'tenant_phone' => ($rental?->calling_code && $rental?->phone_number)
                ? $rental->calling_code . ' ' . $rental->phone_number
                : '-',
            'tenant_id_number' => $rental?->identification_number ?? '-',
            'tenant_address' => '-',

            // Property Placeholders
            'property_name' => $property?->property_name ?? '-',
            'property_address' => self::buildPropertyAddress($property),
            'property_unit_number' => $propertyUnit?->unit_number ?? '-',
            'property_block_number' => $propertyUnit?->block_name ?? '-',
            'property_floor' => $propertyUnit?->floor ?? '-',
            'property_bedrooms' => $propertyUnit?->bedrooms ?? '-',
            'property_bathrooms' => $propertyUnit?->bathrooms ?? '-',
            'property_built_up_area' => $propertyUnit?->built_up_area ?? '-',

            // Rental Placeholders
            'rental_price' => $propertyUnit?->rental_price ?? '0',
            'rental_deposit' => '0',
            'rental_duration' => $rental?->rental_duration ?? '-',
            'rental_start_date' => $rental?->start_date ?? '-',
            'rental_end_date' => $rental?->end_date ?? '-',
            'maintenance_fee' => $propertyUnit?->maintenance_fee ?? '0',

            // Owner/Agent Placeholders
            'owner_name' => 'Xpark creation',
            'owner_email' => 'xparkcreation@mail.com',
            'owner_phone' => '-',
            'agent_name' => $rental?->agent?->fullname ?? '-',
            'agent_email' => $rental?->agent?->email ?? '-',
            'agent_phone' => ($rental?->agent?->calling_code && $rental?->agent?->phone_number)
                ? $rental->agent->calling_code . ' ' . $rental->agent->phone_number
                : '-',
            'company_name' => 'Xpark creation',

            // Date/Time Placeholders
            'current_date' => date('Y-m-d'),
            'current_datetime' => date('Y-m-d H:i:s'),
            'current_year' => date('Y'),

            // Agreement Placeholders
            'agreement_number' => $agreement?->agreement_number ?? '-',
            'agreement_date' => $agreement?->start_date ?? date('Y-m-d'),
            'agreement_version' => $currentVersion,
        ];
    }

    /**
     * Build property address from address lines
     */
    private static function buildPropertyAddress($property)
    {
        if (!$property) return '-';

        $addressParts = array_filter([
            $property->address_line_1 ?? '',
            $property->address_line_2 ?? '',
            $property->address_line_3 ?? '',
        ]);

        return !empty($addressParts) ? implode(', ', $addressParts) : '-';
    }
}
