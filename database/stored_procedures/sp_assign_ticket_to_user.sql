USE ITicket;
GO

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
GO
