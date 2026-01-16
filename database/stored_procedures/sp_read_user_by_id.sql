USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_user_by_id
  @user_id INT
AS
BEGIN
  SET NOCOUNT ON;

  SELECT TOP (1)
    user_id,
    first_name,
    last_name,
    email,
    contact,
    password_hash,
    role,
    is_active,
    created_at,
    updated_at
  FROM dbo.USERS
  WHERE user_id = @user_id;
END
GO
