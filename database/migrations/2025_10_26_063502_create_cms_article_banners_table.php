<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsArticleBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_article_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_article_id')->constrained('cms_articles')->onDelete('cascade');
            $table->string('image');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0)->comment('Order in carousel slider');
            $table->timestamps();

            // Index for sorting banners
            $table->index(['cms_article_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_article_banners');
    }
}
