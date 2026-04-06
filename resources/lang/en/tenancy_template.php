<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy Template Language Lines
    |--------------------------------------------------------------------------
    */

    'id' => 'ID',
    'name' => 'Template Name',
    'description' => 'Description',
    'status' => 'Status',
    'created_by' => 'Created By',
    'sections' => 'Sections',
    'not_found' => 'Template not found',

    // Section fields
    'section_key' => 'Section Key',
    'section_title' => 'Section Title',
    'section_content' => 'Section Content',
    'section_status' => 'Section Status',
    'sort_order' => 'Sort Order',
    'add_section' => 'Add Section',
    'remove_section' => 'Remove Section',

    // Placeholders
    'available_placeholders' => 'Available Placeholders',
    'placeholder_guide' => 'Use these placeholders in your template. They will be replaced with actual data when generating agreements.',
    'click_to_copy' => 'Click to copy',

    // Placeholder categories
    'tenant_placeholders' => 'Tenant Information',
    'property_placeholders' => 'Property Information',
    'rental_placeholders' => 'Rental Details',
    'agreement_placeholders' => 'Agreement Details',
    'owner_placeholders' => 'Owner Information',
    'date_placeholders' => 'Date & Time',

    // Tenant placeholders
    'tenant_fullname' => 'Tenant Full Name',
    'tenant_email' => 'Tenant Email',
    'tenant_phone' => 'Tenant Phone Number',
    'tenant_id_number' => 'Tenant ID Number',
    'tenant_address' => 'Tenant Address',

    // Property placeholders
    'property_name' => 'Property Name',
    'property_address' => 'Property Address',
    'property_unit_number' => 'Unit Number',
    'property_block_number' => 'Block Number',
    'property_floor' => 'Floor Number',
    'property_bedrooms' => 'Number of Bedrooms',
    'property_bathrooms' => 'Number of Bathrooms',
    'property_built_up_area' => 'Built-up Area (sqft)',

    // Rental placeholders
    'rental_price' => 'Monthly Rental Price',
    'rental_deposit' => 'Security Deposit',
    'rental_duration' => 'Rental Duration (months)',
    'rental_start_date' => 'Rental Start Date',
    'rental_end_date' => 'Rental End Date',
    'maintenance_fee' => 'Maintenance Fee',

    // Agreement placeholders
    'agreement_number' => 'Agreement Number',
    'agreement_date' => 'Agreement Date',
    'agreement_version' => 'Agreement Version',

    // Owner/Agent placeholders
    'owner_name' => 'Owner Name',
    'owner_email' => 'Owner Email',
    'owner_phone' => 'Owner Phone',
    'agent_name' => 'Agent Name',
    'agent_email' => 'Agent Email',
    'agent_phone' => 'Agent Phone',
    'company_name' => 'Company Name',

    // Date placeholders
    'current_date' => 'Current Date',
    'current_datetime' => 'Current Date & Time',
    'current_year' => 'Current Year',

    // Hints
    'name_hint' => 'Give this template a descriptive name',
    'description_hint' => 'Optional description for this template',
    'section_key_hint' => 'Unique identifier for this section (e.g., terms_conditions, payment_terms)',
    'section_title_hint' => 'Section heading that will appear in the agreement',
    'section_content_hint' => 'Use {{placeholders}} that will be replaced with actual data',
];
