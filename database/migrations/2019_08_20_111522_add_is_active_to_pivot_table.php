<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveToPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('lang');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('created_by');
        });
        Schema::table('user_workspaces', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('permission');
        });
        Schema::table('user_projects', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('project_id');
        });
        Schema::table('client_workspaces', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('workspace_id');
        });
        Schema::table('client_projects', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('user_workspaces', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('user_projects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('client_workspaces', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('client_projects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
