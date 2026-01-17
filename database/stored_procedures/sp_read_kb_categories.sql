USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_kb_categories
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    c.category_id,
    c.name,
    c.description,
    COUNT(a.article_id) AS article_count
  FROM dbo.KB_CATEGORIES c
  LEFT JOIN dbo.KB_ARTICLES a
    ON a.category_id = c.category_id
   AND ISNULL(a.is_published, 1) = 1
  GROUP BY c.category_id, c.name, c.description
  ORDER BY c.name ASC;
END
GO
