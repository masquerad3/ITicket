USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_ticket_file
  @ticket_id INT,
  @uploaded_by INT,
  @stored_path NVARCHAR(255),
  @original_name NVARCHAR(255),
  @mime NVARCHAR(80) = NULL,
  @size BIGINT = NULL
AS
BEGIN
  SET NOCOUNT ON;

  IF NOT EXISTS (
    SELECT 1 FROM dbo.TICKET_FILES
    WHERE ticket_id = @ticket_id AND stored_path = @stored_path
  )
  BEGIN
    INSERT INTO dbo.TICKET_FILES
      (ticket_id, uploaded_by, stored_path, original_name, mime, size, created_at, updated_at)
    VALUES
      (@ticket_id, @uploaded_by, @stored_path, @original_name, @mime, @size, SYSDATETIME(), SYSDATETIME());
  END

  SELECT CAST(1 AS BIT) AS ok;
END
GO
