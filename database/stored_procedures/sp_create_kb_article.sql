USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_kb_article
  @category_id  INT,
  @title        NVARCHAR(200),
  @slug         NVARCHAR(200),
  @summary      NVARCHAR(500) = NULL,
  @content_html NVARCHAR(MAX),
  @is_featured  BIT = 0,
  @is_published BIT = 1,
  @created_by   INT
AS
BEGIN
  SET NOCOUNT ON;

  INSERT INTO dbo.KB_ARTICLES
    (category_id, title, slug, summary, content_html, is_featured, is_published, view_count, created_by, updated_by, created_at, updated_at)
  VALUES
    (@category_id, @title, @slug, @summary, @content_html, @is_featured, @is_published, 0, @created_by, @created_by, SYSDATETIME(), SYSDATETIME());

  SELECT CAST(SCOPE_IDENTITY() AS INT) AS article_id;
END
GO
