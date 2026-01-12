USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_delete_user
  @user_id INT
AS
BEGIN
  SET NOCOUNT ON;

  DELETE FROM dbo.USERS
  WHERE user_id = @user_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO