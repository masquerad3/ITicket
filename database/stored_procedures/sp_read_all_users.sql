USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_all_users
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    user_id,
    first_name,
    last_name,
    email,
    contact,
    role,
    is_active,
    created_at,
    updated_at
  FROM dbo.USERS
  ORDER BY user_id DESC;
END
GO