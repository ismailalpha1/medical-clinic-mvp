<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinic_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_name', 180);
            $table->string('contact_person', 160);
            $table->string('email', 180);
            $table->string('phone', 40);
            $table->string('website')->nullable();
            $table->string('country', 100);
            $table->string('city', 100);
            $table->string('address')->nullable();
            $table->string('clinic_type', 120);
            $table->json('specialties')->nullable();
            $table->text('services')->nullable();
            $table->string('number_of_doctors', 50)->nullable();
            $table->string('number_of_locations', 50)->nullable();
            $table->unsignedSmallInteger('year_established')->nullable();
            $table->text('description')->nullable();
            $table->json('goals')->nullable();
            $table->string('preferred_contact_method', 30);
            $table->boolean('consent')->default(false);
            $table->timestamps();
            $table->index(['country', 'city']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_submissions');
    }
};
