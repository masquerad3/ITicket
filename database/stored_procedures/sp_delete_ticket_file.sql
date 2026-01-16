USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_delete_ticket_file
  @ticket_id INT,
  @file_id   INT
AS
BEGIN
  SET NOCOUNT ON;

  DELETE FROM dbo.TICKET_FILES
  WHERE ticket_id = @ticket_id
    AND file_id = @file_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
