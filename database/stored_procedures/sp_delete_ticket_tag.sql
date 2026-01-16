USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_delete_ticket_tag
  @ticket_id INT,
  @tag NVARCHAR(50)
AS
BEGIN
  SET NOCOUNT ON;

  SET @tag = LTRIM(RTRIM(@tag));

  DELETE FROM dbo.TICKET_TAGS
  WHERE ticket_id = @ticket_id AND tag = @tag;

  SELECT CAST(1 AS BIT) AS ok;
END
GO
