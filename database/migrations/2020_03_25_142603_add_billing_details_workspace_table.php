<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingDetailsWorkspaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('company')->nullable()->after('currency');
            $table->string('address')->nullable()->after('company');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('zipcode')->nullable()->after('state');
            $table->string('country')->nullable()->after('zipcode');
            $table->string('telephone')->nullable()->after('country');
            $table->string('logo')->nullable()->after('telephone');
            $table->text('stripe_key')->nullable()->after('logo');
            $table->text('stripe_secret')->nullable()->after('stripe_key');
            $table->string('invoice_template')->nullable()->after('stripe_secret');
            $table->string('invoice_color')->nullable()->after('invoice_template');
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
            $table->dropColumn('company');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zipcode');
            $table->dropColumn('country');
            $table->dropColumn('telephone');
            $table->dropColumn('stripe_key');
            $table->dropColumn('stripe_secret');
            $table->dropColumn('invoice_template');
            $table->dropColumn('invoice_template');
        });
    }
}
