USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_set_kb_article_publish
  @article_id   INT,
  @is_published BIT,
  @updated_by   INT
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.KB_ARTICLES
  SET is_published = @is_published,
      updated_by = @updated_by,
      updated_at = SYSDATETIME()
  WHERE article_id = @article_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
