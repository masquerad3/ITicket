USE ITicket;
GO

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
GO
