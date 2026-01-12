USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_update_user_password
  @user_id        INT,
  @password_hash  NVARCHAR(255)
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.USERS
  SET password_hash = @password_hash,
      updated_at    = SYSDATETIME()
  WHERE user_id = @user_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
