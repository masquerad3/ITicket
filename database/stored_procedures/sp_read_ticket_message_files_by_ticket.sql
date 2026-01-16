USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_message_files_by_ticket
  @ticket_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    f.file_id,
    f.message_id,
    m.ticket_id,
    f.uploaded_by,
    f.stored_path,
    f.original_name,
    f.mime,
    f.size,
    f.created_at
  FROM dbo.TICKET_MESSAGE_FILES f
  INNER JOIN dbo.TICKET_MESSAGES m ON m.message_id = f.message_id
  WHERE m.ticket_id = @ticket_id
  ORDER BY f.created_at ASC;
END
GO
