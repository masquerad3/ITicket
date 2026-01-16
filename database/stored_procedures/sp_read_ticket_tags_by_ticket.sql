USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_tags_by_ticket
  @ticket_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    tt.tag_id,
    tt.ticket_id,
    tt.tag,
    tt.created_at
  FROM dbo.TICKET_TAGS tt
  WHERE tt.ticket_id = @ticket_id
  ORDER BY tt.tag ASC;
END
GO
