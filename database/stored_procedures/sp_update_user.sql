USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_update_user
  @user_id     INT,
  @first_name  NVARCHAR(50),
  @last_name   NVARCHAR(50),
  @email       NVARCHAR(50),
  @contact     NVARCHAR(15),
  @role        NVARCHAR(20),
  @is_active   BIT
AS
BEGIN
  SET NOCOUNT ON;

  IF EXISTS (SELECT 1 FROM dbo.USERS WHERE email = @email AND user_id <> @user_id)
    THROW 50003, 'Email already exists', 1;

  IF EXISTS (SELECT 1 FROM dbo.USERS WHERE contact = @contact AND user_id <> @user_id)
    THROW 50004, 'Contact already exists', 1;

  UPDATE dbo.USERS
  SET first_name = @first_name,
      last_name  = @last_name,
      email      = @email,
      contact    = @contact,
      role       = @role,
      is_active  = @is_active,
      updated_at = SYSDATETIME()
  WHERE user_id = @user_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO