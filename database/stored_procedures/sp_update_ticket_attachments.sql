USE ITicket;
GO

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
GO
