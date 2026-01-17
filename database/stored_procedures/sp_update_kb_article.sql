USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_update_kb_article
  @article_id   INT,
  @category_id  INT,
  @title        NVARCHAR(200),
  @slug         NVARCHAR(200),
  @summary      NVARCHAR(500) = NULL,
  @content_html NVARCHAR(MAX),
  @is_featured  BIT,
  @is_published BIT,
  @updated_by   INT
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.KB_ARTICLES
  SET category_id = @category_id,
      title = @title,
      slug = @slug,
      summary = @summary,
      content_html = @content_html,
      is_featured = @is_featured,
      is_published = @is_published,
      updated_by = @updated_by,
      updated_at = SYSDATETIME()
  WHERE article_id = @article_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
