USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_ticket_attachment
  @ticket_id INT,
  @message_id INT = NULL,
  @uploaded_by INT,
  @stored_path NVARCHAR(255),
  @original_name NVARCHAR(255),
  @mime NVARCHAR(80) = NULL,
  @size BIGINT = NULL
AS
BEGIN
  SET NOCOUNT ON;

  IF @message_id IS NULL
  BEGIN
    IF NOT EXISTS (
      SELECT 1 FROM dbo.TICKET_ATTACHMENTS
      WHERE ticket_id = @ticket_id
        AND message_id IS NULL
        AND stored_path = @stored_path
    )
    BEGIN
      INSERT INTO dbo.TICKET_ATTACHMENTS
        (ticket_id, message_id, uploaded_by, stored_path, original_name, mime, size, created_at, updated_at)
      VALUES
        (@ticket_id, NULL, @uploaded_by, @stored_path, @original_name, @mime, @size, SYSDATETIME(), SYSDATETIME());
    END
  END
  ELSE
  BEGIN
    IF NOT EXISTS (
      SELECT 1 FROM dbo.TICKET_ATTACHMENTS
      WHERE message_id = @message_id
        AND stored_path = @stored_path
    )
    BEGIN
      INSERT INTO dbo.TICKET_ATTACHMENTS
        (ticket_id, message_id, uploaded_by, stored_path, original_name, mime, size, created_at, updated_at)
      VALUES
        (@ticket_id, @message_id, @uploaded_by, @stored_path, @original_name, @mime, @size, SYSDATETIME(), SYSDATETIME());
    END
  END

  SELECT CAST(1 AS BIT) AS ok;
END
GO
