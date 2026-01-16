<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only SQL Server supports these exact procedure bodies.
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_create_ticket
  @user_id            INT,
  @subject            NVARCHAR(255),
  @category           NVARCHAR(50),
  @priority           NVARCHAR(10),
  @department         NVARCHAR(100) = NULL,
  @location           NVARCHAR(100) = NULL,
  @description        NVARCHAR(MAX),
  @preferred_contact  NVARCHAR(10),
  @status             NVARCHAR(20) = 'open'
AS
BEGIN
  SET NOCOUNT ON;

  INSERT INTO dbo.TICKETS
    (user_id, subject, category, priority, department, location, description, preferred_contact, status, attachments, created_at, updated_at)
  VALUES
    (@user_id, @subject, @category, @priority, @department, @location, @description, @preferred_contact, @status, NULL, SYSDATETIME(), SYSDATETIME());

  SELECT CAST(SCOPE_IDENTITY() AS INT) AS ticket_id;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_update_ticket_attachments
  @ticket_id   INT,
  @attachments NVARCHAR(MAX) = NULL
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.TICKETS
  SET attachments = @attachments,
      updated_at = SYSDATETIME()
  WHERE ticket_id = @ticket_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_read_tickets_by_user
  @user_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    t.ticket_id,
    t.user_id,
    t.subject,
    t.category,
    t.priority,
    t.department,
    t.location,
    t.description,
    t.preferred_contact,
    t.status,
    t.attachments,
    t.assigned_to,
    t.assigned_at,
    t.resolved_at,
    t.created_at,
    t.updated_at,
    u.first_name AS requester_first_name,
    u.last_name  AS requester_last_name,
    a.first_name AS assignee_first_name,
    a.last_name  AS assignee_last_name
  FROM dbo.TICKETS t
  INNER JOIN dbo.USERS u ON u.user_id = t.user_id
  LEFT JOIN dbo.USERS a ON a.user_id = t.assigned_to
  WHERE t.user_id = @user_id
  ORDER BY t.created_at DESC;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_read_all_tickets
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    t.ticket_id,
    t.user_id,
    t.subject,
    t.category,
    t.priority,
    t.department,
    t.location,
    t.description,
    t.preferred_contact,
    t.status,
    t.attachments,
    t.assigned_to,
    t.assigned_at,
    t.resolved_at,
    t.created_at,
    t.updated_at,
    u.first_name AS requester_first_name,
    u.last_name  AS requester_last_name,
    a.first_name AS assignee_first_name,
    a.last_name  AS assignee_last_name
  FROM dbo.TICKETS t
  INNER JOIN dbo.USERS u ON u.user_id = t.user_id
  LEFT JOIN dbo.USERS a ON a.user_id = t.assigned_to
  ORDER BY t.created_at DESC;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_by_id
  @ticket_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT TOP (1)
    t.ticket_id,
    t.user_id,
    t.subject,
    t.category,
    t.priority,
    t.department,
    t.location,
    t.description,
    t.preferred_contact,
    t.status,
    t.attachments,
    t.assigned_to,
    t.assigned_at,
    t.resolved_at,
    t.created_at,
    t.updated_at,
    u.first_name AS requester_first_name,
    u.last_name  AS requester_last_name,
    u.email      AS requester_email,
    u.contact    AS requester_contact,
    a.first_name AS assignee_first_name,
    a.last_name  AS assignee_last_name,
    a.email      AS assignee_email
  FROM dbo.TICKETS t
  INNER JOIN dbo.USERS u ON u.user_id = t.user_id
  LEFT JOIN dbo.USERS a ON a.user_id = t.assigned_to
  WHERE t.ticket_id = @ticket_id;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_assign_ticket_to_user
  @ticket_id   INT,
  @assigned_to INT
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.TICKETS
  SET assigned_to = @assigned_to,
      assigned_at = COALESCE(assigned_at, SYSDATETIME()),
      status = CASE WHEN status = 'open' THEN 'in_progress' ELSE status END,
      updated_at = SYSDATETIME()
  WHERE ticket_id = @ticket_id
    AND assigned_to IS NULL;

  SELECT @@ROWCOUNT AS rows_affected;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER PROCEDURE dbo.sp_update_ticket_status
  @ticket_id INT,
  @status    NVARCHAR(20)
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.TICKETS
  SET status = @status,
      resolved_at = CASE WHEN @status = 'resolved' THEN COALESCE(resolved_at, SYSDATETIME()) ELSE NULL END,
      updated_at = SYSDATETIME()
  WHERE ticket_id = @ticket_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_update_ticket_status');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_assign_ticket_to_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_read_ticket_by_id');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_read_all_tickets');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_read_tickets_by_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_update_ticket_attachments');
        DB::unprepared('DROP PROCEDURE IF EXISTS dbo.sp_create_ticket');
    }
};
