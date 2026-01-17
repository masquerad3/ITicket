USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_kb_article_by_slug_admin
  @slug NVARCHAR(200)
AS
BEGIN
  SET NOCOUNT ON;

  SELECT TOP (1)
    a.article_id,
    a.category_id,
    c.name AS category_name,
    a.title,
    a.slug,
    a.summary,
    a.content_html,
    a.is_featured,
    a.is_published,
    a.view_count,
    a.created_by,
    a.updated_by,
    a.created_at,
    a.updated_at
  FROM dbo.KB_ARTICLES a
  INNER JOIN dbo.KB_CATEGORIES c ON c.category_id = a.category_id
  WHERE a.slug = @slug
  ORDER BY a.updated_at DESC;
END
GO
