<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy Agreement Language Lines
    |--------------------------------------------------------------------------
    */

    'id' => 'ID',
    'agreement_number' => 'Agreement Number',
    'template' => 'Template',
    'rental_type' => 'Rental Type',
    'rental' => 'Rental',
    'version' => 'Version',
    'status' => 'Status',
    'created_by' => 'Created By',
    'created_at' => 'Created At',
    'not_found' => 'Agreement not found',

    // Rental types
    'long_rental' => 'Long Rental',
    'short_rental' => 'Short Rental',

    // Form fields
    'select_template' => 'Select Template',
    'select_rental_type' => 'Select Rental Type',
    'select_rental' => 'Select Rental',
    'property' => 'Property',
    'tenant' => 'Tenant',
    'tenant_info' => 'Tenant Information',
    'property_info' => 'Property Information',
    'rental_info' => 'Rental Information',
    'agreement_info' => 'Agreement Information',

    // Messages
    'select_template_first' => 'Please select a template first',
    'select_rental_type_first' => 'Please select rental type first',
    'no_rentals_available' => 'No rentals available',
    'agreement_generated' => 'Agreement generated successfully',
    'agreement_updated' => 'Agreement updated successfully (new version created)',
    'version_not_found' => 'Version not found',
    'switch_version_confirm' => 'Load this version? Current unsaved changes will be lost.',

    // Sections
    'sections' => 'Agreement Sections',
    'section_title' => 'Section Title',
    'section_content' => 'Section Content',
    'version_history' => 'Version History',
    'current_version' => 'Current Version',
    'previous_versions' => 'Previous Versions',
    'select_version' => 'Select Version',
    'no_sections' => 'No sections available. Click "Add Section" to create sections manually.',
    'add_section_manually' => 'You can add sections manually even without a template.',

    // Actions
    'generate_agreement' => 'Generate Agreement',
    'regenerate' => 'Regenerate',
    'view_agreement' => 'View Agreement',
    'print_agreement' => 'Print Agreement',
    'download_pdf' => 'Download PDF',
    'send_to_tenant' => 'Send to Tenant',

    // Hints
    'template_hint' => 'Choose the template to generate agreement',
    'rental_type_hint' => 'Select Long Rental or Short Rental',
    'rental_hint' => 'Select the rental to generate agreement for',
    'status_hint' => 'Agreement status',
    'version_hint' => 'Saving changes will create a new version of this agreement (e.g., v2, v3). The current version will be preserved in the version history.',
    'change_template_hint' => 'Changing template will update the agreement template reference',

    // Preview
    'preview' => 'Preview Agreement',
    'agreement_preview' => 'Agreement Preview',
    'print' => 'Print',

    // Table columns
    'type' => 'Type',
];
