<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCommentNoteDatatypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table){
            $table->text('comment')->change();
        });
        Schema::table('bug_comments', function (Blueprint $table){
            $table->text('comment')->change();
        });
        Schema::table('notes', function (Blueprint $table){
            $table->text('text')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('comment')->change();
        });
        Schema::table('bug_comments', function (Blueprint $table) {
            $table->string('comment')->change();
        });
        Schema::table('notes', function (Blueprint $table) {
            $table->string('text')->change();
        });
    }
}
