<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    FeatureProjectService,
    ProjectService,
};

use App\Models\{
    Country,
};

class FeatureProjectController extends Controller
{
    private function getFeatureTypeConfig($type, $adType) {
        $featureTypes = [
            '1' => 'featured_listing',
            '2' => 'trending_project',
            '3' => 'mm2m_project',
        ];

        $adTypes = [
            '1' => 'for_sale',
            '2' => 'short_rent',
            '3' => 'long_rent',
        ];

        $featureTitles = [
            '1' => __( 'feature_project.featured_listings' ),
            '2' => __( 'feature_project.trending_projects' ),
            '3' => __( 'feature_project.mm2m_projects' ),
        ];

        $adTypeTitles = [
            '1' => __( 'feature_project.for_sale' ),
            '2' => __( 'feature_project.short_rent' ),
            '3' => __( 'feature_project.long_rent' ),
        ];

        if (!isset($featureTypes[$type]) || !isset($adTypes[$adType])) {
            return null;
        }

        // Generate route prefix based on feature type
        // Type 1 (featured_listings) uses 'feature_project' instead of 'featured_listings'
        // Types 2 and 3 use their feature type names directly
        if ($type == '1') {
            $routePrefix = $adTypes[$adType] . '_feature_project';
        } else {
            $routePrefix = $adTypes[$adType] . '_' . $featureTypes[$type];
        }

        return [
            'title' => $featureTitles[$type] . ' (' . $adTypeTitles[$adType] . ')',
            'route_prefix' => $routePrefix,
            'ad_type_prefix' => $adTypes[$adType],
        ];
    }

    public function index( Request $request ) {
        $featureType = $request->route('feature_type');
        $advertisementType = $request->route('advertisement_type');
        $config = $this->getFeatureTypeConfig($featureType, $advertisementType);

        if (!$config) {
            abort(404);
        }

        $this->data['header']['title'] = $config['title'];
        $this->data['content'] = 'admin.feature_project.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => $config['title'],
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
                '10' => __( 'datatables.activated' ),
                '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['feature_type'] = $featureType;
        $this->data['data']['advertisement_type'] = $advertisementType;
        $this->data['data']['feature_config'] = $config;
        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {
        $featureType = $request->route('feature_type');
        $advertisementType = $request->route('advertisement_type');
        $config = $this->getFeatureTypeConfig($featureType, $advertisementType);
        
        if (!$config) {
            abort(404);
        }

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( $config['title'] ) ] );
        $this->data['content'] = 'admin.feature_project.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.' . $config['route_prefix'] . '.index' ),
                'text' => $config['title'],
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( $config['title'] ) ] ),
                'class' => 'active',
            ],
        ];

        // Specific countries for language support: en, zh_tw, id, zh_cn, ja, ko
        $targetCountries = [
            'Malaysia',    // en (English)
            'Singapore',    // en (English)
            'Taiwan',          // zh_tw (Traditional Chinese)
            'Indonesia',       // id (Indonesian)
            'China',           // zh_cn (Simplified Chinese)
            'Japan',           // ja (Japanese)
            'Korea'            // ko (Korean)
        ];

        $this->data['data']['countries'] = Country::whereIn('country_name->en', $targetCountries)
        ->orderByRaw("FIELD(JSON_UNQUOTE(JSON_EXTRACT(country_name, '$.en')), '" . implode("','", $targetCountries) . "')")
        ->pluck('country_name', 'id')
        ->toArray();

        $this->data['data']['projects'] = FeatureProjectService::getAllProjects();
        $this->data['data']['feature_type'] = $featureType;
        $this->data['data']['advertisement_type'] = $advertisementType;
        $this->data['data']['feature_config'] = $config;
        
        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {
        $featureType = $request->route('feature_type');
        $advertisementType = $request->route('advertisement_type');
        $config = $this->getFeatureTypeConfig($featureType, $advertisementType);

        if (!$config) {
            abort(404);
        }

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( $config['title'] ) ] );
        $this->data['content'] = 'admin.feature_project.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.' . $config['route_prefix'] . '.index' ),
                'text' => $config['title'],
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( $config['title'] ) ] ),
                'class' => 'active',
            ],
        ];

        // Specific countries for language support: en, zh_tw, id, zh_cn, ja, ko
        $targetCountries = [
            'Malaysia',    // en (English)
            'Singapore',    // en (English)
            'Taiwan',          // zh_tw (Traditional Chinese)
            'Indonesia',       // id (Indonesian)
            'China',           // zh_cn (Simplified Chinese)
            'Japan',           // ja (Japanese)
            'Korea'            // ko (Korean)
        ];

        $this->data['data']['countries'] = Country::whereIn('country_name->en', $targetCountries)
        ->orderByRaw("FIELD(JSON_UNQUOTE(JSON_EXTRACT(country_name, '$.en')), '" . implode("','", $targetCountries) . "')")
        ->pluck('country_name', 'id')
        ->toArray();

        $this->data['data']['projects'] = FeatureProjectService::getAllProjects();
        $this->data['data']['feature_type'] = $featureType;
        $this->data['data']['advertisement_type'] = $advertisementType;
        $this->data['data']['feature_config'] = $config;

        return view( 'admin.main' )->with( $this->data );
    }

    public function allFeatureProjects( Request $request ) {

        return FeatureProjectService::allFeatureProjects( $request );
    }

    public function oneFeatureProject( Request $request ) {

        return FeatureProjectService::oneFeatureProject( $request );
    }

    public function createFeatureProject( Request $request ) {

        return FeatureProjectService::createFeatureProject( $request );
    }

    public function updateFeatureProject( Request $request ) {

        return FeatureProjectService::updateFeatureProject( $request );
    }

    public function updateFeatureProjectStatus( Request $request ) {

        return FeatureProjectService::updateFeatureProjectStatus( $request );
    }
}