<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsArticleTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_article_id')->constrained('cms_articles')->onDelete('cascade');
            $table->string('locale', 10)->comment('en, ms, zh-CN, etc.');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable()->comment('Rich text editor content');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate translations for same article-locale pair
            $table->unique(['cms_article_id', 'locale']);

            // Index for faster locale lookups
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_article_translations');
    }
}
