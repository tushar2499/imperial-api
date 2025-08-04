<?php

use App\Models\Designation;
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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_no');
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('date_of_birth');
            $table->string('nid_or_passport_no')->nullable();
            $table->string('nid_or_passport_no_image')->nullable();
            $table->string('job_type')->nullable();
            $table->string('duty_hour')->nullable();
            $table->date('joining_date')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->foreignIdFor(District::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Designation::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('license_category')->nullable();
            $table->string('license_no')->nullable();
            $table->string('license_expired_date')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_contact_no')->nullable();
            $table->text('reference_remark')->nullable();
            $table->string('nominee_name')->nullable();
            $table->string('nominee_photo')->nullable();
            $table->string('nominee_contact_no')->nullable();
            $table->string('nominee_nid_or_passport_no')->nullable();
            $table->string('nominee_nid_or_passport_no_image')->nullable();
            $table->string('nominee_relation')->nullable();

            $table->tinyInteger('status')->default(1)->comment('1:active, 0:inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
