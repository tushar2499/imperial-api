<?php

use App\Models\Counter;
use App\Models\District;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignIdFor(Counter::class)->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable()->after('counter_id');
            $table->string('user_name')->after('role')->index();
            $table->string('first_name')->after('user_name');
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('mobile')->after('email')->nullable();
            $table->string('identity_type')->after('mobile')->nullable();
            $table->string('identity_image')->after('identity_type')->nullable();
            $table->string('date_of_birth')->after('identity_image')->nullable();
            $table->string('country')->after('date_of_birth')->nullable();
            $table->foreignIdFor(District::class)->nullable()->after('country')->constrained()->cascadeOnDelete();
            $table->string('gender')->after('district_id')->nullable();
            $table->enum('type', ['1', '2'])->after('gender')->comment('1: company user, 2: agent');
            $table->enum('access_type', ['1', '2', '3'])->nullable()->after('type')->comment('1: web, 2: api, 3: both');
            $table->enum('account_type', ['1', '2'])->nullable()->after('access_type')->comment('1: prepaid, 2: postpaid');
            $table->integer('front_date')->after('account_type')->nullable();
            $table->integer('back_date')->after('front_date')->nullable();
            $table->enum('status', ['0', '1'])->default('1')->after('back_date')->comment('0: inactive, 1: active');
            $table->unsignedBigInteger('created_by')->after('status')->nullable();
            $table->unsignedBigInteger('updated_by')->after('created_by')->nullable();
            $table->softDeletes();

            $table->string('email')->nullable()->change();
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor(Counter::class);
            $table->dropColumn('counter_id');

            $table->dropColumn('role');
            $table->dropIndex(['user_name']);
            $table->dropColumn('user_name');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('mobile');
            $table->dropColumn('identity_type');
            $table->dropColumn('identity_image');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('country');

            $table->dropForeignIdFor(District::class);
            $table->dropColumn('district_id');

            $table->dropColumn('gender');
            $table->dropColumn('type');
            $table->dropColumn('access_type');
            $table->dropColumn('account_type');
            $table->dropColumn('front_date');
            $table->dropColumn('back_date');
            $table->dropColumn('status');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');

            $table->dropSoftDeletes();

            $table->string('email')->nullable(false)->change();
            $table->string('name')->after('id');
        });
    }
};
