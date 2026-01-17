USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_delete_kb_article
  @article_id INT
AS
BEGIN
  SET NOCOUNT ON;

  DELETE FROM dbo.KB_ARTICLES
  WHERE article_id = @article_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
