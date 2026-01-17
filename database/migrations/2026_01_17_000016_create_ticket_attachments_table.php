<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TICKET_ATTACHMENTS', function (Blueprint $table) {
            $table->id('file_id');

            $table->foreignId('ticket_id')
                ->constrained('TICKETS', 'ticket_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreignId('message_id')
                ->nullable()
                ->constrained('TICKET_MESSAGES', 'message_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreignId('uploaded_by')
                ->constrained('USERS', 'user_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->string('stored_path', 255);
            $table->string('original_name', 255);
            $table->string('mime', 80)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->index(['message_id', 'created_at']);
        });

        // Migrate existing data from the old tables (SQL Server only).
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared(<<<'SQL'
IF OBJECT_ID('dbo.TICKET_FILES', 'U') IS NOT NULL
BEGIN
  INSERT INTO dbo.TICKET_ATTACHMENTS
    (ticket_id, message_id, uploaded_by, stored_path, original_name, mime, size, created_at, updated_at)
  SELECT
    f.ticket_id,
    NULL AS message_id,
    f.uploaded_by,
    f.stored_path,
    f.original_name,
    f.mime,
    f.size,
    f.created_at,
    f.updated_at
  FROM dbo.TICKET_FILES f
  WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.TICKET_ATTACHMENTS a
    WHERE a.ticket_id = f.ticket_id
      AND a.message_id IS NULL
      AND a.stored_path = f.stored_path
  );
END

IF OBJECT_ID('dbo.TICKET_MESSAGE_FILES', 'U') IS NOT NULL
BEGIN
  INSERT INTO dbo.TICKET_ATTACHMENTS
    (ticket_id, message_id, uploaded_by, stored_path, original_name, mime, size, created_at, updated_at)
  SELECT
    m.ticket_id,
    f.message_id,
    f.uploaded_by,
    f.stored_path,
    f.original_name,
    f.mime,
    f.size,
    f.created_at,
    f.updated_at
  FROM dbo.TICKET_MESSAGE_FILES f
  INNER JOIN dbo.TICKET_MESSAGES m ON m.message_id = f.message_id
  WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.TICKET_ATTACHMENTS a
    WHERE a.message_id = f.message_id
      AND a.stored_path = f.stored_path
  );
END

-- Drop old tables to avoid duplication.
IF OBJECT_ID('dbo.TICKET_MESSAGE_FILES', 'U') IS NOT NULL
  DROP TABLE dbo.TICKET_MESSAGE_FILES;

IF OBJECT_ID('dbo.TICKET_FILES', 'U') IS NOT NULL
  DROP TABLE dbo.TICKET_FILES;
SQL);
    }

    public function down(): void
    {
        // Best-effort restore old tables for rollback scenarios.
      if (!Schema::hasTable('TICKET_FILES')) {
        Schema::create('TICKET_FILES', function (Blueprint $table) {
          $table->id('file_id');

          $table->foreignId('ticket_id')
            ->constrained('TICKETS', 'ticket_id')
            ->onUpdate('no action')
            ->onDelete('no action');

          $table->foreignId('uploaded_by')
            ->constrained('USERS', 'user_id')
            ->onUpdate('no action')
            ->onDelete('no action');

          $table->string('stored_path', 255);
          $table->string('original_name', 255);
          $table->string('mime', 80)->nullable();
          $table->unsignedBigInteger('size')->nullable();

          $table->timestamps();

          $table->index(['ticket_id', 'created_at']);
          $table->unique(['ticket_id', 'stored_path']);
        });
      }

      if (!Schema::hasTable('TICKET_MESSAGE_FILES')) {
        Schema::create('TICKET_MESSAGE_FILES', function (Blueprint $table) {
          $table->id('file_id');

          $table->foreignId('message_id')
            ->constrained('TICKET_MESSAGES', 'message_id')
            ->onUpdate('no action')
            ->onDelete('no action');

          $table->foreignId('uploaded_by')
            ->constrained('USERS', 'user_id')
            ->onUpdate('no action')
            ->onDelete('no action');

          $table->string('stored_path', 255);
          $table->string('original_name', 255);
          $table->string('mime', 80)->nullable();
          $table->unsignedBigInteger('size')->nullable();

          $table->timestamps();

          $table->index(['message_id', 'created_at']);
          $table->unique(['message_id', 'stored_path']);
        });
      }

        Schema::dropIfExists('TICKET_ATTACHMENTS');
    }
};
