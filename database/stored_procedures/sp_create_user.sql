USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_user
  @first_name     NVARCHAR(50),
  @last_name      NVARCHAR(50),
  @email          NVARCHAR(50),
  @contact        NVARCHAR(15),
  @password_hash  NVARCHAR(100),
  @role           NVARCHAR(20) = 'user'
AS
BEGIN
  SET NOCOUNT ON;

  IF EXISTS (SELECT 1 FROM dbo.USERS WHERE email = @email)
    THROW 50001, 'Email already exists', 1;

  IF EXISTS (SELECT 1 FROM dbo.USERS WHERE contact = @contact)
    THROW 50002, 'Contact already exists', 1;

  INSERT INTO dbo.USERS
    (first_name, last_name, email, contact, password_hash, role, is_active, created_at, updated_at)
  VALUES
    (@first_name, @last_name, @email, @contact, @password_hash, @role, 1, SYSDATETIME(), SYSDATETIME());

  SELECT CAST(SCOPE_IDENTITY() AS INT) AS user_id;
END
GO  