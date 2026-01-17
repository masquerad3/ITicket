USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_update_user_photo
  @user_id INT,
  @profile_photo_path NVARCHAR(255)
AS
BEGIN
  SET NOCOUNT ON;

  UPDATE dbo.USERS
  SET profile_photo_path = @profile_photo_path,
      updated_at = SYSDATETIME()
  WHERE user_id = @user_id;

  SELECT @@ROWCOUNT AS rows_affected;
END
GO
