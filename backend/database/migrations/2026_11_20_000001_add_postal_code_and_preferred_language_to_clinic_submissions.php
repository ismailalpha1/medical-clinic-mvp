<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinic_submissions', function (Blueprint $table) {
            $table->string('postal_code', 20)->nullable()->after('city');
            $table->string('preferred_language', 2)->default('en')->after('preferred_contact_method');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_submissions', function (Blueprint $table) {
            $table->dropColumn(['postal_code', 'preferred_language']);
        });
    }
};
