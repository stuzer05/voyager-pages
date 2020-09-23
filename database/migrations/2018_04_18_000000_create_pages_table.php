<?php

use Pvtl\VoyagerPages\Page;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('author_id');
                $table->string('title');
                $table->text('excerpt')->nullable();
                $table->text('body')->nullable();
                $table->string('image')->nullable();
                $table->string('slug');
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->enum('status', Page::$statuses)->default(Page::STATUS_INACTIVE);
                $table->string('route_name')->nullable();
                $table->string('layout')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pages');
    }
}
