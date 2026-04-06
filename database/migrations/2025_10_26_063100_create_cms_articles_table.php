<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_articles', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail')->nullable();
            $table->date('publish_date')->default(now());
            $table->tinyInteger('status')->default(0)->comment('0: draft, 1: published');
            $table->timestamps();
            $table->softDeletes();

            // Index for sorting by publish date
            $table->index(['publish_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_articles');
    }
}
