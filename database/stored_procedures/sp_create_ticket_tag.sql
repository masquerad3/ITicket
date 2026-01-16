USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_ticket_tag
  @ticket_id INT,
  @tag NVARCHAR(50)
AS
BEGIN
  SET NOCOUNT ON;

  SET @tag = LTRIM(RTRIM(@tag));

  IF (@tag IS NULL OR @tag = '')
  BEGIN
    RETURN;
  END

  IF NOT EXISTS (
    SELECT 1 FROM dbo.TICKET_TAGS
    WHERE ticket_id = @ticket_id AND tag = @tag
  )
  BEGIN
    INSERT INTO dbo.TICKET_TAGS (ticket_id, tag, created_at, updated_at)
    VALUES (@ticket_id, @tag, SYSDATETIME(), SYSDATETIME());
  END

  SELECT CAST(1 AS BIT) AS ok;
END
GO
