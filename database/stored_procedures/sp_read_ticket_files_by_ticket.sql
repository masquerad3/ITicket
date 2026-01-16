USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_files_by_ticket
  @ticket_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    f.file_id,
    f.ticket_id,
    f.uploaded_by,
    f.stored_path,
    f.original_name,
    f.mime,
    f.size,
    f.created_at,
    u.first_name AS uploader_first_name,
    u.last_name  AS uploader_last_name,
    u.role       AS uploader_role
  FROM dbo.TICKET_FILES f
  INNER JOIN dbo.USERS u ON u.user_id = f.uploaded_by
  WHERE f.ticket_id = @ticket_id
  ORDER BY f.created_at ASC;
END
GO
