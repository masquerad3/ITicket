USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_kb_articles
  @q           NVARCHAR(200) = NULL,
  @category_id INT = NULL,
  @featured    BIT = NULL,
  @take        INT = 50,
  @order       NVARCHAR(20) = 'latest'
AS
BEGIN
  SET NOCOUNT ON;

  DECLARE @q2 NVARCHAR(210) = NULL;
  IF (@q IS NOT NULL AND LTRIM(RTRIM(@q)) <> '')
    SET @q2 = CONCAT('%', LTRIM(RTRIM(@q)), '%');

  SELECT TOP (@take)
    a.article_id,
    a.category_id,
    c.name AS category_name,
    a.title,
    a.slug,
    a.summary,
    a.is_featured,
    a.is_published,
    a.view_count,
    a.created_at,
    a.updated_at,
    u.first_name AS author_first_name,
    u.last_name  AS author_last_name
  FROM dbo.KB_ARTICLES a
  INNER JOIN dbo.KB_CATEGORIES c ON c.category_id = a.category_id
  LEFT JOIN dbo.USERS u ON u.user_id = a.updated_by
  WHERE ISNULL(a.is_published, 1) = 1
    AND (@category_id IS NULL OR a.category_id = @category_id)
    AND (@featured IS NULL OR a.is_featured = @featured)
    AND (
      @q2 IS NULL
      OR a.title LIKE @q2
      OR ISNULL(a.summary, '') LIKE @q2
      OR ISNULL(a.content_html, '') LIKE @q2
    )
  ORDER BY
    CASE WHEN LOWER(@order) = 'featured' THEN a.is_featured END DESC,
    a.updated_at DESC;
END
GO
