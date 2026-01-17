USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_kb_category
  @name        NVARCHAR(100),
  @description NVARCHAR(255) = NULL
AS
BEGIN
  SET NOCOUNT ON;

  INSERT INTO dbo.KB_CATEGORIES (name, description, created_at, updated_at)
  VALUES (@name, @description, SYSDATETIME(), SYSDATETIME());

  SELECT CAST(SCOPE_IDENTITY() AS INT) AS category_id;
END
GO
