USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_attachments_by_ticket
  @ticket_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    a.file_id,
    a.ticket_id,
    a.message_id,
    a.uploaded_by,
    a.stored_path,
    a.original_name,
    a.mime,
    a.size,
    a.created_at,
    u.first_name AS uploader_first_name,
    u.last_name  AS uploader_last_name,
    u.role       AS uploader_role
  FROM dbo.TICKET_ATTACHMENTS a
  INNER JOIN dbo.USERS u ON u.user_id = a.uploaded_by
  WHERE a.ticket_id = @ticket_id
  ORDER BY a.created_at ASC;
END
GO
