# Admin Utils — Reusable Blade Components

Reusable form partials for the admin panel. Drop into any blade, pass a unique `$prefix`, and all fields, JS, and validation wiring is handled automatically.

Showcase page: `GET /{admin_path}/utils/showcase`

---

## Table of Contents

1. [admin.utils.utils — General Fields](#adminutilsutils--general-fields)
2. [admin.utils.cms — CMS / Article Content](#adminutilscms--cms--article-content)
3. [Utils Routes](#utils-routes)
4. [Conventions](#conventions)

---

## `admin.utils.utils` — General Fields

### Include

```blade
@include('admin.utils.utils', ['prefix' => 'my_prefix'])
```

### Controller — Required Data

```php
$this->data['data']['calling_codes']      = Helper::getCallingCodes();
$this->data['data']['select_options']     = [
    ['value' => '1', 'text' => 'Option A'],
    ['value' => '2', 'text' => 'Option B'],
];
$this->data['data']['multiselect_options'] = [
    ['value' => '1', 'text' => 'Category 1'],
    ['value' => '2', 'text' => 'Category 2'],
];
```

### Fields

| Field | Element ID | Input Type | Notes |
|---|---|---|---|
| Full Name | `{prefix}_fullname` | `text` | |
| Calling Code | `{prefix}_calling_code` | `select` | Defaults to `+60` |
| Phone Number | `{prefix}_phone_number` | `text` | Paired with calling code in input-group |
| Password | `{prefix}_password` | `password` | `autocomplete="new-password"` |
| Numeric | `{prefix}_numeric` | `number` | `step="1"` `min="0"` |
| Decimal | `{prefix}_decimal` | `number` | `step="0.01"` `min="0"` |
| Select | `{prefix}_select` | Select2 (single) | Static options from `$data['select_options']` |
| Multiselect | `{prefix}_multiselect` | Select2 (multi) | Static options from `$data['multiselect_options']`, `closeOnSelect: false` |
| Tags | `{prefix}_tags` | Select2 (tags) | AJAX search via `admin.sports_tag.all`; new entries prefixed `new:` |
| Image | `{prefix}_dropzone` | Dropzone | Single file; path stored in `window['{prefix}_imagePath']` |
| Images | `{prefix}_multi_dropzone` | Dropzone | Multiple files; paths stored in `window['{prefix}_imagePaths']` |

### JS Globals

| Variable | Type | Description |
|---|---|---|
| `window['{prefix}_imagePath']` | `string` | Uploaded path from the single-image Dropzone |
| `window['{prefix}_imagePaths']` | `string[]` | Uploaded paths from the multi-image Dropzone |

### Reading Values on Submit

```js
let prefix = 'my_prefix';

let fullname      = $(`#${prefix}_fullname`).val();
let callingCode   = $(`#${prefix}_calling_code`).val();
let phoneNumber   = $(`#${prefix}_phone_number`).val();
let password      = $(`#${prefix}_password`).val();
let numeric       = $(`#${prefix}_numeric`).val();
let decimal       = $(`#${prefix}_decimal`).val();
let selected      = $(`#${prefix}_select`).val();              // string
let multiSelected = $(`#${prefix}_multiselect`).val();         // array
let tags          = $(`#${prefix}_tags`).find(':selected')
                        .map((i, el) => $(el).val()).get();    // array; 'new:term' for created tags
let imagePath     = window[`${prefix}_imagePath`];             // string
let imagePaths    = window[`${prefix}_imagePaths`];            // array
```

### Appending to FormData

```js
formData.append('fullname',     fullname);
formData.append('calling_code', callingCode);
formData.append('phone_number', phoneNumber);
formData.append('password',     password);
formData.append('numeric',      numeric);
formData.append('decimal',      decimal);
formData.append('select',       selected);
multiSelected.forEach(v  => formData.append('multiselect[]', v));
tags.forEach(t          => formData.append('tags[]', t));
if (imagePath)            formData.append('image', imagePath);
imagePaths.forEach(p    => formData.append('images[]', p));
```

---

## `admin.utils.cms` — CMS / Article Content

### Include

```blade
@include('admin.utils.cms', ['prefix' => 'my_prefix'])
```

No extra controller data needed — languages are read from `Config::get('languages')` automatically.

### Fields (per language tab)

| Field | Element ID | Input Type | Notes |
|---|---|---|---|
| Title | `{prefix}_{lang}_title` | `text` | Required on first (default) language |
| Slug | `{prefix}_{lang}_slug` | `text` (readonly) | Auto-generated from title |
| Short Description | `{prefix}_{lang}_short_desc` | `textarea` | Plain text |
| Content | `{prefix}_{lang}_content` | CKEditor 5 | Full rich-text editor with image upload |

### JS Globals

| Variable | Type | Description |
|---|---|---|
| `window['{prefix}_editors']` | `object` | Keyed by lang code; each value is a CKEditor instance |

### Reading Values on Submit

```js
let prefix = 'my_prefix';
let langs  = Object.keys(window[`${prefix}_editors`]);

let translations = {};
langs.forEach(lang => {
    translations[lang] = {
        title       : $(`#${prefix}_${lang}_title`).val(),
        slug        : $(`#${prefix}_${lang}_slug`).val(),
        short_desc  : $(`#${prefix}_${lang}_short_desc`).val(),
        content     : window[`${prefix}_editors`][lang].getData(),
    };
});

formData.append('translations', JSON.stringify(translations));
```

### CKEditor Image Upload

- **Route:** `admin.utils.ckeUpload`
- **URL:** `POST /{admin_path}/utils/cke-upload`
- **Storage:** `storage/app/public/admin/images/utils/ckeditor/`

---

## Utils Routes

| Method | URL | Route Name | Description |
|---|---|---|---|
| `GET`  | `/{admin_path}/utils/showcase`       | `admin.utils.showcase`        | Renders the showcase page |
| `POST` | `/{admin_path}/utils/calling-codes`  | `admin.utils.getCallingCodes` | Returns calling codes as JSON |
| `POST` | `/{admin_path}/utils/cke-upload`     | `admin.utils.ckeUpload`       | CKEditor in-editor image upload |

---

## Conventions

### Prefix
Always pass a **unique** `$prefix` per inclusion. If the same partial is included twice on one page, use different prefixes (e.g. `user_create` and `staff_create`) to avoid ID collisions.

### Select2
Always initialised with:
```js
{ theme: 'bootstrap-5', width: '100%' }
```

### Dropzone (pre-upload pattern)
Dropzone uploads immediately on file drop → receives the stored path from the server → path **string** is appended to FormData on submit. The binary file is never re-sent on submit.

### CKEditor
Always uses the **local build** at `admin/js/ckeditor/ckeditor.js` (not CDN). Initialised via `ClassicEditor.create()`. Instances stored in `window['{prefix}_editors']`.

### Validation Errors
All fields have a sibling `<div class="invalid-feedback"></div>`. To show errors from an AJAX 422 response:
```js
$.each(errors, function(key, message) {
    $(`#${prefix}_${key}`)
        .addClass('is-invalid')
        .nextAll('div.invalid-feedback')
        .text(message);
});
```

---

## File Locations

```
resources/views/admin/utils/
    utils.blade.php       — general fields partial
    cms.blade.php         — CMS/article content partial
    showcase.blade.php    — showcase page

app/Http/Controllers/Admin/
    UtilsController.php   — showcase(), ckeUpload(), getCallingCodes()

routes/admin.php
    → Route::prefix('utils') ...
```
