USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_user_by_email
  @email NVARCHAR(50)
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
    profile_photo_path
  FROM dbo.USERS
  WHERE email = @email;
END
GO
