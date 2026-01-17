USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_increment_kb_article_view
  @article_id INT
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.KB_ARTICLES
  SET view_count = ISNULL(view_count, 0) + 1,
      updated_at = updated_at
  WHERE article_id = @article_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
