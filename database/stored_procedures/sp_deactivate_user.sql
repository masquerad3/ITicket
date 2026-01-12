USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_deactivate_user
  @user_id INT
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.USERS
  SET is_active = 0,
      updated_at = SYSDATETIME()
  WHERE user_id = @user_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO