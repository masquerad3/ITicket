USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_delete_ticket_message_file
  @ticket_id INT,
  @file_id   INT
AS
BEGIN
  SET NOCOUNT ON;

  DELETE f
  FROM dbo.TICKET_MESSAGE_FILES f
  INNER JOIN dbo.TICKET_MESSAGES m
    ON m.message_id = f.message_id
  WHERE f.file_id = @file_id
    AND m.ticket_id = @ticket_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
