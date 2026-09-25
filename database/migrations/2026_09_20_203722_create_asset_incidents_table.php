<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_incidents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')
                ->constrained('assets');

            $table->foreignId('affected_staff_id')
                ->constrained('staff');

            $table->foreignId('assigned_to_user_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('incident_type_id')
                ->constrained('incident_types');

            $table->foreignId('incident_status_id')
                ->constrained('incident_statuses');

            $table->date('incident_date');

            $table->text('description');

            $table->text('action_taken')->nullable();

            $table->string('warranty_claim_ref')->nullable();

            $table->date('warranty_claim_date')->nullable();

            $table->decimal('warranty_excess', 10, 2)->nullable();

            $table->decimal('associated_cost', 10, 2)->nullable();

            $table->date('resolved_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_incidents');
    }
};