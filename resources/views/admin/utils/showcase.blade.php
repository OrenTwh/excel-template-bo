<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Utils Showcase</h3>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-inner">
        <h5 class="card-title mb-4">General Fields</h5>
        @include( 'admin.utils.utils', [ 'prefix' => 'showcase' ] )
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <h5 class="card-title mb-4">CMS / Article Fields</h5>
        @include( 'admin.utils.cms', [ 'prefix' => 'showcase_cms' ] )
    </div>
</div>
