USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_ticket_message
  @ticket_id    INT,
  @user_id      INT,
  @message_type NVARCHAR(20) = 'public',
  @body         NVARCHAR(MAX)
AS
BEGIN
  SET NOCOUNT ON;

  INSERT INTO dbo.TICKET_MESSAGES
    (ticket_id, user_id, message_type, body, created_at, updated_at)
  VALUES
    (@ticket_id, @user_id, @message_type, @body, SYSDATETIME(), SYSDATETIME());

  SELECT CAST(SCOPE_IDENTITY() AS INT) AS message_id;
END
GO
