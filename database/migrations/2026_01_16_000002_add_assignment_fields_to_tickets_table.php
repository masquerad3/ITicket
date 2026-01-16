<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TICKETS', function (Blueprint $table) {
            // Columns
            $table->unsignedBigInteger('assigned_to')->nullable()->after('user_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->timestamp('resolved_at')->nullable()->after('status');

            // SQL Server: avoid set null/cascade to prevent multiple cascade paths
            // and avoid "restrict" keyword (not valid on SQL Server).
            $table->foreign('assigned_to', 'tickets_assigned_to_foreign')
                ->references('user_id')
                ->on('USERS')
                ->onDelete('no action')   // <- SQL Server valid
                ->onUpdate('no action');
        });
    }

    public function down(): void
    {
        Schema::table('TICKETS', function (Blueprint $table) {
            $table->dropForeign('tickets_assigned_to_foreign');
            $table->dropColumn(['assigned_to', 'assigned_at', 'resolved_at']);
        });
    }
};