<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    CMSService,
};

class CmsController extends Controller
{
    public function __construct() {}

    /**
     * 1. Get Articles
     *
     * <aside class="notice">Get all articles</aside>
     *
     *
     * @group CMS Articles API
     *
     */
    public function getArticles( Request $request ) {

        return CMSService::getArticles( $request );
    }

    /**
     * 2. Get one article detail
     * 
     * @group CMS Articles API
     * 
     * 
     * @queryParam id string required The id the article. Example: 1
     * 
     */ 
    public function oneArticle( Request $request ) {
        
        return CMSService::oneArticle( $request );
    }
}