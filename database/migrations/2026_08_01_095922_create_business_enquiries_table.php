<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('partner_type')->index();
            $table->string('name');
            $table->string('firm_name');
            $table->string('mobile', 10)->index();
            $table->string('email')->nullable();
            $table->string('state')->index();
            $table->string('district');
            $table->string('city');
            $table->string('investment_range');
            $table->string('years_in_business');
            $table->string('current_business');
            $table->boolean('has_godown')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_enquiries');
    }
};
